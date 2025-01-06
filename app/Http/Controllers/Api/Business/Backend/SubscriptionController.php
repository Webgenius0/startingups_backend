<?php

namespace App\Http\Controllers\Api\Business\Backend;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Notifications\SubscriptionStatusNotification;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    use ApiResponse;

    // __subscription plans
    public function index()
    {

        try {
            $plans = Plan::all();


            if ($plans->isEmpty()) {
                return $this->error([], 'Subscription plan not found', 404);
            }

            return $this->success($plans, 'Subscription plan data retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error retrieving subscription plans', 500);
        }
    }

    // __create intent
    public function createIntent(Request $request)
    {
        try {
            $user = $request->user();
            $intent = $user->createSetupIntent();

            return $this->success(['intent' => $intent], 'Setup intent created successfully', 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error creating setup intent', 500);
        }
    }

    // __subscribe
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'plan_id' => 'required|string',
        ]);

        $user = $request->user();

        try {
            $user->newSubscription('default', $validated['plan_id'])
                ->create($validated['payment_method']);

            return $this->success([], 'Subscription successful!', 200);

            // __send created notification
            $user->notify(new SubscriptionStatusNotification('Your subscription has been successfully created.'));

        } catch (IncompletePayment $exception) {

            return $this->error([
                'status' => 'incomplete',
                'payment_url' => $exception->payment->next_action->use_stripe_sdk->stripe_js,
            ], 'Payment incomplete', 422);

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error creating subscription', 500);
        }
    }

    // __cancel subscription
    public function cancel(Request $request)
    {
        $user = $request->user();

        try {
            $subscription = $user->subscription('default');

            if (!$subscription || !$subscription->active()) {
                return $this->error([], 'No active subscription found', 404);
            }

            $subscription->cancel();


            // __send cancel notification
            $user->notify(new SubscriptionStatusNotification('Your subscription has been canceled.'));


            return $this->success([], 'Subscription canceled successfully', 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error canceling subscription', 500);
        }
    }

    // __subscription status
    public function status(Request $request)
    {
        $user = $request->user();

        try {
            $subscription = $user->subscription('default');

            if (!$subscription) {
                return $this->error([], 'No subscription found', 404);
            }

            return $this->success([
                'active' => $subscription->active(),
                'canceled' => $subscription->canceled(),
                'ends_at' => $subscription->ends_at,
            ], 'Subscription status retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error retrieving subscription status', 500);
        }
    }

    // __subscribe with trial
    public function trial(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => 'required|string',
            'plan_id' => 'required|string',
        ]);

        try {


            $request->user()->newSubscription('default', $validated['plan_id'])
                ->trialDays(10)
                ->create($validated['payment_method']);


            // __send trial notification
            $user = $request->user();
            $user->notify(new SubscriptionStatusNotification('Your trial period has started. Enjoy your 10-day free trial!'));


            return $this->success([], 'Subscription created with trial period!', 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error creating subscription with trial', 500);
        }
    }

    // __check trial status
    public function trial_status(Request $request)
    {
        $user = $request->user();

        try {
            $subscription = $user->subscription('default');

            if (!$subscription) {
                return $this->error([
                    'is_on_trial' => false,
                ], 'User does not have an active subscription', 404);
            }

            return $this->success([
                'is_on_trial' => $subscription->onTrial(),
                'trial_ends_at' => $subscription->onTrial() ? Carbon::parse($subscription->trial_ends_at)->format('Y-m-d H:i:s') : null,
            ], 'Trial status retrieved successfully', 200);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 'Error checking trial status', 500);
        }
    }
}
