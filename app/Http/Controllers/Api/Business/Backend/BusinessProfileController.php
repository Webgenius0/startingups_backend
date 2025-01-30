<?php

namespace App\Http\Controllers\Api\Business\Backend;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessProfileController extends Controller
{

    use ApiResponse;

    public function create()
    {
        //
    }

    // __store business profile
    public function store(Request $request)
    {

        // dd($request->all());

        $validatedData = $request->validate([

            'cover' => 'nullable',
            'business_name' => 'required|string',
            'category_id' => 'required|integer',
            'sub_category_id' => 'required|integer',
            'activity' => 'required|in:Indoor,Outdoor',

            'location' => 'required|string',
            'hours' => 'required|array',
            'hours.*.day' => 'required|string',
            'hours.*.is_closed' => 'required',

            'hours.*.open_time' => 'nullable|string',
            'hours.*.close_time' => 'nullable|string',

            'hours.*.is_second_time' => 'required',

            'hours.*.re_open_time' => 'nullable|string',
            'hours.*.re_close_time' => 'nullable|string',

            'prices' => 'required|array',
            'prices.*.type' => 'required|string',
            'prices.*.amount' => 'required',
            'prices.*.days' => 'required',
            'prices.*.offerings' => 'nullable|string',

            // age limit
            'age_min' => 'nullable',
            'age_max' => 'nullable',

        ]);

        $businessProfile = BusinessProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'type' => 'business_profile',
                'business_name' => $validatedData['business_name'],
                'category_id' => $validatedData['category_id'],
                'sub_category_id' => $validatedData['sub_category_id'],
                'activity' => $validatedData['activity'],
                'location' => $validatedData['location'],
                'age_min' => $validatedData['age_min'],
                'age_max' => $validatedData['age_max'],

            ]
        );

        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
            $businessProfile->cover = $coverPath;
            $businessProfile->save();
        }

        $businessProfile->business_hours()->delete();
        foreach ($validatedData['hours'] as $hour) {
            $businessProfile->business_hours()->create([
                'day' => $hour['day'],
                'is_closed' => $hour['is_closed'] == true ? 1 : 0,
                'open_time' => $hour['is_closed'] ? null : $hour['open_time'],
                'close_time' => $hour['is_closed'] ? null : $hour['close_time'],

                'is_second_time' => $hour['is_second_time'] == true ? 1 : 0,
                're_open_time' => isset($hour['re_open_time']) && !$hour['is_closed'] ? $hour['re_open_time'] : null,
                're_close_time' => isset($hour['re_close_time']) && !$hour['is_closed'] ? $hour['re_close_time'] : null,
            ]);
        }

        $businessProfile->business_prices()->delete();
        foreach ($validatedData['prices'] as $price) {
            $businessProfile->business_prices()->create([
                'type' => $price['type'],
                'amount' => $price['amount'],
                'days' => isset($price['days']) ? $price['days'] : null,
                'offerings' => $price['offerings'],
            ]);
        }

        $businessProfile->cover = $businessProfile->cover ? url($businessProfile->cover) : null;

        // load business hours
        $businessProfile->load('business_hours', 'business_prices');

        return $this->success($businessProfile, 'Business Profile created successfully', 200);
    }

    public function business_profile_details()
    {
        $businessProfile = BusinessProfile::where('user_id', Auth::id())
            ->where('type', 'business_profile')
            ->first();

        if (!$businessProfile) {
            return $this->error([], 'Business Profile not found', 404);
        }

        // Set the cover URL
        $businessProfile->cover = $businessProfile->cover ? url($businessProfile->cover) : null;


        $businessProfile->load('business_hours');

        // Structure the data in serialized order
        $data = [
            'id' => $businessProfile->id,
            // 'type' => $businessProfile->type,
            'user_id' => $businessProfile->user_id,
            'cover' => $businessProfile->cover ? url($businessProfile->cover) : null,
            'business_name' => $businessProfile->business_name,
            'category_id' => $businessProfile->category_id,
            'category_name' => $businessProfile->category->name,

            'subcategory_id' => $businessProfile->sub_category_id,
            'subcategory_name' => $businessProfile->sub_category ?  $businessProfile->sub_category->name : '',
            'activity' => $businessProfile->activity,
            'location' => $businessProfile->location,
            'age_min' => $businessProfile->age_min,
            'age_max' => $businessProfile->age_max,


            'business_hours' => $businessProfile->business_hours->map(function ($hour) {
                return [
                    'id' => $hour->id,
                    'business_profile_id' => $hour->business_profile_id,
                    'day' => $hour->day,

                    'is_closed' => $hour->is_closed == 1 ? true : false,
                    'open_time' => $hour->open_time,
                    'close_time' => $hour->close_time,


                    'is_second_time' => $hour->is_second_time == 1 ? true : false,
                    're_open_time' => $hour->re_open_time,
                    're_close_time' => $hour->re_close_time,


                ];
            }),
        ];

        return $this->success($data, 'Business Profile retrieved successfully', 200);
    }


    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function business_profile_update(Request $request)
    {
        $businessProfile = BusinessProfile::where('user_id', Auth::id())
            ->where('type', 'business_profile')
            ->first();
    
        if (!$businessProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Business profile not found or unauthorized access.',
            ], 404);
        }
    
        $validatedData = $request->validate([
            'cover' => 'nullable|image|mimes:jpg,jpeg,png',
            'business_name' => 'required|string',
            'category_id' => 'required|integer',
            'sub_category_id' => 'required|integer',
            'activity' => 'required|string',
            'location' => 'required|string',
            'hours' => 'required|array',
            'hours.*.day' => 'required|string',
            'hours.*.is_closed' => 'required',
            'hours.*.open_time' => 'nullable|string',
            'hours.*.close_time' => 'nullable|string',
            'hours.*.is_second_time' => 'required',
            'hours.*.re_open_time' => 'nullable|string',
            'hours.*.re_close_time' => 'nullable|string',
            
        ]);
    
        // Update business profile details
        $businessProfile->update([
            'business_name' => $validatedData['business_name'],
            'category_id' => $validatedData['category_id'],
            'sub_category_id' => $validatedData['sub_category_id'],
            'activity' => $validatedData['activity'],
            'location' => $validatedData['location'],
        ]);
    
        
        if ($request->hasFile('cover')) {
            if ($businessProfile->cover) {
                Helper::deleteImage($businessProfile->cover);
            }
    
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
            $businessProfile->cover = $coverPath;
            $businessProfile->save();
        }
    
        
        if ($request->has('hours')) {
            $businessProfile->business_hours()->delete();
    
            foreach ($validatedData['hours'] as $hour) {
                $businessProfile->business_hours()->create([
                    'day' => $hour['day'],
                    'is_closed' => $hour['is_closed'],
                    'open_time' => $hour['is_closed'] ? null : $hour['open_time'],
                    'close_time' => $hour['is_closed'] ? null : $hour['close_time'],
                    'is_second_time' => $hour['is_second_time'],
                    're_open_time' => isset($hour['re_open_time']) && !$hour['is_closed'] ? $hour['re_open_time'] : null,
                    're_close_time' => isset($hour['re_close_time']) && !$hour['is_closed'] ? $hour['re_close_time'] : null,
                ]);
            }
        }
    
        
        if ($request->has('prices')) {
            $businessProfile->business_prices()->delete();
    
            foreach ($validatedData['prices'] as $price) {
                $businessProfile->business_prices()->create([
                    'type' => $price['type'],
                    'amount' => $price['amount'],
                    'offerings' => $price['offerings'],
                ]);
            }
        }
    
        // Load updated relationships
        $businessProfile->load('business_hours', 'business_prices', 'category', 'sub_category');
    
        // Prepare response data
        $data = [
            'id' => $businessProfile->id,
            'user_id' => $businessProfile->user_id,
            'cover' => $businessProfile->cover ? url($businessProfile->cover) : null,
            'business_name' => $businessProfile->business_name,
            'category_id' => $businessProfile->category_id,
            'category_name' => $businessProfile->category->name ?? '',
            'subcategory_id' => $businessProfile->sub_category_id,
            'subcategory_name' => $businessProfile->sub_category->name ?? '',
            'activity' => $businessProfile->activity,
            'location' => $businessProfile->location,
            'business_hours' => $businessProfile->business_hours->map(function ($hour) {
                return [
                    'id' => $hour->id,
                    'business_profile_id' => $hour->business_profile_id,
                    'day' => $hour->day,
                    'is_closed' => (bool) $hour->is_closed,
                    'open_time' => $hour->open_time,
                    'close_time' => $hour->close_time,
                    'is_second_time' => (bool) $hour->is_second_time,
                    're_open_time' => $hour->re_open_time,
                    're_close_time' => $hour->re_close_time,
                ];
            }),
            'business_prices' => $businessProfile->business_prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'business_profile_id' => $price->business_profile_id,
                    'type' => $price->type,
                    'amount' => $price->amount,
                    'offerings' => $price->offerings,
                ];
            }),
        ];
    
        return $this->success($data, 'Business Profile updated successfully', 200);
    }
    

    public function destroy(string $id)
    {
        //
    }
}
