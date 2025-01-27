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
                return $this->error($available, 'Insufficient balance for withdrawal.', 400);
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
            return $this->success([
                'user_name' => $user->name == null ? $user->full_name : $user->user_name,
                'user_cover' => $user->avatar ? url($user->avatar) : null,
                'is_connect_stripe' => $user->stripe_account_id ? true : false,
                'available_balance' => 0,
                'pending_balance' => 0,
            ], 'User Balance retrieved successfully.');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $balance = \Stripe\Balance::retrieve(['stripe_account' => $user->stripe_account_id]);

            $available = $balance->available[0]->amount / 100;
            $pending = $balance->pending[0]->amount / 100;

            return $this->success([
                'user_name' => $user->name == null ? $user->full_name : $user->user_name,
                'user_cover' => $user->avatar ? url($user->avatar) : null,
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

            // Retrieve PaymentIntents (successful payments)
            $paymentIntents = \Stripe\PaymentIntent::all([
                'limit' => 10,
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            // Retrieve Payouts (withdrawals)
            $payouts = \Stripe\Payout::all([
                'limit' => 10,
            ], [
                'stripe_account' => $user->stripe_account_id,
            ]);

            // Combine PaymentIntent and Payout data
            $transactions = collect($paymentIntents->data)->map(function ($payment) {
                return [
                    'transaction_id' => $payment->id,
                    'type' => 'payment',
                    'amount' => $payment->amount ? $payment->amount / 100 : 0,
                    'currency' => $payment->currency ? $payment->currency : 'usd',
                    'status' => $payment->status ? $payment->status : 'succeeded',
                    'created_at' => $payment->created ? $payment->created : now()->timestamp,
                ];
            });

            $transactions = $transactions->merge(collect($payouts->data)->map(function ($payout) {
                return [
                    'transaction_id' => $payout->id,
                    'type' => 'payout',
                    'amount' => $payout->amount ? $payout->amount / 100 : 0,
                    'currency' => $payout->currency ? $payout->currency : 'usd',
                    'status' => $payout->status ? $payout->status : 'pending',
                    'created_at' => $payout->created ? $payout->created : now()->timestamp,
                ];
            }));

            $transactions = $transactions->sortByDesc('created_at');

            // Dummy transactions
            $dummy_transactions = [
                [
                    'transaction_id' => 'txn_1J4J7vLzZQJ9jv1J4J7vLzZQJ9jv',
                    'type' => 'payment',
                    'amount' => 0,
                    'currency' => 'usd',
                    'status' =>  'succeeded',
                    'created_at' => '2021-09-01 12:00:00',
                ],
                [
                    'transaction_id' => 'txn_1J4J7vLzZQJ9jv1J4J7vLzZQJ9jv',
                    'type' => 'payout',
                    'amount' => 0,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                    'created_at' => '2021-09-01 12:00:00',
                ],
            ];

            // Use dummy data if no transactions are found
            $transactions = $transactions->isEmpty() ? collect($dummy_transactions) : $transactions;

            return $this->success($transactions, 'All transaction history retrieved successfully.');
        } catch (ApiErrorException $e) {
            return $this->error([], 'Stripe error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->error([], 'Error: ' . $e->getMessage(), 500);
        }
    }
}
