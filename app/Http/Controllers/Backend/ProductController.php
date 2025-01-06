<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Style;
use App\Models\Theme;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request) {
        if ($request->ajax()) {

            $data = Product::latest();
            if (!empty($request->input('search.value'))) {
                $searchTerm = $request->input('search.value');
                $data->where('name', 'LIKE', "%$searchTerm%");
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image_url', function ($data) {
                    $url = asset($data->image_url);
                    $image = '<img src="'. $url .'" width="100px">';

                    return $image;
                })
                ->addColumn('type', function ($data) {
                    $type = "<span class='badge bg-primary text-white'> $data->type </span>";
                    return '<p>' . $type . ' </p>';
                })
                ->addColumn('description', function ($data) {
                    $page_content       = $data->description;
                    $short_page_content = strlen($page_content) > 100 ? substr($page_content, 0, 100) . '...' : $page_content;
                    return '<p>' . $short_page_content . ' </p>';
                })
                ->addColumn('status', function ($data) {
                    $status = ' <div class="form-check form-switch">';
                    $status .= ' <input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                    if ($data->status == "active") {
                        $status .= "checked";
                    }
                    $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                    return $status;
                })
                ->addColumn('popular', function ($data) {
                    $status = ' <div class="form-check form-switch">';
                    $status .= ' <input onclick="showPopularStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                    if ($data->popular == 1) {
                        $status .= "checked";
                    }
                    $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                    return $status;
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.products.edit', ['id' => $data->id]) . '" class="btn btn-primary text-white" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="btn btn-danger text-white" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div>';
                })
                ->rawColumns(['image_url', 'type', 'popular', 'description', 'status', 'action'])
                ->make(true);
        }
        return view('backend.layouts.products.index');
    }

    public function create() {
        $styles = Style::where('status', 'active')->get();
        $themes = Theme::where('status', 'active')->get();
        return view('backend.layouts.products.create', compact('styles', 'themes'));
    }

    public function store(Request $request) {

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:free,premium',
            'price' => 'nullable|numeric',
            'popular' => 'boolean',
            'description' => 'nullable|string',
            'theme' => 'nullable|array', // IDs of themes
            'theme.*' => 'exists:themes,id', // Ensure each theme ID exists in the themes table
            'style' => 'nullable|array', // IDs of styles
            'style.*' => 'exists:styles,id', // Ensure each style ID exists in the styles table
            'image_url' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('image_url')) {
            $image  = $request->file('image_url');
            $imagePath = uploadImage($image, 'products');
            $validatedData['image_url'] = $imagePath;
        }

        try {

            $product = Product::create($validatedData);

            // Attach themes and styles
            $product->themes()->attach($request->theme);
            $product->styles()->attach($request->style);

            return to_route('admin.products.index')->with('t-success', 'New Product Created');

        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function edit($id) {
        $product = Product::findOrFail($id);
        $styles = Style::where('status', 'active')->get();
        $themes = Theme::where('status', 'active')->get();
        return view('backend.layouts.products.edit', compact('product', 'styles', 'themes'));
    }

    public function update(Request $request, $id) {


        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:free,premium',
            'price' => 'nullable|numeric',
            'popular' => 'boolean',
            'description' => 'nullable|string',
            'theme' => 'nullable|array', // IDs of themes
            'theme.*' => 'exists:themes,id', // Ensure each theme ID exists in the themes table
            'style' => 'nullable|array', // IDs of styles
            'style.*' => 'exists:styles,id', // Ensure each style ID exists in the styles table
            'image_url' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $product = Product::findOrFail($id);

        if ($request->hasFile('image_url')) {

            if ($product->image_url) {
                $previousImagePath = public_path($product->image_url);
                if (file_exists($previousImagePath)) {
                    unlink($previousImagePath);
                }
            }

            $image  = $request->file('image_url');
            $imagePath = uploadImage($image, 'products');
            $validatedData['image_url'] = $imagePath;
        }else {
            $validatedData['image_url'] = $product->image_url;
        }

        // dd($validatedData);


            $product->update($validatedData);

            $product->themes()->sync($request->theme);
            $product->styles()->sync($request->style);

            return to_route('admin.products.index')->with('t-success', 'Product Updated');

    }

    public function status(int $id): JsonResponse {
        $data = Product::findOrFail($id);
        if ($data->status == 'inactive') {
            $data->status = 'active';
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data'    => $data,
            ]);
        } else {
            $data->status = 'inactive';
            $data->save();

            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data'    => $data,
            ]);
        }
    }

    public function popular(int $id): JsonResponse {
        $data = Product::findOrFail($id);
        if ($data->popular == 0) {
            $data->popular = 1;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data'    => $data,
            ]);
        } else {
            $data->popular = 0;
            $data->save();

            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data'    => $data,
            ]);
        }
    }

    public function destroy(int $id): JsonResponse {
        $data = Product::findOrFail($id);
        if ($data->image_url) {
            $previousImagePath = public_path($data->image_url);
            if (file_exists($previousImagePath)) {
                unlink($previousImagePath);
            }
        }
        $data->delete();
        return response()->json([
            't-success' => true,
            'message'   => 'Deleted successfully.',
        ]);
    }
}
