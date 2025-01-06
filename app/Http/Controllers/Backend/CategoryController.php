<?php

namespace App\Http\Controllers\Backend;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class CategoryController extends Controller
{




    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = Category::latest();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {

                    if (empty($data->image)) {
                        return ' --- ';
                    }
                    $url = asset($data->image);
                    $iamge = '<img src="' . $url . '" width="50px">';

                    return $iamge;
                })
                ->addColumn('gender_type', function ($data) {

                    if (empty($data->gender_type)) {
                        return ' --- ';
                    }
                    return $data->gender_type;

                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.category.edit', ['id' => $data->id]) . '" class="btn btn-primary text-white" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="btn btn-danger text-white" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div>';
                })
                ->rawColumns(['image', 'gender_type', 'action'])
                ->make(true);
        }

        return view('backend.layouts.category.index');
    }


    public function create()
    {
        return view('backend.layouts.category.create');
    }

    public function store(Request $request)
    {

        // dd($request->all());

        try {

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|unique:categories|max:100',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',

            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = Helper::uploadImage($image, 'categorys');
            } else {
                $imagePath = '-';
            }

            Category::create([
                'name' => $request->name,
                'image' => $imagePath,

            ]);

            return to_route('admin.category.index')->with('t-success', 'Category Created Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function edit($id)
    {

        try {
            $category = Category::find($id);

            return view('backend.layouts.category.edit', compact('category'));
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    // public function update(Request $request, string $id)
    // {
    //     try {

    //         $category = Category::find($id);
    //         if (!$category) {
    //         }

    //         if ($request->hasFile('image')) {

    //             if ($category->image) {
    //                 $oldImagePath = public_path($category->image);
    //                 if (file_exists($oldImagePath)) {
    //                     unlink($oldImagePath);
    //                 }
    //             }

    //             $image = $request->file('image');
    //             $imagePath = uploadImage($image, 'categorys');
    //         } else {
    //             $imagePath = $category->image;
    //         }

    //         $category->update([
    //             'name' => $request->name,
    //             'image' => $imagePath,
    //             'gender_type' => $request->gender_type,
    //             'type' => $request->type,
    //         ]);

    //         return to_route('admin.category.index')->with('t-success', 'Category updated Successfull.');

    //     } catch (\Exception $e) {

    //         return redirect()->back()->with('t-error', $e->getMessage());
    //     }
    // }

    public function update(Request $request, string $id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return redirect()->back()->with('t-error', 'Category not found.');
            }

            // Validation rules
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'image' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',

            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Check if a new image is uploaded
            if ($request->hasFile('image')) {
                // Delete the old image if it exists
                if ($category->image && file_exists(public_path($category->image))) {
                    unlink(public_path($category->image));
                }

                // Upload the new image
                $image = $request->file('image');
                $imagePath = Helper::uploadImage($image, 'categorys');
            } else {
                // If no image is uploaded, keep the existing image
                $imagePath = $category->image;
            }

            // Update the category details
            $category->update([
                'name' => $request->name,
                'image' => $imagePath,

            ]);

            return to_route('admin.category.index')->with('t-success', 'Category updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {

        $data = Category::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found.',
            ], 404);
        }

        if ($data->image) {
            $oldImagePath = public_path($data->image);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully!',
        ], 200);
    }
}
