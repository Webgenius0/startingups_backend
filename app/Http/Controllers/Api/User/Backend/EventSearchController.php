<?php

namespace App\Http\Controllers\Api\User\Backend;

use Illuminate\Http\Request;
use App\Models\UserPreference;
use App\Models\BusinessProfile;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;

class EventSearchController extends Controller
{
    use ApiResponse;


    public function explore_event()
    {
        try {
            $user = auth('api')->user();

            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $radius = 10000;

            if (!$latitude || !$longitude) {
                return $this->error([], 'User location not found', 400);
            }


            $business_events = BusinessProfile::selectRaw(
                "
                business_profiles.id, business_profiles.business_name, 
                business_profiles.location, business_profiles.cover, 
                business_profiles.latitude, business_profiles.longitude, business_profiles.created_at,
                (6371 * acos(cos(radians(?)) * cos(radians(business_profiles.latitude)) 
                * cos(radians(business_profiles.longitude) - radians(?)) 
                + sin(radians(?)) * sin(radians(business_profiles.latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->where(function ($query) {
                    $query->whereHas('event_clicks')
                        ->orWhereHas('event_bookings')
                        ->orWhereHas('event_reviews');
                })
                ->having('distance', '<', $radius)
                ->orderBy('distance', 'ASC')
                ->with('business_hours', 'event_clicks', 'event_bookings')
                ->get();

            // dd($business_events);


            $near_events = $business_events->map(function ($event) {
                $business_hour = $event->business_hours->first();

                // Calculate Discovery Score
                $signups = $event->event_bookings->count();
                $clicks = $event->event_clicks->count();
                $reviews_avg = $event->event_reviews->avg('rating') ?? 0; 
                $favorites = $event->event_reviews->where('rating', ">", 3)->count();
                // $video_bonus = $event->videos->count() > 0 ? 50 : 0; 

                $score = ($signups * 5) + ($clicks * 4) + ($reviews_avg * 3) + ($favorites * 2);

                return [
                    'id' => $event->id,
                    'title' => $event->business_name,
                    'time' => $business_hour ? $business_hour->open_time . "-" . $business_hour->close_time : 'N/A',
                    'date' => $event->created_at ? $event->created_at->format('M d, Y') : 'N/A',
                    'location' => $event->location,
                    'cover' => $event->cover ? url($event->cover) : null,
                    'distance' => round($event->distance, 2) . ' km',
                    'score' => round($score, 2), // Final score
                ];
            });

            $sorted_events = $near_events->sortByDesc('score')->values();

            // $sorted_events = $near_events->sortBy('score')->values();


            $recommated_events = BusinessProfile::with('business_hours', 'event_clicks', 'event_bookings')

                ->get()
                ->map(function ($event) {
                    $business_hour = $event->business_hours->first();
                    return [
                        'id' => $event->id,
                        'title' => $event->business_name,
                        'time' => $business_hour ? $business_hour->open_time . "-" . $business_hour->close_time : 'N/A',
                        'date' => $event->created_at->format('M d, Y'),
                        'location' => $event->location,
                        'cover' => $event->cover ? url($event->cover) : null,
                    ];
                });

            return $this->success([
                'near_events' => $sorted_events,
                'recommated_events' => $recommated_events,
            ], 'Events retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }



    // __tailored events
    public function tailored_event()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return $this->error([], 'User not authenticated', 401);
            }

            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $radius = 5; // 5 km radius

            if (!$latitude || !$longitude) {
                return $this->error([], 'User location not found', 400);
            }

            $user->load('followees');

            $user_preferences = UserPreference::where('user_id', $user->id)->pluck('category_id');

            $category_based_events = BusinessProfile::selectRaw(
                "
                business_profiles.*, 
                (6371 * acos(cos(radians(?)) * cos(radians(business_profiles.latitude)) 
                * cos(radians(business_profiles.longitude) - radians(?)) 
                + sin(radians(?)) * sin(radians(business_profiles.latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->whereIn('category_id', $user_preferences)
                // ->having('distance', '<', $radius)
                ->with('business_hours', 'event_clicks', 'event_bookings')
                ->get();

            $followee_ids = $user->followees->pluck('followee_id');

            $followee_based_events = BusinessProfile::selectRaw(

                    "business_profiles.*, 
                    (6371 * acos(cos(radians(?)) * cos(radians(business_profiles.latitude)) 
                    * cos(radians(business_profiles.longitude) - radians(?)) 
                    + sin(radians(?)) * sin(radians(business_profiles.latitude)))) AS distance",
                    [$latitude, $longitude, $latitude]

                )
                ->whereHas('event_bookings', function ($query) use ($followee_ids) {
                    $query->whereIn('user_id', $followee_ids);
                })
                ->having('distance', '<', $radius)
                ->with('business_hours', 'event_clicks', 'event_bookings')
                ->get();

            $tailored_events = $category_based_events->merge($followee_based_events)->unique('id');

            $tailored_events = $tailored_events->map(function ($event) use ($user_preferences, $followee_ids) {
                $business_hour = $event->business_hours->first();

                // Calculate Tailored Score
                $business_match_score = in_array($event->category_id, $user_preferences->toArray()) ? 5 : 0;
                $friends_previous_activities_score = $event->event_bookings->whereIn('user_id', $followee_ids)->count() > 0 ? 10 : 0;
                $friends_upcoming_activities_score = $event->event_bookings->whereIn('user_id', $followee_ids)->where('created_at', '>', now())->count() > 0 ? 15 : 0;

                $tailored_score = $business_match_score + $friends_previous_activities_score + $friends_upcoming_activities_score;

                return [
                    'id' => $event->id,
                    'title' => $event->business_name,
                    'time' => $business_hour ? $business_hour->open_time . "-" . $business_hour->close_time : 'N/A',
                    'date' => $event->created_at->format('M d, Y'),
                    'location' => $event->location,
                    'cover' => $event->cover ? url($event->cover) : null,
                    'distance' => round($event->distance, 2) . ' km',
                    'score' => $tailored_score,
                ];
            });

            return $this->success($tailored_events, 'Tailored Events retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }



    // __random events
    public function random_event()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return $this->error([], 'User not authenticated', 401);
            }

            $latitude = $user->latitude;
            $longitude = $user->longitude;
            $radius = 5; // 5 km radius

            if (!$latitude || !$longitude) {
                return $this->error([], 'User location not found', 400);
            }

            $user_preferences = UserPreference::where('user_id', $user->id)->pluck('category_id');
            $followee_ids = $user->followees->pluck('followee_id');

            $tailored_event_ids = BusinessProfile::whereIn('category_id', $user_preferences)
                ->orWhereHas('event_bookings', function ($query) use ($followee_ids) {
                    $query->whereIn('user_id', $followee_ids);
                })
                ->pluck('id');

            $random_events = BusinessProfile::selectRaw(
                "
                business_profiles.*, 
                (6371 * acos(cos(radians(?)) * cos(radians(business_profiles.latitude)) 
                * cos(radians(business_profiles.longitude) - radians(?)) 
                + sin(radians(?)) * sin(radians(business_profiles.latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )
                ->whereNotIn('id', $tailored_event_ids)
                // ->having('distance', '<', $radius)
                ->inRandomOrder()
                ->limit(10) // Limit to 10 random events
                ->with('business_hours', 'event_clicks', 'event_bookings')
                ->get()
                ->map(function ($event) {
                    $business_hour = $event->business_hours->first();
                    return [
                        'id' => $event->id,
                        'title' => $event->business_name,
                        'time' => $business_hour ? $business_hour->open_time . "-" . $business_hour->close_time : 'N/A',
                        'date' => $event->created_at->format('M d, Y'),
                        'location' => $event->location,
                        'cover' => $event->cover ? url($event->cover) : null,
                        'distance' => round($event->distance, 2) . ' km',
                        'score' => rand(1, 100),
                    ];
                });

            return $this->success($random_events, 'Random Events retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error([], 'Error retrieving random events: ' . $e->getMessage(), 500);
        }
    }
}


// START

// 1️⃣ **Discovery Events:**
//    1. Get user location (latitude, longitude)
//    2. Validate location data, return error if missing
//    3. Query `BusinessProfile`:
//       - Has `event_clicks`, `event_bookings`, or `event_reviews`
//       - Within a **30km radius** (Haversine formula)
//    4. Calculate **Discovery Score**:
//       - (signups * 5) + (clicks * 4) + (reviews_avg * 3) + (favorites * 2)
//    5. Sort events by `discovery_score` (descending)
//    6. Return results ✅

// 2️⃣ **Tailored Events:**
//    1. Authenticate user
//    2. Fetch **User Preferences** (`category_id`)
//    3. Fetch **Followees' Activities** (events they booked)
//    4. Query `BusinessProfile`:
//       - Match **category_id** (User Preferences)
//       - Match **Followees' bookings**
//    5. Merge results & remove duplicates
//    6. Return results ✅

// 3️⃣ **Random Events:**
//    1. Authenticate user
//    2. Fetch **IDs of Tailored Events** (both user preferences & followees' booked events)
//    3. Query `BusinessProfile` excluding these events
//    4. Assign **Random Score**:
//       - `rand(1, 100) + (clicks * 2) + (bookings * 3)`
//    5. Sort by `random_score` (descending)
//    6. Return results ✅

// END
