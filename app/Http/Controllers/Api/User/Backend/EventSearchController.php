<?php

namespace App\Http\Controllers\Api\User\Backend;

use Illuminate\Http\Request;
use App\Models\UserPreference;
use App\Models\BusinessProfile;
use App\Http\Controllers\Controller;

class EventSearchController extends Controller
{
    // _categories event details
    public function explore_event()
    {

        try {

            $business_events = BusinessProfile::with('business_hours', 'event_clicks', 'event_bookings')
                ->where(function ($query) {
                    $query->whereHas('event_clicks')
                        ->orWhereHas('event_bookings');
                })
                ->get();

            $near_events = $business_events->map(function ($event) {
                $business_hour = $event->business_hours->first();
                return [
                    'id' => $event->id,
                    'title' => $event->business_name,
                    'time' => $business_hour ? $business_hour->open_time . "-" .  $business_hour->close_time  : 'N/A',
                    'date' => $event->created_at->format('M d, Y'),
                    'location' => $event->location,
                    'cover' => $event->cover ? url($event->cover) : null,
                    // 'click_count' => $event->event_clicks->count(),  // Count clicks
                    // 'booking_count' => $event->event_bookings->count(), // Count bookings
                ];
            });


            $recommated_events  = BusinessProfile::with('business_hours', 'event_clicks', 'event_bookings')
                // ->where('type', 'business_profile')
                // ->where(function ($query) {
                //     $query->whereHas('event_clicks')
                //         ->orWhereHas('event_bookings');
                // })
                ->get();

            $recommated_events = $recommated_events->map(function ($event) {
                $business_hour = $event->business_hours->first();
                return [
                    'id' => $event->id,
                    'title' => $event->business_name,
                    'time' => $business_hour ? $business_hour->open_time . "-" .  $business_hour->close_time  : 'N/A',
                    'date' => $event->created_at->format('M d, Y'),
                    'location' => $event->location,
                    'cover' => $event->cover ? url($event->cover) : null,
                    // 'click_count' => $event->event_clicks->count(),  // Count clicks
                    // 'booking_count' => $event->event_bookings->count(), // Count bookings
                ];
            });


            return $this->success([
                'near_events' => $near_events,
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

            $preferredCategories = UserPreference::where('user_id', $user->id)->pluck('category_id');
            // dd($preferredCategories);
            $tailored_event  = BusinessProfile::whereIn('category_id', $preferredCategories)
                                                ->with('business_hours', 'event_clicks', 'event_bookings')
                                                ->get();

            
            $tailored_event = $tailored_event->map(function ($event) {
                $business_hour = $event->business_hours->first();
                return [
                    'id' => $event->id,
                    'title' => $event->business_name,
                    'time' => $business_hour ? $business_hour->open_time . "-" .  $business_hour->close_time  : 'N/A',
                    'date' => $event->created_at->format('M d, Y'),
                    'location' => $event->location,
                    'cover' => $event->cover ? url($event->cover) : null,
                    // 'click_count' => $event->event_clicks->count(),  // Count clicks
                    // 'booking_count' => $event->event_bookings->count(), // Count bookings
                ];
            });

            return $this->success($tailored_event, 'Tailored Events retrieved successfully', 200);
        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }

    // __random events
    public function random_event()
    {
        try {


            $business_events  = BusinessProfile::with('business_hours', 'event_clicks', 'event_bookings')->orderBy('created_at', 'desc')->get();

            $random_event = $business_events->map(function ($event) {
                $business_hour = $event->business_hours->first();
                return [
                    'id' => $event->id,
                    'title' => $event->business_name,
                    'time' => $business_hour ? $business_hour->open_time . "-" .  $business_hour->close_time  : 'N/A',
                    'date' => $event->created_at->format('M d, Y'),
                    'location' => $event->location,
                    'cover' => $event->cover ? url($event->cover) : null,
                    // 'click_count' => $event->event_clicks->count(),  // Count clicks
                    // 'booking_count' => $event->event_bookings->count(), // Count bookings
                ];
            });



            return $this->success($random_event, 'Random Events retrieved successfully', 200);
        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }

}
