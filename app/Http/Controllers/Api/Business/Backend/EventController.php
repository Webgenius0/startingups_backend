<?php

namespace App\Http\Controllers\Api\Business\Backend;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\EventInviteMail;
use App\Models\Event;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EventController extends Controller
{

    use ApiResponse;

    // __store event
    public function store(Request $request)
    {

        // dd($request->all());
        $data = $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required',
            'age_min' => 'nullable|integer',
            'age_max' => 'nullable|integer',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'frequency' => 'required|in:once,daily,weekly,monthly',
            'frequency_count' => 'nullable|integer',
            'frequency_end_after' => 'nullable|integer',
            'frequency_end_date' => 'nullable|date',
            'location_type' => 'required|in:physical,virtual',
            'location_address' => 'nullable|string',
            'amount' => 'nullable',
            'offerings' => 'nullable|string',
            'has_guests' => 'required|boolean',
            'guest_list' => 'nullable|array',
            'guest_list.*' => 'required',
            'guest_options' => 'nullable|array',
            'guest_options.*' => 'string',
            'note_for_guests' => 'nullable|string',

            // 'prices' => 'required|array',
            // 'prices.*.type' => 'required|string',
            // 'prices.*.amount' => 'required',
            // 'prices.*.offerings' => 'nullable|string',

        ]);

        $data['user_id'] = auth()->user()->id;

        if (is_string($data['guest_list'])) {
            $data['guest_list'] = json_decode($data['guest_list'], true);
        }

        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover') ? Helper::uploadImage($request->file('cover'), 'events') : null;
            $data['cover'] = $coverPath ?? null;

        }

        $event = Event::create($data);

        // $event->business_prices()->delete(); // __clear existing hours
        foreach ($request->prices as $price) {
            $event->event_prices()->create([
                'type' => $price['type'],
                'amount' => $price['amount'],
                'offerings' => $price['offerings'],

            ]);
        }

        // event prices

        if (isset($data['guest_list']) && is_array($data['guest_list'])) {
            foreach ($data['guest_list'] as $guestEmail) {
                Mail::to($guestEmail)->send(new EventInviteMail($event, $guestEmail));
            }
        }

        if ($data['frequency'] !== 'once') {
            $this->createRecurringEvents($event, $data);
        }

        return $this->success($event, 'Event created successfully!', 200);

    }

    private function createRecurringEvents($event, $data)
    {
        $currentDate = Carbon::parse($data['date']);
        $endDate = $data['frequency_end_date'] ? Carbon::parse($data['frequency_end_date']) : null;
        $count = 0;
        $maxOccurrences = $data['frequency_end_after'] ?? 10;

        while ($count < $maxOccurrences) {
            if ($endDate && $currentDate->greaterThan($endDate)) {
                break;
            }

            $currentDate = $this->getNextDate($currentDate, $data['frequency'], $data['frequency_count'] ?? 1);

            // dd($event->category_id);
            $new_event = Event::create([
                'title' => $event->title,
                'cover' => $event->cover,
                'user_id' => auth()->user()->id,
                'category_id' => $event->category_id,
                'age_min' => $event->age_min,
                'age_max' => $event->age_max,
                'description' => $event->description,
                'date' => $currentDate->format('Y-m-d'),
                'start_time' => $event->start_time,
                'end_time' => $event->end_time,
                'frequency' => 'once',
                'location_type' => $event->location_type,
                'location_address' => $event->location_address,
                'amount' => $event->amount,
                'offerings' => $event->offerings,
                'has_guests' => $event->has_guests,
                'guest_list' => $event->guest_list,
                'guest_options' => $event->guest_options,
                'note_for_guests' => $event->note_for_guests,
            ]);

            // Create event prices for the recurring event
            foreach ($event->event_prices as $price) {
                $new_event->event_prices()->create([
                    'type' => $price->type,
                    'amount' => $price->amount,
                    'offerings' => $price->offerings,
                ]);
            }

            $count++;
        }
    }

    private function getNextDate($currentDate, $frequency, $count)
    {
        switch ($frequency) {
            case 'daily':
                return $currentDate->addDays($count);
            case 'weekly':
                return $currentDate->addWeeks($count);
            case 'monthly':
                return $currentDate->addMonths($count);
            default:
                return $currentDate;
        }
    }

}
