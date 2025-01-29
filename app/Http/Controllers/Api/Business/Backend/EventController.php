<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Carbon\Carbon;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Mail\EventInviteMail;
use App\Models\BusinessProfile;
use App\Http\Controllers\Controller;
use App\Notifications\NewEventNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{

    use ApiResponse;

    // __store event


    public function store(Request $request)
    {
        try {
            // Validate the incoming request
            $data = $request->validate([
                'cover' => 'nullable|image|mimes:jpg,jpeg,png|max:4096',
                'title' => 'required|max:255',
                'category_id' => 'required',
                'age_min' => 'nullable|integer',
                'age_max' => 'nullable|integer',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'start_time' => 'required',
                'end_time' => 'required',


                'frequency' => 'nullable|in:once,daily,weekly,monthly',
                'frequency_count' => 'nullable|integer',
                'frequency_end_after' => 'nullable|integer',
                'frequency_end_date' => 'nullable|date',


                'location_type' => 'required|in:physical,virtual',
                'location_address' => 'nullable|string',
                'amount' => 'nullable',
                'offerings' => 'nullable|string',
                // 'has_guests' => 'nullable',
                // 'guest_list' => 'nullable|array',
                // 'guest_list.*' => 'nullable|email',
                // 'guest_options' => 'nullable|array',
                // 'guest_options.*' => 'nullable|string',
                // 'note_for_guests' => 'nullable|string',
                'prices' => 'required|array',
                'prices.*.type' => 'required|string',
                'prices.*.amount' => 'required',
                'prices.*.days' => 'required',
                'prices.*.offerings' => 'nullable|string',
            ]);

            // Handle cover image upload
            if ($request->hasFile('cover')) {
                $data['cover'] = Helper::uploadImage($request->file('cover'), 'events');
            }

            // Create the business event
            $business_event = new BusinessProfile();
            $business_event->type = 'event';
            $business_event->user_id = auth()->user()->id;
            $business_event->title = $data['title'];
            $business_event->category_id = $data['category_id'];
            $business_event->age_min = $data['age_min'];
            $business_event->age_max = $data['age_max'];
            $business_event->description = $data['description'];
            $business_event->date = $data['date'];
            $business_event->start_time = $data['start_time'];
            $business_event->end_time = $data['end_time'];
            $business_event->frequency = $data['frequency'];
            $business_event->frequency_count = $data['frequency_count'] ?? null;
            $business_event->frequency_end_after = $data['frequency_end_after'] ?? null;
            $business_event->frequency_end_date = $data['frequency_end_date'] ?? null;
            $business_event->location_address = $data['location_address'] ?? null;
            $business_event->location_type = $data['location_type'];
            $business_event->cover = $data['cover'] ?? null;
            $business_event->amount = $data['amount'] ?? null;
            $business_event->offerings = $data['offerings'] ?? null;

            $business_event->has_guests = $request->has_guests == true ? 1 : 0;
            $business_event->guest_list = isset($request->guest_list) ? json_encode($request->guest_list) : null;
            $business_event->guest_options = isset($request->guest_options) ? json_encode($request->guest_options) : null;
            $business_event->note_for_guests = $request->note_for_guests ?? null;



            $business_event->save();

            // Save prices
            foreach ($request->prices as $price) {
                $business_event->business_prices()->create([
                    'type' => $price['type'],
                    'amount' => $price['amount'],
                    'days' => isset($price['days']) ? $price['days'] : null,
                    'offerings' => $price['offerings'] ?? null,
                ]);
            }

            // Send guest invitations
            // if (isset($data['guest_list']) && is_array($data['guest_list'])) {
            //     foreach ($data['guest_list'] as $guestEmail) {
            //         Mail::to($guestEmail)->send(new EventInviteMail($business_event, $guestEmail));
            //     }
            // }

            // Handle recurring events
            if ($data['frequency'] !== 'once') {
                $this->createRecurringEvents($business_event, $data);
            }


           

            return $this->success($business_event, 'Event created successfully!', 200);
        } catch (ValidationException $e) {
            // Return a JSON response with validation errors
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }


    private function createRecurringEvents($business_event, $data)
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

            // dd($business_event->category_id);
            $new_business_event = BusinessProfile::create([
                'type' => 'event',
                'title' => $business_event->title,
                'cover' => $business_event->cover,
                'user_id' => auth()->user()->id,
                'category_id' => $business_event->category_id,
                'age_min' => $business_event->age_min,
                'age_max' => $business_event->age_max,
                'description' => $business_event->description,
                'date' => $currentDate->format('Y-m-d'),
                'start_time' => $business_event->start_time,
                'end_time' => $business_event->end_time,
                'frequency' => 'once',
                'location_type' => $business_event->location_type,
                'location_address' => $business_event->location_address,
                'amount' => $business_event->amount,
                'offerings' => $business_event->offerings,
                'has_guests' => $business_event->has_guests,
                'guest_list' => $business_event->guest_list,
                'guest_options' => $business_event->guest_options,
                'note_for_guests' => $business_event->note_for_guests,
            ]);


            // $businessProfile->business_hours()->delete(); // __clear existing hours
            // foreach ($validatedData['hours'] as $hour) {
            //     $businessProfile->business_hours()->create([
            //         'day' => $hour['day'],
            //         // 'date' => $hour['date'],
            //         'is_closed' => $hour['is_closed'],
            //         'open_time' => $hour['is_closed'] ? null : $hour['open_time'],
            //         'close_time' => $hour['is_closed'] ? null : $hour['close_time'],
            //     ]);
            // }

            // Create business_event prices for the recurring business_event
            foreach ($business_event->business_prices as $price) {
                $new_business_event->business_prices()->create([
                    'type' => $price->type,
                    'amount' => $price->amount,
                    'days' => $price->days,
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
