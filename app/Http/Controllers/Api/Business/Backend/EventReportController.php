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
    // __event reports

    public function all_event_reports(Request $request)
    {
        $user = auth('business')->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'data' => [],
                'code' => 404,
            ]);
        }

        $events = BusinessProfile::where('user_id', $user->id)
            ->with('event_clicks', 'event_bookings', 'event_reviews')
            ->get();

        $overall = [
            'link_clicks' => 0,
            'sign_ups' => 0,
            'revenue' => 0.00,
            'reported_customers' => 0,
        ];

        $eventAnalytics = $events->map(function ($event) use (&$overall) {
            $linkClicks = $event->event_clicks->count();
            $signUps = $event->event_bookings->count();
            $revenue = $event->event_bookings->sum('price');
            $reportedCustomers = $event->event_bookings->where('user_id', '!=', null)->count();
            $rating = $event->event_reviews->sum('rating');
            $reviewCount = $event->event_reviews->count();
            $averageRating = $reviewCount > 0 ? round($rating / $reviewCount, 2) : 0;

            $overall['link_clicks'] += $linkClicks;
            $overall['sign_ups'] += $signUps;
            $overall['revenue'] += $revenue;
            $overall['reported_customers'] += $reportedCustomers;

            $eventDate = Carbon::parse($event->date)->format('F j, Y');
            $startTime = Carbon::parse($event->start_time)->format('g:i A');
            $endTime = Carbon::parse($event->end_time)->format('g:i A');

            return [
                'event_id' => $event->id,
                'event_name' => $event->business_name == null ?  $event->title : $event->business_name,
                'event_date' => $eventDate,
                'event_time' => $startTime . ' - ' . $endTime,
                'average_rating' => $averageRating,
                'link_clicks' => $linkClicks,
                'sign_ups' => $signUps,
                'revenue' => $revenue,
                'reported_customers' => $reportedCustomers,
            ];
        });

        $overallData = collect($overall)->map(function ($value, $key) {
            return [
                'name' => $key,
                'value' => $value,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'All event analytics fetched successfully.',
            'data' => [
                'events' => $eventAnalytics,
                // 'overall' => $overallData,
            ],
            'code' => 200,
        ]);
    }

    public function event_details(Request $request)
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
            'Link Clicks' => 0,
            'Sign Ups' => 0,
            'Revenue' => 0.00,
            'Repeat Customers' => 0,
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
            'message' => 'Event analytics fetched successfully.',
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
        // dd($reviewCount);
        $ratingPercentages = [];

        for ($i = 5; $i >= 1; $i--) {
            $ratingPercentages[$i] = $reviewCount > 0 ? round(($ratingCounts->get($i, 0) / $reviewCount) * 100, 1) : 0;
        }

        $rating = $event->event_reviews->sum('rating');

        $averageRating = $reviewCount > 0 ? round($rating / $reviewCount, 1) : 0;

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
        ], 'Event ratings fetched successfully.');
    }


    public function signle_event_reports(Request $request, $eventId)
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
            'event_id' => $event->id,
            'title' => $event->title,
            'statistics' => [
                'link_clicks' => [
                    'total' => $linkClicks,
                    'change_percentage' => $this->calculatePercentageChange($linkClicks, $event->event_clicks->count()),
                    'trend_data' => $trendData['link_clicks'],
                ],
                'sign_ups' => [
                    'total' => $signUps,
                    'change_percentage' => $this->calculatePercentageChange($signUps, $event->event_bookings->count()),
                    'trend_data' => $trendData['sign_ups'],
                ],
                'revenue' => [
                    'total' => $revenue,
                    'change_percentage' => $this->calculatePercentageChange($revenue, $event->event_bookings->sum('price')),
                    'trend_data' => $trendData['revenue'],
                ],
                'repeat_customers' => [
                    'total' => $repeatCustomers,
                    'trend_data' => $trendData['repeat_customers'],
                ],
            ],
        ], 'Event report fetched successfully.');
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


    public function event_analysis(Request $request)
    {
        $user = auth('business')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        // Get the filter (daily, weekly, monthly)
        $filter = $request->input('filter', 'daily');
        $startDate = $this->getStartDateByFilter($filter);

        // Fetch all events and related data
        $events = BusinessProfile::with(['event_clicks', 'event_bookings'])->where('user_id', $user->id)->get();

        // Combine data from all events
        $combinedClicks = $events->flatMap(fn($event) => $event->event_clicks)
            ->where('created_at', '>=', $startDate);
        $combinedBookings = $events->flatMap(fn($event) => $event->event_bookings)
            ->where('created_at', '>=', $startDate);

        // Calculate totals
        $totalLinkClicks = $combinedClicks->count();
        $totalSignUps = $combinedBookings->count();
        $totalRevenue = $combinedBookings->sum('price');
        $totalRepeatCustomers = $combinedBookings->whereNotNull('user_id')->unique('user_id')->count();

        // Generate trend data
        $trendData = [
            'link_clicks' => $this->getTrendDataWithDayNames($combinedClicks),
            'sign_ups' => $this->getTrendDataWithDayNames($combinedBookings),
            'revenue' => $this->getRevenueTrendDataWithDayNames($combinedBookings),
            'repeat_customers' => $this->getRepeatCustomersTrendDataWithDayNames($combinedBookings),
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

    /**
     * Get the start date based on the filter.
     */
    private function getStartDateByFilter($filter)
    {
        switch ($filter) {
            case 'daily':
                return Carbon::now()->subDays(7); // Fetch the last 7 days for "daily"
            case 'weekly':
                return Carbon::now()->subWeeks(4); // Fetch the last 4 weeks
            case 'monthly':
                return Carbon::now()->subMonths(6); // Fetch the last 6 months
            default:
                return Carbon::now();
        }
    }

    /**
     * Get trend data grouped by created date with day names.
     */
    private function getTrendDataWithDayNames($data)
    {
        return $data->groupBy(function ($item) {
            $date = Carbon::parse($item->created_at);
            return $date->format('Y-m-d') . ' (' . $date->format('l') . ')'; // Format: "2025-01-21 (Tuesday)"
        })->map(function ($group) {
            return $group->count();
        })->toArray();
    }

    /**
     * Get revenue trend data grouped by created date with day names.
     */
    private function getRevenueTrendDataWithDayNames($bookings)
    {
        return $bookings->groupBy(function ($booking) {
            $date = Carbon::parse($booking->created_at);
            return $date->format('Y-m-d') . ' (' . $date->format('l') . ')'; // Format: "2025-01-21 (Tuesday)"
        })->map(function ($group) {
            return $group->sum('price');
        })->toArray();
    }

    /**
     * Get repeat customers trend data grouped by created date with day names.
     */
    private function getRepeatCustomersTrendDataWithDayNames($bookings)
    {
        return $bookings->groupBy(function ($booking) {
            $date = Carbon::parse($booking->created_at);
            return $date->format('Y-m-d') . ' (' . $date->format('l') . ')'; // Format: "2025-01-21 (Tuesday)"
        })->map(function ($group) {
            return $group->unique('user_id')->count();
        })->toArray();
    }
}
