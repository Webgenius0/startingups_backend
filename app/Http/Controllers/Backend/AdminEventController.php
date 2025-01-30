<?php

namespace App\Http\Controllers\Backend;

use App\Helper\Helper;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminEventController extends Controller
{




    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = BusinessProfile::where('status', 'pending')->get();

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('image', function ($data) {
                    if (empty($data->cover)) {
                        return ' --- ';
                    }
                    $url = asset($data->cover);
                    return '<img src="' . $url . '" width="50px">';
                })
                ->addColumn('business_name', function ($data) {
                    return $data->business_name ?? ' --- ';
                })
                ->addColumn('location', function ($data) {
                    return $data->location ?? ' --- ';
                })
                ->addColumn('status', function ($data) {
                    return ucfirst($data->status);
                })
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                <a href="' . route('admin.event.edit', $data->id) . '" class="btn btn-primary text-white" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                              <a href="javascript:void(0);" onclick="showDeleteConfirm(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                              <i class="bi bi-trash"></i>
                              </a>
                            </div>';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('backend.layouts.event.index');
    }



    public function create()
    {

        $categories = Category::all();

        $sub_categories = SubCategory::all();
        return view('backend.layouts.event.create', compact('categories', 'sub_categories'));
    }

    public function store(Request $request)
    {

        // dd($request->all());

        // Create the Event
        $event = BusinessProfile::create([
            'user_id' => Auth::id(),
            'type' => 'business',
            'business_name' => $request->business_name,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'activity' => $request->activity,
            'location' => $request->location,
            'age_min' => $request->age_min,
            'age_max' => $request->age_max,
            'status' => 'pending'
        ]);

        // dd($event);


        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
            $event->cover = $coverPath;
            $event->save();
        }

        // Store Operating Hours

        foreach ($request->hours as $hour) {
            $event->business_hours()->create([
                'day' => $hour['day'],
                'is_closed' => $hour['is_closed'] ?? false,
                'open_time' => $hour['open_time'] ?? null,
                'close_time' => $hour['close_time'] ?? null,
                'is_second_time' => $hour['is_second_time'] ?? false,
                're_open_time' => $hour['re_open_time'] ?? null,
                're_close_time' => $hour['re_close_time'] ?? null,
            ]);
        }



        // Return Success Response
        return redirect()->route('admin.event.index')->with('success', 'Event created successfully.');
    }


    public function edit($id)
    {
        try {
            $event = BusinessProfile::findOrFail($id);
            $categories = Category::all();
            $sub_categories = SubCategory::all();

            return view('backend.layouts.event.edit', compact('event', 'categories', 'sub_categories'));
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

    public function update(Request $request, $id)
    {
        try {
            $event = BusinessProfile::with('business_hours')->find($id);



            // Update Event Details
            $event->update([
                'business_name' => $request->business_name,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'activity' => $request->activity,
                'location' => $request->location,
                'age_min' => $request->age_min,
                'age_max' => $request->age_max,
            ]);

            
            if ($request->hasFile('cover')) {
                $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
                $event->update(['cover' => $coverPath]);
            }

            
            $event->business_hours()->delete(); 
            foreach ($request->hours as $hour) {
                $event->business_hours()->create([
                    'day' => $hour['day'],
                    'open_time' => $hour['open_time'] ?? null,
                    'close_time' => $hour['close_time'] ?? null,
                ]);
            }

            return redirect()->route('admin.event.index')->with('success', 'Event updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('t-error', $e->getMessage());
        }
    }


    public function destroy(string $id)
    {

        $data = BusinessProfile::findOrFail($id);
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
