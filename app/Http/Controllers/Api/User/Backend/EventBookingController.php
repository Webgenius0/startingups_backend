<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Models\Event;
// use Barryvdh\DomPDF\PDF;
use App\Traits\ApiResponse;
use App\Models\EventBooking;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\EventBookingQuest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use App\Notifications\NewEventNotification;
use Illuminate\Support\Facades\Notification;
use App\Notifications\EventBookingNotification;
// use Barryvdh\DomPDF\Facade as PDF;

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

        // event booking notification
        $user = Auth::user();
        Notification::send($user, new EventBookingNotification($booking));


        // event load with guests
        $booking = $booking->load('guests');;

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


    // __event ticket

    public function event_ticket($id)
    {
        $booking = EventBooking::with('guests', 'business_profile')->find($id);

        // if event booking not found
        if (!$booking) {
            return $this->error([], 'Event booking not found.', 404);
        }

        $data = [

            'download_link' => route('user.event_ticket_download', Crypt::encrypt($booking->id)),

            'user_id' => $booking->user_id,
            'user_name' => $booking->full_name ? $booking->full_name : '',
            'user_cover' => $booking->user->avatar ? url($booking->user->avatar) : null,

            'booking_id' => $booking->id,

            'event_id' => $booking->business_profile->id,
            'event_cover' => $booking->business_profile->cover ? url($booking->business_profile->cover) : null,
            'price' => $booking->price ? $booking->price : 0,
            'person_count' => $booking->guest_count ? $booking->guest_count : 0,
            'event_name' => $booking->business_profile->title == null ? $booking->business_profile->business_name : $booking->business_profile->title,
            'date' => $booking->event_date ? $booking->event_date : '',
            'in_time' => $booking->event_time ? $booking->event_time : '',
            'location' => $booking->business_profile->location_address == null ? $booking->business_profile->location : $booking->business_profile->location_address,


            'guests' => $booking->guests->map(function ($guest) {
                return $guest->full_name ? $guest->full_name : '';
            }),


        ];

        return $this->success($data, 'Event ticket retrieved successfully.');
    }

    // __event ticket download
    public function download_ticket($id)
    {

        try {
            $bookingId = Crypt::decrypt($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return $this->error([], 'Invalid booking ID.', 400);
        }

        $booking = EventBooking::with('guests', 'business_profile')->find($bookingId);

        // if event booking not found
        if (!$booking) {
            return $this->error([], 'Event booking not found.', 404);
        }
        $data = [

            'user_id' => $booking->user_id,
            'user_name' => $booking->full_name,
            'user_cover' => $booking->user->avatar ? url($booking->user->avatar) : null,
            'booking_id' => $booking->id,
            'event_id' => $booking->business_profile->id,
            'event_cover' => "https://media.istockphoto.com/id/1500283713/vector/cinema-ticket-on-white-background-movie-ticket-on-white-background.jpg?s=612x612&w=0&k=20&c=4J15lHFXyjEs6xBoagcZqq5GYHKk5sMwCJRP8pNM3Zg=",
            'price' => $booking->price,
            'person_count' => $booking->guest_count,
            'event_name' => $booking->business_profile->title == null ? $booking->business_profile->business_name : $booking->business_profile->title,
            'date' => $booking->event_date,
            'in_time' => $booking->event_time,
            'location' => $booking->business_profile->location_address == null ? $booking->business_profile->location : $booking->business_profile->location_address,

            // check if guest is not null then map the guest if null then return empty array
            'guests' => $booking->guests ? $booking->guests->map(function ($guest) {
                return $guest->full_name;
            }) : null,



        ];

        // dd($data);

        // // Load the view and generate the PDF
        // $pdf = app('dompdf.wrapper')->loadView('frontend.user.ticket', compact('data'));
        // // $pdf = Pdf::loadView('frontend.user.ticket', compact('data'))->setPaper('a4', 'portrait');
        // return $pdf->download('ticket.pdf');
        return view('frontend.user.ticket', compact('data'));
    }
}
