<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Models\Event;
use App\Traits\ApiResponse;
use App\Models\EventBooking;
use Illuminate\Http\Request;
use App\Models\EventBookingQuest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewEventNotification;
use Illuminate\Support\Facades\Notification;

class EventBookingController extends Controller
{

    use ApiResponse;

    // __event booking
    public function event_book(Request $request, $id)
    {

        // dd($request->all());
        $validated = Validator::make($request->all(), [
            // 'event_id' => 'required|exists:business_profiles,id',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'age' => 'nullable|integer|min:0',
            'event_date' => 'required|date',
            'event_time' => 'required',
            'is_guest' => 'required',
            'guest_count' => 'nullable|integer|min:0',
            'guests' => 'required_if:is_guest,true|array',
            'guests.*.full_name' => 'required|string|max:255',
            'guests.*.age' => 'nullable|integer|min:0',
            'guests.*.phone' => 'nullable|string|max:15',

            // event price
            'event_price' => 'required',
        ]);

        // dd($validated);


        // validation erros show
        if ($validated->fails()) {
            return $this->error([], $validated->errors()->first(), 422);
        }


        // $event = Event::with('event_prices')->find($request->event_id);
        // $event->increment('total_bookings');


        // Create the main booking
        $booking = EventBooking::create([
            'business_profile_id' => $request->id,
            'user_id' => Auth::id(),
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'age' => $request->age,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'is_guest' => $request->is_guest,
            'guest_count' => $request->guest_count,
            'notes' => $request->notes,

            // event price
            'price' => $request->event_price,
            "status" => "pending",


        ]);

        // Store guests (if applicable)
        if ($request->is_guest) {
            foreach ($request->guests as $guest) {
                EventBookingQuest::create([
                    'event_booking_id' => $booking->id,
                    'full_name' => $guest['full_name'],
                    'phone' => $guest['phone'],
                    'age' => $guest['age'],
                ]);
            }
        }

        $event_owner = $booking->business_profile->user;
            
        Notification::send($event_owner, new NewEventNotification($booking));


        // event load with guests
        $booking = $booking->load('guests'); ;

        return $this->success($booking, 'Event booking successful!', 200);
    }





    // __event order summary
    public function order_summary($id)
    {
        // dd($id);

        $booking = EventBooking::with('guests', 'business_profile')->findOrFail($id);
        // dd($booking);

        $subtotal = $booking->price;
        $commissionFee = 0;
        $tax = 0;
        $grandTotal = $subtotal + $commissionFee + $tax;

        $data = [

            'booking_id' => $booking->id,
            'event_details' => [
                'event_id' => $booking->business_profile->id,
                'event_name' => $booking->business_profile->title == null ? $booking->business_profile->business_name : $booking->business_profile->title,
                'event_date' => $booking->event_date,
                'event_time' => $booking->event_time,
                'location' => $booking->business_profile->location_address == null ? $booking->business_profile->location : $booking->business_profile->location_address,
            ],
            'user_details' => [
                'full_name' => $booking->full_name,
                'email' => $booking->email,
                'phone' => $booking->phone,
            ],
            'guest_details' => $booking->guests->map(function ($guest) {
                return [
                    'full_name' => $guest->full_name,
                    'age' => $guest->age,
                    'phone' => $guest->phone,
                ];
            }),
            'pricing_summary' => [
                'subtotal' => $subtotal,
                'commission_fee' => $commissionFee,
                'tax' => $tax,
                'grand_total' => $grandTotal,
            ],
            // 'payment_status' => $booking->payment_status,
        ];



        return $this->success($data, 'Event order summary retrieved successfully.');


    }

}
