<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\StripeClient;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeOnboardingController extends Controller
{
    use ApiResponse;

    protected $stripeClient;

    public function __construct()
    {
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
    }

    // Onboarding User to Stripe
    public function onboard(Request $request)
    {

        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated.',
            ], 401);
        }

        if ($user->stripe_account_id) {
            try {

                Stripe::setApiKey(config('services.stripe.secret'));


                $loginLink = \Stripe\Account::createLoginLink($user->stripe_account_id);


                return $this->success(['url' => $loginLink->url], 'Redirecting to Stripe Express Dashboard..');
            } catch (\Exception $e) {
                Log::info($e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error generating Stripe login link: ' . $e->getMessage(),
                ], 500);
            }
        }


        try {

            Stripe::setApiKey(config('services.stripe.secret'));


            $account = Account::create([
                'type' => 'express',
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => [
                            'interval' => 'daily',
                        ],
                    ],
                ],
            ]);


            $link = AccountLink::create([
                'account' => $account->id,
                'refresh_url' => route('stripe.refresh', ['id' => $account->id]),
                'return_url' => route('stripe.success', ['id' => $account->id]),
                'type' => 'account_onboarding',
            ]);

            return $this->success(['url' => $link->url], 'Onboarding link generated successfully.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::info($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Stripe API error: ' . $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function stripeSuccess($id)
    {
        try {

            Stripe::setApiKey(config('services.stripe.secret'));


            $account = Account::retrieve($id);


            $user = User::where('email', $account->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found in the database for this Stripe account.',
                ], 404);
            }


            $user->update([
                'stripe_account_id' => $id,
                // 'is_stripe_onboarded' => true
            ]);


            return $this->redirectToStripeDashboard($user->stripe_account_id);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error processing onboarding success: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function redirectToStripeDashboard($stripeAccountId)
    {
        try {

            Stripe::setApiKey(config('services.stripe.secret'));


            $loginLink = \Stripe\Account::createLoginLink($stripeAccountId);


            return redirect()->away($loginLink->url);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating Stripe login link: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function stripeRefresh($id)
    {
        try {

            Stripe::setApiKey(config('services.stripe.secret'));
            $user = User::where('stripe_account_id', $id)->first();

            $link = AccountLink::create([
                'account' => $id,
                'refresh_url' => route('stripe.refresh', ['id' => $id]),
                'return_url' => route('stripe.success', ['id' => $id]),
                'type' => 'account_onboarding',
            ]);


            return redirect()->away($link->url);
        } catch (\Exception $e) {

            Log::info($e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Error generating refresh link: ' . $e->getMessage(),
            ], 500);
        }
    }
}
