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
use Illuminate\Support\Facades\Validator;

class StripeOnboardingController extends Controller
{
    use ApiResponse;

    protected $stripeClient;

    public function __construct()
    {
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
    }

    public function onboard($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            if (empty($user->stripe_account_id)) {
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

                $user->stripe_account_id = $account->id;
                $user->save();
            }

            $onBoardLink = AccountLink::create([
                'account' => $user->stripe_account_id,
                'refresh_url' => route('business.event_reports'),
                'return_url' => route('stripe.onboard-result', Crypt::encrypt($user->stripe_account_id)),
                'type' => 'account_onboarding',
            ]);

            return $this->success(['url' => $onBoardLink->url], 'Onboarding link generated successfully.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->error([], 'Stripe Onboarding Error: ' . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return $this->error([], 'Unexpected Error: ' . $e->getMessage(), 500);
        }
    }

    public function onboardResult($encodedToken)
    {
        try {
            $user = User::where('stripe_account_id', Crypt::decrypt($encodedToken))->firstOrFail();

            $user->stripe_boarding_completed = 'completed';
            $user->save();

            return redirect(route('dashboard'));
        } catch (\Exception $e) {
            return $this->error([], 'Error processing onboarding result: ' . $e->getMessage(), 500);
        }
    }
}
