<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Stripe\Stripe;
use Stripe\Payout;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Stripe\Exception\ApiErrorException;

class BusinessPayoutController extends Controller
{
    use ApiResponse;

    public function withdraw(Request $request)
    {
        $user = auth('api')->user();

        if (!$user->stripe_account_id) {
            return $this->error([], 'Stripe account not found. Please complete onboarding.', 400);
        }

        // Validate the request
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));


            $balance = \Stripe\Balance::retrieve(['stripe_account' => $user->stripe_account_id]);

            $available = $balance->available[0]->amount / 100;

            if ($request->amount > $available) {
                return $this->error([], 'Insufficient balance for withdrawal.', 400);
            }

            // Create the payout
            $payout = Payout::create(
                [
                    'amount' => $request->amount * 100,
                    'currency' => 'usd',
                ],
                ['stripe_account' => $user->stripe_account_id]
            );

            return $this->success($payout, 'Payout initiated successfully.');
        } catch (ApiErrorException $e) {

            return $this->error([], 'Stripe error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {

            return $this->error([], 'Error: ' . $e->getMessage(), 500);
        }
    }



    // balance
    public function getBalance()
    {
        $user = auth('api')->user();

        if (!$user->stripe_account_id) {
            return $this->error([], 'Stripe account not found. Please complete onboarding.', 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $balance = \Stripe\Balance::retrieve(['stripe_account' => $user->stripe_account_id]);

            $available = $balance->available[0]->amount / 100;
            $pending = $balance->pending[0]->amount / 100;

            return $this->success([
                'user_name' => $user->name == null ? $user->full_name : $user->user_name,
                'user_cover' => url($user->avatar),
                'is_connect_stripe' => $user->stripe_account_id ? true : false,
                'available_balance' => $available,
                'pending_balance' => $pending,
            ], 'User Balance retrieved successfully.');
        } catch (\Exception $e) {

            return $this->error([], 'Error: ' . $e->getMessage(), 500);
        }
    }



    public function getAllTransactionHistory(Request $request)
    {
        $user = auth('api')->user();

        if (!$user->stripe_account_id) {
            return $this->error([], 'Stripe account not found. Please complete onboarding.', 400);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // retrieve PaymentIntents (successful payments)
            $paymentIntents = \Stripe\PaymentIntent::all([
                'limit' => 10,  
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            // dd($paymentIntents);

            // retrieve Payouts (withdrawals)
            $payouts = \Stripe\Payout::all([
                'limit' => 10,  
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            // combine PaymentIntent and Payout data
            $transactions = collect($paymentIntents->data)->map(function ($payment) {
                return [
                    'transaction_id' => $payment->id,
                    'type' => 'payment',
                    'amount' => $payment->amount / 100, 
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'created_at' => $payment->created,
                ];
            });

            dd($transactions);

            $transactions = $transactions->merge(collect($payouts->data)->map(function ($payout) {
                return [
                    'transaction_id' => $payout->id,
                    'type' => 'payout',
                    'amount' => $payout->amount / 100, 
                    'currency' => $payout->currency,
                    'status' => $payout->status,
                    'created_at' => $payout->created,
                ];
            }));

         
            $transactions = $transactions->sortByDesc('created_at');

            return $this->success($transactions, 'All transaction history retrieved successfully.');

        } catch (ApiErrorException $e) {

            return $this->error([], 'Stripe error: ' . $e->getMessage(), 500);

        } catch (\Exception $e) {

            return $this->error([], 'Error: ' . $e->getMessage(), 500);
            
        }
    }
}
