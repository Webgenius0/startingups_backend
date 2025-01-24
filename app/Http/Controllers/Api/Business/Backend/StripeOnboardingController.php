<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Stripe\Stripe;
use Stripe\Account;
use App\Models\User;
use Stripe\AccountLink;
use Stripe\StripeClient;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;


class StripeOnboardingController extends Controller
{
    use ApiResponse;

    
    protected $stripeClient;

    public function __construct()
    {
        $this->stripeClient = new StripeClient(config('services.stripe.secret'));
    }



    // onboarding
    public function onboard($id)
    {
        $user = User::findOrFail($id); 

        if (!$user->stripe_account_id) {
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

                $onBoardLink = AccountLink::create([
                    'account' => $user->stripe_account_id,
                    'refresh_url' => route('business.event_reports'),
                    'return_url' => route('stripe.onboard-result', Crypt::encrypt($user->stripe_account_id)),
                    'type' => 'account_onboarding',
                ]);

                return $this->success(['url' => $onBoardLink->url], 'Onboarding link generated successfully.');

            } catch (\Exception $e) {

                return $this->error([], 'Stripe Onboarding Error: ' . $e->getMessage(), 500);

            }
        }

        try {

            $loginLink = $this->stripeClient->accounts->createLoginLink($user->stripe_account_id, []);
            return $this->success(['url' => $loginLink->url], 'Login link generated successfully.');

        } catch (\Exception $e) {

            return $this->error([], 'Error generating login link: ' . $e->getMessage(), 500);

        }
    }


    public function onboardResult($encodedToken)
    {
        $user = User::whereStripeConnectId(Crypt::decrypt($encodedToken))->firstOrFail();

        $user->stripe_boarding_completed = 'completed';
        $user->save();

        return redirect(route('dashboard'));
    }
}
