<?php

namespace App\Http\Controllers\Backend;

use App\Helper\Helper;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use App\Models\BusinessCategory;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminEventController extends Controller
{




    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = BusinessProfile::all();

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
                    $checked = $data->status == "accept" ? "checked" : "";
                    $statusText = $data->status == "accept" ? "Accepted" : "Pending";
                    $badgeClass = $data->status == "accept" ? "badge-success" : "badge-warning";
                
                    $toggleSwitch = '<div class="d-flex align-items-center">
                                        <div class="form-check form-switch me-2">
                                            <input onclick="showStatusChangeAlert(' . $data->id . ')" 
                                                   type="checkbox" class="form-check-input" 
                                                   id="customSwitch' . $data->id . '" 
                                                   name="status" ' . $checked . '>
                                            <label for="customSwitch' . $data->id . '" 
                                                   class="form-check-label"></label>
                                        </div>
                                        <span class="badge ' . $badgeClass . '">' . $statusText . '</span>
                                    </div>';
                
                    $editButton = '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                        <a href="' . route('admin.event.edit', $data->id) . '" class="btn btn-primary text-white" title="Edit">
                                            Show
                                        </a>
                                   </div>';
                
                    return $toggleSwitch . $editButton;
                })
                
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('backend.layouts.event.index');
    }

    public function status(int $id): JsonResponse
    {
        $data = BusinessProfile::findOrFail($id);

        


        if ($data->status == 'pending') {

            $data->status = 'accept';
            $message = 'Business profile status changed to Accepted.';

        } else {
            $data->status = 'pending';
            $message = 'Business profile status changed to Pending.';
        }

        $data->save();

       
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ]);
    }



    public function create()
    {

        $categories = BusinessCategory::all();

        return view('backend.layouts.event.create', compact('categories'));
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
            $categories = BusinessCategory::all();
           

            return view('backend.layouts.event.edit', compact('event', 'categories'));
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
