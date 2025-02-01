<?php

namespace App\Http\Controllers\Api\User\Auth;

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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{

    // for json response

    use ApiResponse;

    public function register(Request $request)
    {
        // Validation for user registration
        $validator = Validator::make($request->all(), [
            'cover' => 'nullable|image|mimes:jpg,jpeg,png',
            'gender' => 'required|string|max:255',
            // 'preferences' => 'required', // Ensure preferences is an array
            'preferences' => 'required',

            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|string|max:255',
            'user_name' => 'required|unique:users,user_name|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        // Handle image upload
        $coverPath = '';
        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
        }

        // Store user data
        $user = User::create([
            'avatar' => $coverPath ?: '',
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user',
            'gender' => $request->gender,
            'preferences' => $request->preferences, 
            'date_of_birth' => $request->date_of_birth,
            'country' => $request->country,
            'phone' => $request->phone,
        ]);

        // Generate token
        $token = auth('api')->login($user);
        $user->token = $token;

        // Cover with full URL
        $user->avatar = $user->avatar ? url($user->avatar) : null;

        return $this->success($user, 'Sign Up Successful.', 201);
    }


    // user_location update

    public function user_location(Request $request)
    {
        $user = auth('api')->user();
        $user->street_address = $request->street_address;
        $user->city = $request->city;
        $user->save();
        return $this->success($user, 'Location updated successfully.');
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
                'is_location' => $user->street_address ? true : false

            ];

            return $this->success($response, 'Login successful.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->error('Server Error', 'An error occurred during login.', 500);
        }
    }

    // user profile
    public function profile()
    {
        $user = auth('api')->user();

        // if not found
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        // return user profile
        $user = [
            'full_name' => $user->full_name,
            'user_name' => $user->user_name,
            'email' => $user->email,
            'country' => $user->country,
            'date_of_birth' => $user->date_of_birth,

        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }

    // send otp to email
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors()->first(), 422);
        }

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

        $cachedOtp = Cache::get('otp_' . $email);

        if (!$cachedOtp || $cachedOtp != $otp) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 401);
        }

        
        Cache::forget('otp_' . $email);

        $resetToken = Str::random(64);
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












    public function preferences()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        
        // if not found
    if (!$user) {
        return $this->error([], 'User not found.', 404);
    }

    // Split preferences string into an array (by commas) and trim extra spaces
    $preferences = explode(',', $user->preferences);

    // Trim any extra whitespace and remove any surrounding quotes
    $preferences = array_map(function($preference) {
        return trim($preference, '"');
    }, $preferences);

        return $this->success($preferences, 'Preferences retrieved successfully.');
    }




    public function update_preferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferences.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }


        $preferences = $request->preferences;
        if (is_string($preferences)) {
            $preferences = explode(',', $preferences);
        }


        $sanitizedPreferences = array_map(function ($preference) {
            return trim(strip_tags($preference), '{}');
        }, $preferences);


        $user->preferences = null;
        $user->save();


        $user->preferences = json_encode($sanitizedPreferences);
        $user->save();


        $data = json_decode($user->preferences);

        return $this->success($data, 'Preferences updated successfully.');
    }









    // logout
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
}
