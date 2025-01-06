<?php

namespace App\Http\Controllers\Api\Business\Backend;

use App\Http\Controllers\Controller;
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
            return $this->error([], 'User not found.', 404);
        }

        $events = Event::where('user_id', $user->id)->with('event_clicks', 'event_bookings', 'event_reviews')->get();

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
            $averageRating = $reviewCount > 0 ? $rating / $reviewCount : 0;

            $overall['link_clicks'] += $linkClicks;
            $overall['sign_ups'] += $signUps;
            $overall['revenue'] += $revenue;
            $overall['reported_customers'] += $reportedCustomers;

            $eventDate = Carbon::parse($event->date)->format('F j, Y');
            $startTime = Carbon::parse($event->start_time)->format('g:i A');
            $endTime = Carbon::parse($event->end_time)->format('g:i A');

            return [
                'event_id' => $event->id,
                'event_name' => $event->title,
                'event_date' => $eventDate,
                'event_time' => $startTime . ' - ' . $endTime,
                'event_reviews' => $averageRating,

                'link_clicks' => $linkClicks,
                'sign_ups' => $signUps,
                'revenue' => $revenue,
                'reported_customers' => $reportedCustomers,
            ];
        });

        return $this->success([
            'events' => $eventAnalytics,
            'overall' => $overall,
        ], 'All event analytics fetched successfully.');
    }

    public function event_details(Request $request)
    {
        // Get the authenticated user
        $user = auth('business')->user();

        // If user not found
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Fetch all events for the user
        $events = Event::where('user_id', $user->id)
            ->with('event_clicks', 'event_bookings')->get();

        // Initialize overall totals
        $totals = [
            'link_clicks' => 0,
            'sign_ups' => 0,
            'revenue' => 0.00,
            'repeat_customers' => 0,
        ];

        // Fetch previous analytics data (Example: Last Week)
        $previousEvents = Event::where('user_id', $user->id)
            ->where('created_at', now()->subMonth())
            ->with('event_clicks', 'event_bookings')
            ->get();
        // dd($previousEvents);

        // Initialize previous totals
        $previousTotals = [
            'link_clicks' => 0,
            'sign_ups' => 0,
            'revenue' => 0.00,
            'repeat_customers' => 0,
        ];

        // Calculate current totals
        foreach ($events as $event) {
            $totals['link_clicks'] += $event->event_clicks->count();
            $totals['sign_ups'] += $event->event_bookings->count();
            $totals['revenue'] += $event->event_bookings->sum('price');
            $totals['repeat_customers'] += $event->event_bookings->where('user_id', '!=', null)->count();
        }

        // Calculate previous totals
        foreach ($previousEvents as $event) {
            $previousTotals['link_clicks'] += $event->event_clicks->count();
            $previousTotals['sign_ups'] += $event->event_bookings->count();
            $previousTotals['revenue'] += $event->event_bookings->sum('price');
            $totals['repeat_customers'] += $event->event_bookings->where('user_id', '!=', null)->count();

        }

        // Calculate percentage change
        $percentageChange = [];
        foreach ($totals as $key => $value) {
            $previousValue = $previousTotals[$key] ?? 0;
            if ($previousValue != 0) {

                $percentageChange[$key] = round((($value - $previousValue) / $previousValue) * 100, 2);
            } else {

                $percentageChange[$key] = ($value > 0) ? 100 : 0;
            }
        }

        // Return response with analytics and percentage changes
        $data = [
            'overall' => $totals,
            'percentage_change' => $percentageChange,
        ];

        return $this->success($data, 'Event analytics fetched successfully.');
    }



    // event ratings
    public function event_ratings(Request $request, $id)
    {

        $event = Event::with(['user', 'event_reviews.user'])->find($id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }

        $rating = $event->event_reviews->sum('rating');
        $reviewCount = $event->event_reviews->count();
        $averageRating = $reviewCount > 0 ? round($rating / $reviewCount, 1) : 0;

        $reviews = $event->event_reviews->map(function ($review) {
            return [
                'review_id' => $review->id,
                'user_name' => $review->user->full_name ?? 'Anonymous',
                'avatar' => $review->user->avatar ?? null,
                'rating' => $review->rating,
                'review_comment' => $review->review ?? '',
                'review_date' => $review->created_at->format('F j, Y, g:i A'),
            ];
        });

        return $this->success([
            // 'event_name' => $event->title,
            'event_id' => $event->id,
            'average_rating' => $averageRating,
            'total_reviews' => $reviewCount,

            'reviews' => $reviews,
        ], 'Event ratings fetched successfully.');
    }

    public function signle_event_reports(Request $request, $eventId)
    {
        $user = auth('business')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $event = Event::where('id', $eventId)->where('user_id', $user->id)
            ->with('event_clicks', 'event_bookings', 'event_reviews')
            ->first();

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

}
