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
    public function onboard()
    {
        $user = auth('api')->user();

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            if (empty($user->stripe_account_id)) {
                $account = Account::create([
                    'type' => 'express',
                    'email' => $user->email,
                    'country' => 'US',
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

                $user->stripe_account_id = $account->id;
                $user->save();
            }

            // Create an onboarding link
            $onBoardLink = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('business.event_reports'),
                'return_url' => $this->generateReturnUrl($user->stripe_account_id),
                'type' => 'account_onboarding',
            ]);

            return $this->success(['url' => $onBoardLink->url], 'Onboarding link generated successfully.');
        } catch (\Exception $e) {
            return $this->error([], 'Stripe Onboarding Error: ' . $e->getMessage(), 500);
        }
    }

    // Generate return URL with deep link for Flutter
    protected function generateReturnUrl($stripeAccountId)
    {
        // Generate a custom deep link URL for Flutter app
        $encodedAccountId = Crypt::encrypt($stripeAccountId);
        return "yourapp://stripe-onboarding?status=completed&account_id={$encodedAccountId}";
    }

    public function onboardResult($encodedToken)
    {
        try {
            $user = User::where('stripe_account_id', Crypt::decrypt($encodedToken))->firstOrFail();

            $user->stripe_boarding_completed = 'completed';
            $user->save();

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            Log::error('Error processing Stripe Onboarding result', [
                'error_message' => $e->getMessage(),
                'encoded_token' => $encodedToken,
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return $this->error([], 'Error processing onboarding result: ' . $e->getMessage(), 500);
        }
    }
}
