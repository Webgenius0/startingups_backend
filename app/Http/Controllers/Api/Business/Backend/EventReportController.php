<?php

namespace App\Http\Controllers\Api\Business\Backend;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Event;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EventReportController extends Controller
{

    use ApiResponse;


    public function all_profile_report(Request $request)
    {
        $user = auth('business')->user();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $events = BusinessProfile::where('user_id', $user->id)
            ->with('event_clicks', 'event_bookings')->get();
        // dd($events);

        $totals = [
            'link_clicks' => 0,
            'sign_ups' => 0,
            'revenue' => 0.00,
            'repeat_customers' => 0,
        ];

        $previousEvents = BusinessProfile::where('user_id', $user->id)
            ->whereBetween('created_at', [now()->subMonth(), now()])
            ->with('event_clicks', 'event_bookings')
            ->get();

        $previousTotals = [
            'link_clicks' => 0,
            'sign_ups' => 0,
            'revenue' => 0.00,
            'repeat_customers' => 0,
        ];

        foreach ($events as $event) {
            $totals['link_clicks'] += $event->event_clicks->count();
            $totals['sign_ups'] += $event->event_bookings->count();
            $totals['revenue'] += $event->event_bookings->sum('price');
            $totals['repeat_customers'] += $event->event_bookings->where('user_id', '!=', null)->count();
        }

        foreach ($previousEvents as $event) {
            $previousTotals['link_clicks'] += $event->event_clicks->count();
            $previousTotals['sign_ups'] += $event->event_bookings->count();
            $previousTotals['revenue'] += $event->event_bookings->sum('price');
            $previousTotals['repeat_customers'] += $event->event_bookings->where('user_id', '!=', null)->count();
        }

        $percentageChange = [];
        foreach ($totals as $key => $value) {
            $previousValue = $previousTotals[$key] ?? 0;
            if ($previousValue != 0) {
                $percentageChange[$key] = round((($value - $previousValue) / $previousValue) * 100, 2);
            } else {
                $percentageChange[$key] = ($value > 0) ? 100 : 0;
            }
        }

        // Format response
        $responseData = [];
        foreach ($totals as $key => $value) {
            $responseData[] = [
                'name' => $key,
                'click' => $value,
                'percentage' => $percentageChange[$key],
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Business profile analytics fetched successfully.',
            'data' => $responseData,
            'code' => 200,
        ]);
    }




    // event ratings
    public function event_ratings(Request $request, $id)
    {
        $event = BusinessProfile::with(['user', 'event_reviews.user'])->find($id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }

        $ratingCounts = $event->event_reviews->groupBy('rating')->map(function ($reviews, $rating) {
            return count($reviews);
        });

        $reviewCount = $event->event_reviews->count();
        $ratingPercentages = [];
        
        $ratings = ['a' => 5, 'b' => 4, 'c' => 3, 'd' => 2, 'e' => 1];
        
        foreach ($ratings as $key => $value) {
            if ($reviewCount > 0) {
                $percentage = round(($ratingCounts->get($value, 0) / $reviewCount) * 100, 2);
            } else {
                $percentage = number_format(0, 2, '.', ''); // Ensures it's 0.00
            }
            $ratingPercentages[$key] = $percentage; // Cast to float if needed
        }
        

        $rating = $event->event_reviews->sum('rating');

        $averageRating = $reviewCount > 0 ? round($rating / $reviewCount, 1) : 0.00;

        $reviews = $event->event_reviews->map(function ($review) {
            return [
                'review_id' => $review->id,
                'user_id' => $review->user_id,
                'user_name' => $review->user->full_name ?? 'Anonymous',
                'avatar' => $review->user ? url($review->user->avatar) : null,
                'rating' => $review->rating,
                'review_comment' => $review->review ?? '',
                'review_cover' => $review->cover ? url($review->cover) : null,
                'review_date' => $review->created_at->format('F j, Y, g:i A'),
            ];
        });

        return $this->success([
            'event_id' => $event->id,
            'average_rating' => $averageRating,
            'total_reviews' => $reviewCount,
            'rating_percentages' => $ratingPercentages,
            'reviews' => $reviews,
        ], 'Business profile ratings fetched successfully.');
    }


    public function business_profile_report(Request $request, $eventId)
    {
        $user = auth('business')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $event = BusinessProfile::with('event_clicks', 'event_bookings', 'event_reviews')->find($eventId);

        if (!$event) {
            return $this->error([], 'Event not found.', 404);
        }

        $filter = $request->input('filter', 'monthly');

        $startDate = Carbon::now();
        switch ($filter) {
            case 'daily':
                $startDate = Carbon::now()->startOfDay();
                break;
            case 'weekly':
                $startDate = Carbon::now()->startOfWeek();
                break;
            case 'monthly':
                $startDate = Carbon::now()->startOfMonth();
                break;
        }

        $filteredClicks = $event->event_clicks->where('created_at', '>=', $startDate);
        $filteredBookings = $event->event_bookings->where('created_at', '>=', $startDate);

        $linkClicks = $filteredClicks->count();
        $signUps = $filteredBookings->count();
        $revenue = $filteredBookings->sum('price');
        $repeatCustomers = $filteredBookings->where('user_id', '!=', null)->count();

        $trendData = [
            'link_clicks' => $this->getTrendData($filteredClicks),
            'sign_ups' => $this->getTrendData($filteredBookings),
            'revenue' => $this->getRevenueTrendData($filteredBookings, $startDate, $filter),
            'repeat_customers' => $this->getRepeatCustomersTrendData($filteredBookings, $startDate, $filter),
        ];

        return $this->success([

            'link_clicks' => [
                'total' => $linkClicks,
                'change_percentage' => $this->calculatePercentageChange($linkClicks, $event->event_clicks->count()),
                'trend_data' => $linkClicks ? $this->formatTrendData($trendData['link_clicks']) : [
                    ['x' => Carbon::now()->format('y-m-d'), 'y' => 0]
                ],
            ],

            'sign_ups' => [
                'total' => $signUps,
                'change_percentage' => $this->calculatePercentageChange($signUps, $event->event_bookings->count()),
                'trend_data' => $signUps ? $this->formatTrendData($trendData['sign_ups']) :
                    [
                        ['x' => Carbon::now()->format('y-m-d'), 'y' => 0]
                    ],
            ],

            'revenue' => [
                'total' => $revenue,
                'change_percentage' => $this->calculatePercentageChange($revenue, $event->event_bookings->sum('price')),
                'trend_data' => $revenue ? $this->formatTrendData($trendData['revenue']) :
                    [
                        ['x' => Carbon::now()->format('y-m-d'), 'y' => 0]
                    ],
            ],

            'repeat_customers' => [
                'total' => $repeatCustomers,
                'change_percentage' => $this->calculatePercentageChange($repeatCustomers, $event->event_bookings->where('user_id', '!=', null)->count()),
                'trend_data' => $repeatCustomers ? $this->formatTrendData($trendData['repeat_customers']) :
                    [
                        ['x' => Carbon::now()->format('y-m-d'), 'y' => 0]
                    ],
            ],


        ], 'Business Proile report fetched successfully.');
    }

    private function formatTrendData($trendData)
    {
        return array_map(function ($date, $value) {
            return [
                'x' => \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('y-m-d'), // Format as YY-MM-DD
                'y' => $value,
            ];
        }, array_keys($trendData), $trendData);
    }


    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return ($current > 0) ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getTrendData($data)
    {
        return $data->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        })->toArray();
    }

    private function getRevenueTrendData($bookings, $startDate, $filter)
    {
        return $bookings->groupBy(function ($booking) use ($startDate, $filter) {
            $date = $booking->created_at;
            switch ($filter) {
                case 'daily':
                    return $date->format('Y-m-d');
                case 'weekly':
                    return $date->startOfWeek()->format('Y-m-d');
                case 'monthly':
                    return $date->startOfMonth()->format('Y-m-d');
                default:
                    return $date->format('Y-m-d');
            }
        })->map(function ($group) {
            return $group->sum('price');
        })->toArray();
    }

    private function getRepeatCustomersTrendData($bookings, $startDate, $filter)
    {
        return $bookings->groupBy(function ($booking) use ($startDate, $filter) {
            $date = $booking->created_at;
            switch ($filter) {
                case 'daily':
                    return $date->format('Y-m-d');
                case 'weekly':
                    return $date->startOfWeek()->format('Y-m-d');
                case 'monthly':
                    return $date->startOfMonth()->format('Y-m-d');
                default:
                    return $date->format('Y-m-d');
            }
        })->map(function ($group) {
            return $group->unique('user_id')->count();
        })->toArray();
    }


    // event analysis
    public function event_analysis(Request $request)
    {
        $user = auth('business')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $filter = $request->input('filter', 'monthly');
        $startDate = $this->getStartDateByFilter($filter);

        $events = BusinessProfile::with(['event_clicks', 'event_bookings'])
            ->where('user_id', $user->id)
            ->get();

        $combinedClicks = $events->flatMap(fn($event) => $event->event_clicks);
        $combinedBookings = $events->flatMap(fn($event) => $event->event_bookings);

        $totalLinkClicks = $combinedClicks->count();
        $totalSignUps = $combinedBookings->count();
        $totalRevenue = $combinedBookings->sum('price');
        $totalRepeatCustomers = $combinedBookings->whereNotNull('user_id')->unique('user_id')->count();

        $trendData = [
            'link_clicks' => $this->formatTrendData1($this->getTrendData($combinedClicks)),
            'sign_ups' => $this->formatTrendData1($this->getTrendData($combinedBookings)),
            'revenue' => $this->formatTrendData1($this->getRevenueTrendData($combinedBookings, $startDate, $filter)),
            'repeat_customers' => $this->formatTrendData1($this->getRepeatCustomersTrendData($combinedBookings, $startDate, $filter)),
        ];

        return $this->success(
            [
                'link_clicks' => [
                    'total' => $totalLinkClicks,
                    'trend_data' => $trendData['link_clicks'],
                ],
                'sign_ups' => [
                    'total' => $totalSignUps,
                    'trend_data' => $trendData['sign_ups'],
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'trend_data' => $trendData['revenue'],
                ],
                'repeat_customers' => [
                    'total' => $totalRepeatCustomers,
                    'trend_data' => $trendData['repeat_customers'],
                ],
            ],
            'Event analytics fetched successfully.'
        );
    }

    private function formatTrendData1($trendData)
    {
        return array_map(function ($date, $value) {
            return [
                'x' => Carbon::createFromFormat('Y-m-d', $date)->format('l, M j'),
                'y' => $value,
            ];
        }, array_keys($trendData), $trendData);
    }

    private function getStartDateByFilter($filter)
    {
        switch ($filter) {
            case 'daily':
                return Carbon::now()->subDays(7);
            case 'weekly':
                return Carbon::now()->subWeeks(4);
            case 'monthly':
                return Carbon::now()->subMonths(6);
            default:
                return Carbon::now();
        }
    }






    private function getTrendDataWithDayNames($data)
    {
        return $data->groupBy(function ($item) {
            $date = Carbon::parse($item->created_at);
            return $date->format('Y-m-d') . ' (' . $date->format('l') . ')';
        })->map(function ($group) {
            return $group->count();
        })->toArray();
    }


    private function getRevenueTrendDataWithDayNames($bookings)
    {
        return $bookings->groupBy(function ($booking) {
            $date = Carbon::parse($booking->created_at);
            return $date->format('Y-m-d') . ' (' . $date->format('l') . ')';
        })->map(function ($group) {
            return $group->sum('price');
        })->toArray();
    }


    private function getRepeatCustomersTrendDataWithDayNames($bookings)
    {
        return $bookings->groupBy(function ($booking) {
            $date = Carbon::parse($booking->created_at);
            return $date->format('Y-m-d') . ' (' . $date->format('l') . ')';
        })->map(function ($group) {
            return $group->unique('user_id')->count();
        })->toArray();
    }


    // schedule_events

    // public function schedule_events(Request $request)
    // {

    //     $startDate = $request->input('start_date')
    //         ? Carbon::parse($request->input('start_date'))
    //         : Carbon::now()->startOfMonth();

    //     $endDate = $request->input('end_date')
    //         ? Carbon::parse($request->input('end_date'))
    //         : Carbon::now()->endOfMonth();


    //     $events = BusinessProfile::where('user_id', auth('business')->id())
    //         ->orderBy('start_time') 
    //         ->get();


    //     $schedule = [];

    //     foreach ($events as $event) {

    //         $currentDate = Carbon::parse($event->start_time);
    //         $recurrenceType = $event->frequency; 

    //         while ($currentDate->lte($endDate)) {

    //             if ($currentDate->gte($startDate)) {
    //                 $dayWithDate = $currentDate->format('l, d'); 


    //                 if (!isset($schedule[$dayWithDate])) {
    //                     $schedule[$dayWithDate] = [
    //                         "title" => $event->title ?? $event->business_name,
    //                         "start_time" => Carbon::parse($event->start_time)->format('g:i A'),
    //                         "end_time" => Carbon::parse($event->end_time)->format('g:i A'),
    //                         "progress" => rand(0, 100), 
    //                         "location" => $event->location_address ?? $event->location,
    //                         "guests" => json_decode($event->guest_list), 
    //                     ];
    //                 }
    //             }


    //             if ($recurrenceType === 'once') {
    //                 break;
    //             }

    //             if ($recurrenceType === 'daily') {
    //                 $currentDate->addDay();

    //             } elseif ($recurrenceType === 'weekly') {
    //                 $currentDate->addWeek();

    //             } elseif ($recurrenceType === 'monthly') {
    //                 $currentDate->addMonth();
    //             }
    //         }
    //     }

    //     $schedule = collect($schedule)->map(function ($event, $day) {
    //         return [$day => $event]; 
    //     })->values();

    //     return $this->success($schedule, 'Schedule events fetched successfully.');
    // }

    public function schedule_events(Request $request)
    {
        $selectedDate = $request->input('date')
            ? Carbon::parse($request->input('date'))->toDateString()
            : Carbon::now()->toDateString();


        $events = BusinessProfile::with('business_hours')->where('user_id', auth('business')->id())->get();

        $eventList = [];

        foreach ($events as $event) {

            $business_hour = $event->business_hours->first();

            $eventList[] = [

                "id" => $event->id,
                'title' => $event->business_name,
                'date' => Carbon::parse($event->created_at)->format('F j, Y'),
                "start_time" => Carbon::parse($business_hour->open_time)->format('g:i A'),
                "end_time" => Carbon::parse($business_hour->close_time)->format('g:i A'),
                "progress" => rand(0, 100),
                "location" => $event->location_address ?? $event->location,
                "guests" => json_decode($event->guest_list),

            ];
        }

        return $this->success($eventList, 'Schedule events fetched successfully.');
    }
}
