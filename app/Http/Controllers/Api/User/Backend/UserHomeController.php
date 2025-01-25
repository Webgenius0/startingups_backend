<?php

namespace App\Http\Controllers\Api\User\Backend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Story;
use App\Models\Country;
use App\Models\Category;
use App\Models\EventClick;
use App\Models\SubCategory;
use App\Traits\ApiResponse;
use App\Models\BusinessHour;
use App\Models\EventBooking;
use App\Models\BusinessProfile;
use App\Http\Controllers\Controller;

class UserHomeController extends Controller
{

    use ApiResponse;




    public function countries()
    {
        $countries = Country::all();

        return $this->success($countries, 'Countries retrieved successfully', 200);
    }

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

    public function sub_categories($category_id)
    {

        // dd($category_id);
        try {

            $sub_categories = SubCategory::where('category_id', $category_id)->get();

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

            // dd($user);

            $business_events = BusinessProfile::where('type', 'business_profile')->where('category_id', $category->id)
                // ->where(function ($query) use ($user) {
                //     $query->where('location', 'like', '%' . $user->city . '%')
                //         ->orWhere('location', 'like', '%' . $user->street_address . '%');
                // })
                // ->limit(5)
                ->get();

            // dd($business_events);

            $near_events = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::whereNotNull('open_time')->where('business_profile_id', $event->id)->get();
                $near_events = $near_events->merge($event_hours);
            }



            $near_events = $near_events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->created_at->format('M d, Y'),

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
                    // ->where('date', '>', Carbon::now()->format('d/m/Y'))
                    ->get();
                // dd($event_hours);
                $recommated_events = $recommated_events->merge($event_hours);
            }

            $recommated_events = $recommated_events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->created_at->format('d M'),

                    'location' => $event->business_profile->location,
                    'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,
                ];
            });

            // daily events

            $daily_events = collect();

            foreach ($business_events as $event) {
                $event_hours = BusinessHour::whereNotNull('open_time')->where('business_profile_id', $event->id)
                    // ->where('date', '>', Carbon::now()->format('d/m/Y'))
                    ->orderBy('day', 'desc')
                    ->get();


                $daily_events = $daily_events->merge($event_hours);
            }

            $daily_events = $daily_events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time,
                    'date' => $event->created_at->format('d M'),

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
                $event_hours = BusinessHour::whereNotnull('open_time')->where('business_profile_id', $event->id)->get();
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
                $event_hours = BusinessHour::whereNotnull('open_time')->where('business_profile_id', $event->id)
                    ->orderBy('day', 'asc')
                    ->get();


                $random_event = $random_event->merge($event_hours);
            }

            $random_event = $random_event->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->business_profile->business_name,
                    'time' => $event->open_time == null ? 'Closed' : $event->open_time,
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

        $upcoming_events = BusinessProfile::whereNull('frequency_end_date')
            ->where('date', '>', Carbon::now())
            ->orderBy('date', 'asc')
            ->get();

        // if ($upcoming_events->isEmpty()) {

        //     $user = auth()->user()->load('followees');

        //     $friends_events = BusinessProfile::whereHas('event_bookings', function ($query) use ($user) {
        //         $query->whereIn('user_id', $user->followees->pluck('followee_id'));
        //     })
        //         ->limit(5)
        //         ->get();

        //     $friends_events = $friends_events->map(function ($event) {
        //         return [
        //             'id' => $event->id,
        //             'title' => $event->title,
        //             'time' => Carbon::parse($event->date)->format('h:i A'),
        //             'date' => Carbon::parse($event->date)->format('d M Y'),
        //             'location' => $event->location_address,
        //             'cover' => $event->cover ? url($event->cover) : null,
        //         ];
        //     });

        //     $friends_events = 0;

        //     return $this->success($friends_events, 'Here are some events where your friends are going.', 200);
        // }

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
    }

    public function friend_events()
    {
        $user = auth()->user()->load('followees');

        // dd($user);

        $friends_events = BusinessProfile::whereHas('event_bookings', function ($query) use ($user) {
            $query->whereIn('user_id', $user->followees->pluck('followee_id'));
        })
            ->limit(5)
            ->get();
        //  dd($friends_events);
        $friends_events = $friends_events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title == null ?  $event->business_name : $event->title,
                'time' => Carbon::parse($event->date)->format('h:i A'),
                'date' => Carbon::parse($event->date)->format('d M Y'),
                'location' => $event->location_address == null ? $event->location : $event->location_address,
                'cover' => $event->cover ? url($event->cover) : null,
            ];
        });

        return $this->success($friends_events, 'Here are some events where your friends are going.', 200);
    }

    // _event details
    public function event_details($id)
    {
        $event = BusinessProfile::with('business_prices')->find($id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }

        $userId = auth()->id();

        $hasViewed = EventClick::where('user_id', $userId)
            ->where('business_profile_id', $id)
            ->exists();

        if (!$hasViewed) {
            $event->increment('view_count');

            // click events
            EventClick::create([
                'user_id' => auth()->user()->id,
                'business_profile_id' => $event->id,
                'last_click' => Carbon::now(),
            ]);
        }

        $event = [

            'user_id' => $event->user_id,
            'organizer' => $event->user->full_name,
            'organizer_avatar' => $event->user->avatar ? url($event->user->avatar) : null,

            'id' => $event->id,
            'title' => $event->title,
            'time' => Carbon::parse($event->date)->format('h:i A'),
            'date' => Carbon::parse($event->date)->format('d M Y'),
            'location' => $event->location_address,
            'cover' => $event->cover ? url($event->cover) : null,
            'description' => $event->description,

            'artist_or_guest' => json_decode($event->guest_list),

            'event_prices' => $event->business_prices,

        ];

        return $this->success($event, 'Event details retrieved successfully', 200);
    }

    public function category_event_details($id)
    {
        $event = BusinessProfile::with('business_prices')->find($id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }

        $userId = auth()->id();

        $hasViewed = EventClick::where('user_id', $userId)
            ->where('business_profile_id', $id)
            ->exists();

        if (!$hasViewed) {
            $event->increment('view_count');

            // click events
            EventClick::create([
                'user_id' => auth()->user()->id,
                'business_profile_id' => $event->id,
                'last_click' => Carbon::now(),
            ]);
        }

        $event = [

            'user_id' => $event->user_id,
            'organizer' => $event->user->full_name,
            'organizer_avatar' => $event->user->avatar ? url($event->user->avatar) : null,

            'id' => $event->id,
            'title' => $event->business_name,
            'time' => Carbon::parse($event->business_prices[0]->day)->format('h:i A'),
            'date' => Carbon::parse($event->date)->format('d M Y'),
            'location_address' => $event->location,

            'cover' => $event->cover ? url($event->cover) : null,
            'description' => $event->description,
            // 'location_type' => $event->location_type,

            // load business prices
            'event_prices' => $event->business_prices,

        ];

        return $this->success($event, 'Event details retrieved successfully', 200);
    }

    // user profile
    public function user_profile($id)
    {

        $user = User::find($id);

        $user = [

            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'avatar' => $user->avatar ? url($user->avatar) : null,
            'phone' => $user->phone,
        ];

        return $this->success($user, 'User profile retrieved successfully', 200);
    }

    // user recent places
    public function user_recent_places($id)
    {

        $user = User::find($id);

        // $user_recent_places = EventBooking::with('business_profile')->where('user_id', $user->id)->latest()->limit(10)->get();
        $user_recent_places = Story::with('user')->where('user_id', $user->id)->latest()->limit(10)->get();

        $user_recent_places = $user_recent_places->map(function ($event) {
            return [
                // 'story_id' => $event->business_profile->id,
                // 'event_name' => $event->business_profile->title == null ? $event->business_profile->business_name : $event->business_profile->title,
                // 'event_date' => $event->event_date,
                // 'event_time' => $event->event_time,
                // 'location' => $event->business_profile->location_address == null ? $event->business_profile->location : $event->business_profile->location_address,

                'story_id' =>  $event->id,
                'cover' => $event->cover ? url($event->cover) : '',
                'location' => $event->location

            ];
        });

        return $this->success($user_recent_places, 'User recent places retrieved successfully', 200);
    }

    // user preferences
    public function user_interested($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $preferences = json_decode($user->preferences);

        return $this->success($preferences, 'Preferences retrieved successfully.');
    }












    public function event_history()
    {
        $user = auth('api')->user();

        $event_bookings = EventBooking::with('business_profile')->where('user_id', $user->id)->get();
        // dd($event_bookings);


        $event_histories = $event_bookings->map(function ($event) {
            return [
                'event_id' => $event->business_profile->id,
                'title' => $event->business_profile->business_name == null ? $event->business_profile->title : $event->business_profile->business_name,

                'location' => $event->business_profile->location_address == null ? $event->business_profile->location :  $event->business_profile->location_address,
                'cover' => $event->business_profile->cover ? url($event->business_profile->cover) : null,

            ];
        });

        return $this->success($event_histories, 'Event history retrieved successfully', 200);
    }


    public function event_history_details($event_id)
    {
        $event = BusinessProfile::with('business_prices')->find($event_id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }



        $event = [

            'user_id' => $event->user_id,
            'organizer' => $event->user->full_name,
            'organizer_avatar' => $event->user->avatar ? url($event->user->avatar) : null,

            'id' => $event->id,
            'title' => $event->business_name == null ? $event->title : $event->business_name,
            'time' => Carbon::parse($event->business_prices[0]->day)->format('h:i A'),
            'date' => Carbon::parse($event->date)->format('d M Y'),
            'location_address' => $event->location == null ? $event->location_address : $event->location,

            'cover' => $event->cover ? url($event->cover) : null,
            'description' => $event->description,
            // 'location_type' => $event->location_type,  
        ];

        return $this->success($event, 'Event history details retrieved successfully', 200);
    }



    public function notifications()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $notifications = $user->notifications->filter(function ($notification) {
            return $notification->created_at->isToday();
        });

        // return only message and created_at
        $data = $notifications->map(function ($notification) {
            return [
                'message' => $notification->data['message'],
                'time' => $notification->created_at->diffForHumans(),
            ];
        });

        return $this->success($data, 'Today\'s notifications retrieved successfully.');
    }



    // previous day notifications
    public function previousDayNotifications()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $notifications = $user->notifications->filter(function ($notification) {
            return $notification->created_at->isToday();
        });

        $today = now()->format('Y-m-d');

        $data = $notifications->map(function ($notification) {
            return [
                'message' => $notification->data['message'],
                'time' => $notification->created_at->diffForHumans(),
            ];
        });

        return $this->success([
            'today' => $today,
            'notifications' => $data,
        ], 'Today\'s notifications retrieved successfully.');
    }
}
