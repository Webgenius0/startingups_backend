<?php

namespace App\Http\Controllers\Api\User\Backend;

use Stripe\Stripe;
use Stripe\Account;
use Stripe\Webhook;
use Stripe\PaymentIntent;
use App\Traits\ApiResponse;
use App\Models\EventBooking;

use Illuminate\Http\Request;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\SignatureVerificationException;

class UserPaymentController extends Controller
{
    use ApiResponse;

    /**
     * Create a Payment Intent
     */
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $validatedData = Validator::make($request->all(), [
            'event_booking_id' => 'required|integer|exists:event_bookings,id',
            'amount' => 'required|numeric',
        ]);

        if ($validatedData->fails()) {
            return $this->error([], $validatedData->errors()->first(), 422);
        }

        // Event booking and event owner
        $eventBooking = EventBooking::with('business_profile')->findOrFail($request->event_booking_id);
        $event_owner = $eventBooking->business_profile->user;

        if (!$event_owner->stripe_account_id) {
            return $this->error([], 'Event owner is not onboarded to Stripe.', 400);
        }

        $account = Account::retrieve($event_owner->stripe_account_id);

        if (!$account->capabilities->transfers || $account->capabilities->transfers !== 'active') {
            return $this->error([], 'The event owner\'s account is not enabled for transfers.', 400);
        }

        try {


            $platformFee = $request->amount * 0.20; // 20% Platform Fee
            $userChargeFee = $request->amount * 0.015; // 1.5% User Charge
            $totalAmount = $request->amount; // The full amount customer is paying
            $adminFee = $platformFee + $userChargeFee; // Total amount admin earns
            $businessOwnerAmount = $totalAmount - $adminFee; // Remaining for event owner

            $paymentIntent = PaymentIntent::create([
                'amount' => $totalAmount * 100, 
                'currency' => 'usd',
                'metadata' => [
                    'event_booking_id' => $request->event_booking_id,
                    'event_owner_id' => $event_owner->id,
                ],
                'transfer_data' => [
                    'destination' => $event_owner->stripe_account_id, 
                ],
                'application_fee_amount' => $adminFee * 100, 
            ]);

            // Store the transaction
            PaymentTransaction::create([
                'event_booking_id' => $request->event_booking_id,
                'transaction_id' => $paymentIntent->id,
                'amount' => $totalAmount, 
                // 'platform_fee' => $platformFee,
                // 'user_charge_fee' => $userChargeFee,
                // 'admin_fee' => $adminFee,
                // 'business_owner_amount' => $businessOwnerAmount,
                'status' => 'pending',
            ]);




            // $data = [
            //     'plat_form_fee' => $platformFee,
            //     'user_charge' => $userChargeFee,
            //     'client_secret' => $paymentIntent->client_secret
            // ];

            // return $this->success($data, 'Payment intent created successfully.');

            return $this->success(['client_secret' => $paymentIntent->client_secret], 'Payment intent created successfully.');
        } catch (ApiErrorException $e) {
            return $this->error([], 'Stripe error: ' . $e->getMessage(), 500);
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
