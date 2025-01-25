<?php

namespace App\Http\Controllers\Api\Business\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\OtpMailNotification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Location\Facades\Location;

class BusinessAuthController extends Controller
{

    // for json response

    use ApiResponse;


    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cover' => 'nullable|image|mimes:jpg,jpeg,png',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|string|max:255',
            'country' => 'required|string|max:255',

            'user_name' => 'required|unique:users,user_name|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }


        $countryCode = $this->getCountryCode($request->country);


        // if (!$countryCode) {
        //     return $this->error([], 'Invalid country name.', 422);
        // }

        $coverPath = '';
        // Handle cover image
        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
        }

        // Create User with country code
        $data = User::create([
            'full_name' => $request->full_name,
            'date_of_birth' => $request->date_of_birth,
            'country' => $request->country,
            'country_code' => $countryCode, // Add country code field
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'business',
            'avatar' => $coverPath ? $coverPath : '',
        ]);

        // Cover with URL
        $data->avatar = $data->avatar ? url($data->avatar) : null;

        // Generate token
        $token = auth('api')->login($data);
        $data['token'] = $token;

        return $this->success($data, 'Sign Up Successful.', 201);
    }

    // Method to get country code
    public function getCountryCode($countryName)
    {
        // Make API request to fetch country details
        $response = Http::get('https://restcountries.com/v3.1/name/' . urlencode($countryName));

        if ($response->successful()) {
            $countryData = $response->json();
            return $countryData[0]['cca2']; // 2-letter country code
        }

        return null; // Return null if not found
    }


    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation Error', $validator->errors()->first(), 422);
            }

            $credentials = $request->only('email', 'password');

            if (!$token = auth('api')->attempt($credentials)) {
                return $this->error('Unauthorized', 'Invalid email or password.', 401);
            }

            $user = auth('api')->user();
            $response = [
                'full_name' => $user->full_name,
                'user_name' => $user->user_name,
                'email' => $user->email,
                'token' => $token,
                'is_business' => $user->businessProfile ? true : false,

            ];

            return $this->success($response, 'Login successful.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error('Server Error', 'An error occurred during login.', 500);
        }
    }

    // __user profile
    public function profile()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $user = [
            'avatar' =>  $user->avatar ?  url($user->avatar) : '',
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,

        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }

    // user profile edit
    // public function edit()
    // {
    //     $user = auth('api')->user();

    //     if (!$user) {
    //         return $this->error([], 'User not found.', 404);
    //     }

    //     $user = [
    //         'avatar' => $user->avatar ? url($user->avatar) : '',
    //         'full_name' => $user->full_name,
    //         'email' => $user->email,
    //         'phone' => $user->phone,
    //         'gender' => $user->gender,
    //         'date_of_birth' => $user->date_of_birth,

    //     ];

    //     return $this->success($user, 'Profile retrieved successfully.');
    // }

    //  __update user profile
    // public function update_profile(Request $request)
    // {

    //     // dd($request->all());
    //     $validator = Validator::make($request->all(), [
    //         'full_name' => 'required|string|max:255',
    //         // 'email' => 'required|email|unique:users,email|max:255',
    //         'date_of_birth' => 'required|string|max:255',
    //         'gender' => 'required|string|max:255',
    //         'phone' => 'required|string|max:255',

    //     ]);

    //     if ($validator->fails()) {
    //         return $this->error([], $validator->errors()->first(), 422);
    //     }

    //     $coverPath = '';


    //     if ($request->hasFile('avatar')) {
    //         $coverPath = Helper::uploadImage($request->file('avatar'), 'business_profiles');
    //     }

    //     $user = auth('api')->user();
    //     $user->full_name = $request->full_name;
    //     // $user->email = $request->email;
    //     $user->date_of_birth = $request->date_of_birth;
    //     $user->gender = $request->gender;
    //     $user->phone = $request->phone;
    //     $user->country_code = $request->country_code;
    //     $user->avatar = $coverPath ? $coverPath : '';
    //     $user->save();

    //     $user = [
    //         'full_name' => $user->full_name,
    //         // 'email' => $user->email,
    //         'phone' => $user->phone,
    //         'country_code' => $user->country_code,

    //         'gender' => $user->gender,
    //         'date_of_birth' => $user->date_of_birth,
    //         'avatar' => $user->avatar ?  url($user->avatar) : '',

    //     ];

    //     return $this->success($user, 'Profile updated successfully.');
    // }

    // send otp to email
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors()->first(), 422);
        }

        // Generate a 4-digit OTP
        $otp = rand(1000, 9999);
        $email = $request->email;

        Cache::put('otp_' . $email, $otp, now()->addMinutes(10));

        // Send OTP via email (use your mail logic)
        Mail::to($email)->send(new OtpMailNotification($otp, $email));

        return $this->success(null, 'OTP sent successfully to your email.');
    }

    // verify otp
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;
        $otp = $request->otp;

        // retrieve OTP from cache
        $cachedOtp = Cache::get('otp_' . $email);

        if (!$cachedOtp || $cachedOtp != $otp) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 401);
        }

        // OTP is valid, clear it from cache
        Cache::forget('otp_' . $email);

        $resetToken = Str::random(64);

        // Store reset token in cache (optional)
        Cache::put('reset_token_' . $email, $resetToken, now()->addMinutes(15));

        return response()->json(['reset_token' => $resetToken, 'message' => 'OTP verified.'], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'reset_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $email = $request->email;
        $resetToken = $request->reset_token;

        // Retrieve reset token from cache
        $cachedResetToken = Cache::get('reset_token_' . $email);

        if (!$cachedResetToken || $cachedResetToken != $resetToken) {
            return response()->json(['message' => 'Invalid or expired reset token.'], 401);
        }

        // Reset password
        $user = User::where('email', $email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        // Clear the reset token from cache
        Cache::forget('reset_token_' . $email);

        return response()->json(['message' => 'Password reset successfully.'], 200);
    }

    public function logout()
    {
        try {

            if (!auth('api')->check()) {
                return $this->error([], 'User not found.', 404);
            }

            auth('api')->logout();

            return $this->success('Successfully loged out.', 200);
        } catch (Exception $e) {

            Log::error($e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }


    // get notification
    public function notifications()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $notifications = $user->notifications->filter(function ($notification) {
            return $notification->created_at->isToday();
        });

        // return only message and created_at
        $data = $notifications->map(function ($notification) {
            return [
                'message' => $notification->data['message'],
                'time' => $notification->created_at->diffForHumans(),
            ];
        });

        return $this->success($data, 'Today\'s notifications retrieved successfully.');
    }


   
    // previous day notifications
    public function previousDayNotifications()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $notifications = $user->notifications->filter(function ($notification) {
            return !$notification->created_at->isToday();
        });

        // return only message and created_at
        $data = $notifications->map(function ($notification) {
            return [
                'message' => $notification->data['message'],
                'time' => $notification->created_at->diffForHumans(),
            ];
        });

        return $this->success($data, 'Yesterday\'s notifications retrieved successfully.');
    }
}
