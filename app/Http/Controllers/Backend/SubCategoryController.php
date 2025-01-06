<?php

namespace App\Http\Controllers\Backend;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $data = SubCategory::with('category')->latest();
        
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('category_name', function ($data) {
                    return $data->category ? $data->category->name : 'N/A';
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                              <a href="' . route('admin.sub_category.edit', ['id' => $data->id]) . '" class="btn btn-primary text-white" title="Edit">
                              <i class="bi bi-pencil"></i>
                              </a>
                              <a href="#" onclick="showDeleteConfirm(' . $data->id . ')" type="button" class="btn btn-danger text-white" title="Delete">
                              <i class="bi bi-trash"></i>
                            </a>
                            </div>';
                })
                ->rawColumns(['category_name','action'])
                ->make(true);
        }

        return view('backend.layouts.sub_category.index');
    }

    public function create()
    {
        $category = Category::get();
        return view('backend.layouts.sub_category.create',compact('category'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id'
        ]);
       
        try {
            SubCategory::create([
                'name' => $request->name,
                'category_id' => $request->category_id
            ]);

            return to_route('admin.sub_category.index')->with('t-success', 'SubCategory Created Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function edit($id) {
       
       try{

        $subcategory = SubCategory::findOrFail($id);
        $category = Category::select('id','name')->get();
        if(!$subcategory)
        {
            return redirect()->back()->with('t-error', 'SubCategory not found');
        }
     
     
        return view('backend.layouts.sub_category.edit', compact('subcategory','category'));
       }catch(\Exception $e){
        return redirect()->back()->with('t-error', $e->getMessage());
       }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'category_id' => 'required'
        ]);
        try {
            $data = SubCategory::find($id);
            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SubCategory not found.',
                ], 404);
            }
            $data->update([
                'name' => $request->name,
                'category_id' => $request->category_id
            ]);
            return to_route('admin.sub_category.index')->with('t-success', 'SubCategory updated Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {

        $data = SubCategory::findOrFail($id);
        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'SubCategory not found.',
            ], 404);
        }
        $data->delete();

        return response()->json([
            'success' => true,
            'message' => 'SubCategory deleted successfully!',
        ],200);
    }
}
