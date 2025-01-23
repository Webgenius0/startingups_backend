<?php

namespace App\Http\Controllers\Api\Business\Backend;

use Stripe\Stripe;
use App\Models\Faq;
use Stripe\Account;
use App\Models\User;
use App\Helper\Helper;
use Stripe\AccountLink;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;


class BusinessAccountController extends Controller
{

    use ApiResponse;


    // __faq

    public function business_faq()
    {
        try {

            $faqs = Faq::where('status', 'active')->get();

            if ($faqs->isEmpty()) {
                return $this->error([], 'No faqs found', 404);
            }

            return $this->success($faqs, 'FAQs retrieved successfully', 200);
        } catch (\Exception $e) {

            return $this->error([], 'Error retrieving FAQs: ' . $e->getMessage(), 500);
        }
    }

    public function account_profile()
    {
        $user = auth('api')->user();


        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $user = [
            'full_name' => $user->full_name,
            'location' => $user->city ? $user->street_address : $user->city,
            'email' => $user->email,
            'avatar' => $user->avatar ?  url($user->avatar) : '',

        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }


    public function edit()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $user = [
            'avatar' => $user->avatar ?  url($user->avatar) : '',
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,
        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }

    //  __update user profile
    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            // 'email' => 'required|email|max:255',
            'date_of_birth' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'phone' => 'required|string|max:255',

        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $coverPath = '';


        if ($request->hasFile('avatar')) {
            $coverPath = Helper::uploadImage($request->file('avatar'), 'user_profiles');
        }

        $user = auth('api')->user();
        $user->full_name = $request->full_name;
        $user->email = auth('api')->user()->email;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->phone = $request->phone;
        $user->country_code = $request->country_code;
        $user->avatar = $coverPath;
        $user->save();

        $user = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'avatar' => $user->avatar ?  url($user->avatar) : '',

        ];
        return $this->success($user, 'Profile updated successfully.');
    }


    // onboarding
    public function onboard($id)
    {
        $user = User::find($id);

        if (empty($user->stripe_boarding_completed)) {
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

            return $this->success($onBoardLink->url, 'Onboarding link generated successfully.');
        }

        $loginLink = $this->stripeClient->accounts->createLoginLink($user->stripe_account_id, []);
        
        return $this->success($loginLink->url, 'Login link generated successfully.');
    }

    public function onboardResult($encodedToken)
    {
        $user = User::whereStripeConnectId(Crypt::decrypt($encodedToken))->firstOrFail();

        $user->stripe_on_boarding_completed_at = now();
        $user->save();

        return redirect(route('dashboard'));
    }
}
