<?php

namespace App\Http\Controllers\Api\User\Backend;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\EventBooking;

use App\Models\PaymentTransaction;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class UserPaymentController extends Controller
{
    use ApiResponse;

    /**
     * Create a Payment Intent
     */
    public function createPaymentIntent(Request $request)
    {
        // Validate the incoming request
        $validatedData = Validator::make($request->all(), [
            'event_booking_id' => 'required|integer|exists:event_bookings,id',
            'amount' => 'required|numeric',
        ]);

        if ($validatedData->fails()) {
            return $this->error([], $validatedData->errors()->first(), 422);
        }

        $event_booking = EventBooking::with('payments')
            ->whereHas('payments', function ($query) {
                $query->where('status', 'success');
            })
            ->find($request->event_booking_id);

        if ($event_booking && $event_booking->payments->count() > 0) {
            return $this->error([], 'Booking Payment Already Paid.', 200, []);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount * 100, // Amount in cents
                'currency' => 'usd',
                'metadata' => [
                    'event_booking_id' => $request->event_booking_id,
                ],
            ]);

            DB::table('payment_transactions')->insert([
                'event_booking_id' => $request->event_booking_id,
                'amount' => $request->amount,
                'transaction_id' => $paymentIntent->id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

         

            return $this->success($paymentIntent->client_secret, 'Payment intent send succesfully.');

        } catch (ApiErrorException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    

    /**
     * Handle Stripe Webhook Events (Optional)
     */
    public function webhookHandler(Request $request)
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');

            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $this->handlePaymentSuccess($event->data->object);
                    return response()->json(['message' => 'Payment succeeded']);

                case 'payment_intent.payment_failed':
                    $this->handlePaymentFailure($event->data->object);
                    return response()->json(['message' => 'Payment failed']);

                default:
                    return response()->json(['message' => 'Unhandled event type']);
            }
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Webhook signature verification failed'], 400);
        } catch (ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle Payment Success (webhook event)
     */
    protected function handlePaymentSuccess($paymentIntent)
    {
        // Record successful payment in the database
        $payment = PaymentTransaction::create([
            'event_booking_id' => $paymentIntent->metadata->event_booking_id,
            'transaction_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100,
            'status' => 'success',
        ]);

       
        if ($payment) {
            $payment->update(['status' => 'succeeded']);
        }
    }

    /**
     * Handle Payment Failure (webhook event)
     */
    protected function handlePaymentFailure($paymentIntent)
    {
        // Record failed payment in the database
        $payment = PaymentTransaction::create([
            'event_booking_id' => $paymentIntent->metadata->event_booking_id,
            'trx_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount / 100,
            'status' => 'failed',
        ]);

        
        if ($payment) {
            $payment->update(['status' => 'failed']);
        }
    }
}
