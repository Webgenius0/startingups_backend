<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventClick;
use App\Models\SubCategory;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class UserHomeController extends Controller
{

    use ApiResponse;

    // _categories
    public function categories()
    {
        try {

            $categories = Category::with('sub_categories')->get();

            if ($categories->isEmpty()) {
                return $this->error([], 'No categories found', 404);
            }

            $categories->map(function ($category) {
                $category->image = $category->image ? url($category->image) : null;
            });

            return $this->success($categories, 'Categories retrieved successfully', 200);

        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving categories: ' . $e->getMessage(), 500);
        }
    }


    public function sub_categories()
    {
        try {

            $sub_categories = SubCategory::all();

            if ($sub_categories->isEmpty()) {
                return $this->error([], 'No categories found', 404);
            }

            $sub_category = $sub_categories->map(function ($sub_category) {
                $sub_category->id = $sub_category->id;
                $sub_category->name = $sub_category->name;
                return $sub_category;
            });


            return $this->success($sub_category, 'Sub Categories retrieved successfully', 200);

        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving categories: ' . $e->getMessage(), 500);
        }
    }

    // _categories event details
    public function explore_event($id)
    {

        // dd(Carbon::now()->format('d/m/Y'));
        try {

            $category = Category::find($id);

            if (!$category) {
                return $this->error([], 'Category not found', 404);
            }

            $user = auth()->user();

            $business_events = BusinessProfile::where('category_id', $category->id)
                ->where(function ($query) use ($user) {
                    $query->where('location', 'like', '%' . $user->city . '%')
                        ->orWhere('location', 'like', '%' . $user->street_address . '%');
                })
                ->get();

            $near_events = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::where('business_profile_id', $event->id)->get();
                $near_events = $near_events->merge($event_hours);
            }

            $near_events = $near_events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->date,
                    'location' => $event->business_profile->location,
                    'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,
                ];
            });

            if ($near_events->isEmpty()) {
                return $this->error([], 'No events found near you', 404);
            }

            // return $near_events;

            // recommated events

            $business_events = BusinessProfile::where('category_id', $category->id)->get();

            $recommated_events = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::where('business_profile_id', $event->id)
                    ->where('date', '>', Carbon::now()->format('d/m/Y'))
                    ->get();
                // dd($event_hours);
                $recommated_events = $recommated_events->merge($event_hours);

            }

            $recommated_events = $recommated_events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->date,
                    'location' => $event->business_profile->location,
                    'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,
                ];
            });

            // daily events

            $daily_events = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::where('business_profile_id', $event->id)
                    ->where('date', '>', Carbon::now()->format('d/m/Y'))
                    ->get();
                $daily_events = $daily_events->merge($event_hours);
            }

            $daily_events = $daily_events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->date,
                    'location' => $event->business_profile->location,
                    'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,
                ];
            });

            return $this->success([
                'near_events' => $near_events,
                'recommated_events' => $recommated_events,
                'daily_events' => $daily_events,
            ], 'Events retrieved successfully', 200);

        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }

    // __tailored events
    public function tailored_event($id)
    {
        try {

            $category = Category::find($id);

            // $user = auth()->user();

            $business_events = BusinessProfile::where('category_id', $category->id)->get();

            $tailored_event = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::where('business_profile_id', $event->id)->get();
                $tailored_event = $tailored_event->merge($event_hours);
            }

            $tailored_event = $tailored_event->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->date,
                    'location' => $event->business_profile->location,
                    'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,
                ];
            });

            return $this->success($tailored_event, 'Tailored Events retrieved successfully', 200);

        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }

    // __random events
    public function random_event($id)
    {

        try {

            $category = Category::find($id);

            if (!$category) {
                return $this->error([], 'Category not found', 404);
            }

            // random events
            $business_events = BusinessProfile::where('category_id', $category->id)->get();

            $random_event = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::where('business_profile_id', $event->id)
                    ->orderBy('day', 'asc')
                    ->get();
                $random_event = $random_event->merge($event_hours);
            }

            $random_event = $random_event->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->date,
                    'location' => $event->business_profile->location,
                    'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,
                ];
            });

            return $this->success($random_event, 'Random Events retrieved successfully', 200);

        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving events: ' . $e->getMessage(), 500);
        }
    }

    // _events
    public function events()
    {
        // upcoming events where frequency_end_date not null

        $upcoming_events = Event::whereNull('frequency_end_date')->
            where('date', '>', Carbon::now())
            ->orderBy('date', 'asc')
            ->get();

        // If no upcoming events, fetch friends' events
        if ($upcoming_events->isEmpty()) {

            // $friends_events = Event::whereHas('visits', function ($query) use ($user) {
            //     $query->whereIn('user_id', $user->friends->pluck('id'));
            // })
            //     ->orderBy('date', 'desc')
            //     ->limit(5)
            //     ->get();

            // $friends_events = $friends_events->map(function ($event) {
            //     return [
            //         'id' => $event->id,
            //         'title' => $event->title,
            //         'time' => Carbon::parse($event->date)->format('h:i A'),
            //         'date' => Carbon::parse($event->date)->format('d M Y'),
            //         'location' => $event->location_address,
            //         'cover' => $event->cover ? url($event->cover) : null,
            //     ];
            // });

            $friends_events = 0;

            return $this->success($friends_events, 'Here are some events where your friends are going.', 200);
        }
        $upcoming_events = $upcoming_events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'time' => Carbon::parse($event->date)->format('h:i A'),
                'date' => Carbon::parse($event->date)->format('d M Y'),
                'location' => $event->location_address,
                'cover' => $event->cover ? url($event->cover) : null,

            ];
        });

        return $this->success($upcoming_events, 'Upcoming events retrieved successfully', 200);

        // past events
        // $past_events = Event::where('status', 'active')
        //     ->where('date', '<', Carbon::now())
        //     ->orderBy('date', 'desc')
        //     ->get();

    }

    // _event details
    public function event_details($id)
    {
        $event = Event::with('event_prices')->find($id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }

        $userId = auth()->id();

        $hasViewed = EventClick::where('user_id', $userId)
            ->where('event_id', $id)
            ->exists();

        if (!$hasViewed) {
            $event->increment('view_count');

            // click events
            EventClick::create([
                'user_id' => auth()->user()->id,
                'event_id' => $event->id,
                'last_click' => Carbon::now(),
            ]);
        }

        $event = [

            'user_id' => $event->user_id,
            'user_name' => $event->user->name,
            'user_image' => $event->user->image ? url($event->user->image) : null,

            'id' => $event->id,
            'title' => $event->title,
            'time' => Carbon::parse($event->date)->format('h:i A'),
            'date' => Carbon::parse($event->date)->format('d M Y'),
            'location' => $event->location_address,
            'cover' => $event->cover ? url($event->cover) : null,
            'description' => $event->description,
            'location_type' => $event->location_type,
            'location_address' => $event->location_address,
            'amount' => $event->amount,
            'offerings' => $event->offerings,
            'guest_list' => $event->guest_list,
            'event_prices' => $event->event_prices,

        ];

        return $this->success($event, 'Event details retrieved successfully', 200);
    }

}
