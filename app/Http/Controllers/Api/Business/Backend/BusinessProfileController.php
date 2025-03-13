<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Carbon\Carbon;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BusinessProfileController extends Controller
{

    use ApiResponse;

    public function list()
    {
        $user = auth('business')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $businessProfiles = BusinessProfile::where('user_id', $user->id)
            ->with('event_clicks', 'event_bookings', 'event_reviews')
            ->get();



        $data = $businessProfiles->map(function ($profile) {


            $linkClicks = $profile->event_clicks->count();
            $signUps = $profile->event_bookings->count();
            $revenue = $profile->event_bookings->sum('price');
            $reportedCustomers = $profile->event_bookings->where('user_id', '!=', null)->count();
            $rating = $profile->event_reviews->sum('rating');
            $reviewCount = $profile->event_reviews->count();
            $averageRating = $reviewCount > 0 ? round($rating / $reviewCount, 2) : 0;



            // $eventDate = Carbon::parse($profile->date)->format('F j, Y');
            // $startTime = Carbon::parse($profile->start_time)->format('g:i A');
            // $endTime = Carbon::parse($profile->end_time)->format('g:i A');

            return [

                'business_profile_id' => $profile->id,
                'business_name' => $profile->business_name == null ?  $profile->title : $profile->business_name,
                // 'business_date' => $eventDate,
                // 'business_time' => $startTime . ' - ' . $endTime,
                'average_rating' => $averageRating,
                'link_clicks' => $linkClicks,
                'sign_ups' => $signUps,
                'revenue' => $revenue,
                'reported_customers' => $reportedCustomers,

                'status' => 'active'
            ];
        });

        return $this->success($data, 'Business Profile  list retrived  successfully.');
    }







    // __store business profile
    public function store(Request $request)
    {

        // dd($request->all());
        $validatedData = $request->validate([

            'cover' => 'nullable',
            'business_name' => 'required|string',
            'category_id' => 'required|integer',
            'activity' => 'required',
            'location' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',

            // age limit
            'age_min' => 'nullable',
            'age_max' => 'nullable',

            'prices' => 'required|array',
            'prices.*.type' => 'required|string',
            'prices.*.amount' => 'required',
            'prices.*.days' => 'required',
            'prices.*.offerings' => 'nullable|string',
        ]);

        $businessProfile = BusinessProfile::create(
            [
                'user_id' => Auth::id(),
                'business_name' => $validatedData['business_name'],
                'category_id' => $validatedData['category_id'],
                'activity' => $validatedData['activity'],
                'location' => $validatedData['location'],
                'latitude' => $validatedData['latitude'],
                'longitude' => $validatedData['longitude'],
                'age_min' => $validatedData['age_min'],
                'age_max' => $validatedData['age_max'],
                'created_at' => now(),
                'updated_at' => now(),
                'status' => 'pending'

            ]
        );

        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
            $businessProfile->cover = $coverPath;
            $businessProfile->save();
        }


        $businessProfile->business_hours()->delete();
        foreach ($request->hours as $hour) {
            $businessProfile->business_hours()->create([
                'day' => $hour['day'],
                'is_closed' => $hour['is_closed'],
                'open_time' => !$hour['is_closed'] ? $hour['open_time'] : null,
                'close_time' => !$hour['is_closed'] ? $hour['close_time'] : null,
                'is_second_time' => $hour['is_second_time'],
                're_open_time' => ($hour['is_second_time'] && !$hour['is_closed'] && isset($hour['re_open_time'])) ? $hour['re_open_time'] : null,
                're_close_time' => ($hour['is_second_time'] && !$hour['is_closed'] && isset($hour['re_close_time'])) ? $hour['re_close_time'] : null,

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


        $data = [
            'id' => $businessProfile->id,
            // 'type' => $businessProfile->type,
            'user_id' => $businessProfile->user_id,
            'cover' => $businessProfile->cover ? url($businessProfile->cover) : null,
            'business_name' => $businessProfile->business_name,
            'category_id' => $businessProfile->category_id,
            'category_name' => $businessProfile->category->name,

            'activity' => $businessProfile->activity,
            'location' => $businessProfile->location,
            'latitude' => $businessProfile->latitude,
            'longitude' => $businessProfile->longitude,
            'age_min' => $businessProfile->age_min,
            'age_max' => $businessProfile->age_max,


            'business_hours' => $businessProfile->business_hours->map(function ($hour) {
                return [
                    'id' => $hour->id,
                    'business_profile_id' => $hour->business_profile_id,
                    'day' => $hour->day,

                    'is_closed' => $hour->is_closed == 1 ? true : false,
                    'open_time' => $hour->open_time ? $hour->open_time : null,
                    'close_time' => $hour->close_time ?  $hour->close_time : null,


                    'is_second_time' => $hour->is_second_time == 1 ? true : false,
                    're_open_time' => $hour->re_open_time ? $hour->re_open_time : null,
                    're_close_time' => $hour->re_close_time ? $hour->re_close_time : null,


                ];
            }),

            'business_prices' => $businessProfile->business_prices->map(function ($price) {
                return [
                    'id' => $price->id,
                    'type' => $price->type,
                    'amount' => $price->amount,
                    'offerings' => $price->offerings,
                ];
            }),
        ];

        return $this->success($data, 'Business Profile created successfully', 200);
    }

    public function business_profile_details($id)
    {
        $businessProfile = BusinessProfile::find($id);

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
                    'open_time' => $hour->open_time ? $hour->open_time : null,
                    'close_time' => $hour->close_time ?  $hour->close_time : null,


                    'is_second_time' => $hour->is_second_time == 1 ? true : false,
                    're_open_time' => $hour->re_open_time ? $hour->re_open_time : null,
                    're_close_time' => $hour->re_close_time ? $hour->re_close_time : null,


                ];
            }),
        ];

        return $this->success($data, 'Business Profile retrieved successfully', 200);
    }





    public function business_profile_update(Request $request, $id)
    {

        DB::beginTransaction();

        try {
            $businessProfile = BusinessProfile::where('user_id', Auth::id())->find($id);



            if (!$businessProfile) {
                return $this->error([], 'Business profile not found or unauthorized access.', 404);
            }

            // Decode hours from JSON if needed
            $hours = is_string($request->hours) ? json_decode($request->hours, true) : $request->hours;


            $businessProfile->update([
                'business_name' => $request->input('business_name', $businessProfile->business_name),
                'category_id' => $request->input('category_id', $businessProfile->category_id),

                'activity' => $request->input('activity', $businessProfile->activity),
                'location' => $request->input('location', $businessProfile->location),
                'latitude' => $request->input('latitude', $businessProfile->location),
                'longitude' => $request->input('longitude', $businessProfile->longitude),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if (!empty($request->hasFile('cover'))) {
                if ($businessProfile->cover) {
                    Helper::deleteImage($businessProfile->cover);
                }
                $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
                $businessProfile->cover = $coverPath;
                $businessProfile->save();
            }


            if ($request->has('hours')) {
                $hours = is_string($request->hours) ? json_decode($request->hours, true) : $request->hours;

                if (!empty($hours) && is_array($hours)) {

                    $businessProfile->business_hours()->delete();

                    foreach ($request->hours as $hour) {
                        $businessProfile->business_hours()->create([
                            'day' => $hour['day'],
                            'is_closed' => $hour['is_closed'],
                            'open_time' => !$hour['is_closed'] ? $hour['open_time'] : null,
                            'close_time' => !$hour['is_closed'] ? $hour['close_time'] : null,
                            'is_second_time' => $hour['is_second_time'],
                            're_open_time' => ($hour['is_second_time'] && !$hour['is_closed'] && isset($hour['re_open_time'])) ? $hour['re_open_time'] : null,
                            're_close_time' => ($hour['is_second_time'] && !$hour['is_closed'] && isset($hour['re_close_time'])) ? $hour['re_close_time'] : null,

                        ]);
                    }
                }
            }

            $businessProfile->load('business_hours');

            DB::commit();

            $data = [
                'id' => $businessProfile->id,
                'cover' => url($businessProfile->cover),
                'business_name' => $businessProfile->business_name,
                'category_id' => $businessProfile->category_id,
                'category_name' => $businessProfile->category->name,

                'activity' => $businessProfile->activity,
                'location' => $businessProfile->location,
                'business_hours' => $businessProfile->business_hours,
            ];

            return $this->success($data, 'Business Profile Updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the business profile. Please try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }







    public function destroy(string $id)
    {
        //
    }
}
