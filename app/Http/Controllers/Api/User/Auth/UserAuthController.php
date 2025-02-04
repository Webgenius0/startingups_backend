<?php

namespace App\Http\Controllers\Api\User\Auth;

use Exception;
use App\Models\User;
use App\Helper\Helper;
use App\Models\Category;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\UserPreference;
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

        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'cover' => 'nullable|image|mimes:jpg,jpeg,png',
            'gender' => 'required|string|max:255',
            'preferences_id' => 'required|array',
            'preferences_id.*' => 'integer',
            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|string|max:255',
            'user_name' => 'required|unique:users,user_name|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => ['required', 'confirmed', 'min:8'],

        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $coverPath = '';
        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');
        }

        $user = User::create([
            'avatar' => $coverPath ?: '',
            'name' => $request->full_name,
            'full_name' => $request->full_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user',
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'country' => $request->country,
            'phone' => $request->phone,
        ]);

        foreach ($request->preferences_id as $preference) {
            $user->user_preferences()->create([
                'user_id' => $user->id,
                'category_id' => (int)$preference
            ]);
        }

        $token = auth('api')->login($user);
        $user->token = $token;

        $preferences = Category::whereIn('id', $request->preferences_id)->pluck('name');
        $user['preferences'] = $preferences;

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
        $user_id = auth('api')->user()->id;
        $user = User::with('user_preferences')->find($user_id);

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }


        $user_preferences = $user->user_preferences->pluck('category_id');


        $preferences = Category::whereIn('id', $user_preferences)->pluck('name');

        return $this->success($preferences, 'Preferences retrieved successfully.');
    }





    public function update_preferences(Request $request)
    {
        $user = auth('api')->user();

        // Validate input
        $validator = Validator::make($request->all(), [
            'preferences_id' => 'required|array',
            'preferences_id.*' => 'integer|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        UserPreference::where('user_id', $user->id)->delete();
        foreach ($request->preferences_id as $preference) {
            $user->user_preferences()->create([
                'user_id' => $user->id,
                'category_id' => (int) $preference
            ]);
        }

        
        $updatedPreferences = Category::whereIn('id', $request->preferences_id)->pluck('name');

        return $this->success($updatedPreferences, 'Preferences updated successfully.');
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
