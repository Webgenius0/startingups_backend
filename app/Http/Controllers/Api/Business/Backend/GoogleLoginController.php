<?php

namespace App\Http\Controllers\Api\Business\Backend;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite;


class GoogleLoginController extends Controller
{

    use ApiResponse;



    public function googlesignin(Request $request)
    {
        try {

            $token = $request->input('token');

            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'user_name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt($googleUser->getId()),
                    'role' => 'business',

                ]
            );

            Auth::login($user);
            $token = auth('api')->login($user);

            $userData = [
                'id' => $user['id'],
                'google_id' => $user['google_id'] ? true : false,
                'name' => $user['name'],
                'user_name' => $user['user_name'],
                'email' => $user['email'],
                'avatar' => $user['avatar'],
                'role' => 'business',
                'token' => $token,
            ];

            return $this->success($userData, 'Successfully Logged In', 200);
        } catch (\Exception $e) {

            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }











    public function login()
    {
        return view('google');
    }
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }


    // public function handleGoogleCallback()
    // {
    //     $googleUser = Socialite::driver('google')->stateless()->user();
    //     // dd($googleUser);
    //     $user = User::where('email', $googleUser->email)->first();
    //     if (!$user) {
    //         $user = User::create(['name' => $googleUser->name, 'email' => $googleUser->email, 'password' => \Hash::make(rand(100000, 999999))]);
    //     }

    //     Auth::login($user);

    //     return redirect(RouteServiceProvider::HOME);
    // }
}
