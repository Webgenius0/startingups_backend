<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\StripeClient;
use App\Models\User;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
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
    public function onboard($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            if (empty($user->stripe_account_id)) {
                // Create a new Stripe Account for the user
                $account = Account::create([
                    'type' => 'express',
                    'email' => $user->email,
                    'country' => 'US', // Ensure this is a supported country
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers' => ['requested' => true],
                    ],
                    'settings' => [
                        'payouts' => [
                            'schedule' => [
                                'interval' => 'manual',
                            ],
                        ],
                    ],
                ]);

                // Save the account ID to the user's record
                $user->stripe_account_id = $account->id;
                $user->save();
            }

            // Create Onboarding Link
            $onBoardLink = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('business.event_reports'),
                'return_url' => route('stripe.onboard-result', Crypt::encrypt($user->stripe_account_id)),
                'type' => 'account_onboarding',
            ]);

            return $this->success(['url' => $onBoardLink->url], 'Onboarding link generated successfully.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Log Stripe API errors for easier debugging
            Log::error('Stripe Onboarding Error', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'user_id' => $user->id,
                'stripe_account_id' => $user->stripe_account_id,
            ]);

            // Return a user-friendly error message
            return $this->error([], 'Stripe Onboarding Error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            // Log generic errors
            Log::error('Unexpected Error in Stripe Onboarding', [
                'error_message' => $e->getMessage(),
                'user_id' => $user->id,
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Return a user-friendly error message
            return $this->error([], 'Unexpected Error: ' . $e->getMessage(), 500);
        }
    }

    // Handle Stripe Onboarding Result
    public function onboardResult($encodedToken)
    {
        try {
            $user = User::where('stripe_account_id', Crypt::decrypt($encodedToken))->firstOrFail();

            // Mark onboarding as completed
            $user->stripe_boarding_completed = 'completed';
            $user->save();

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            // Log error during result processing
            Log::error('Error processing Stripe Onboarding result', [
                'error_message' => $e->getMessage(),
                'encoded_token' => $encodedToken,
                'stack_trace' => $e->getTraceAsString(),
            ]);

            // Return a user-friendly error message
            return $this->error([], 'Error processing onboarding result: ' . $e->getMessage(), 500);
        }
    }
}
