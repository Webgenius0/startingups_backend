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
        $validator = Validator::make($request->all(), [
            'gender' => 'required|string|max:255',
            'preferences.*' => 'required|string|max:255',

            'full_name' => 'required|string|max:255',
            'date_of_birth' => 'required|string|max:255',
            // 'street_address' => 'required|string|max:255',
            // 'city' => 'required|string|max:255',

            'user_name' => 'required|unique:users,user_name|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');

        }

        // $validatedData = $validator->validated();

        $data = User::create([
            'avatar' => $coverPath,
            'full_name' => $request->full_name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),


            'role' => 'user',

            'gender' => $request->gender,
            'preferences' => json_encode($request->preferences),
            'date_of_birth' => $request->date_of_birth,
            'country' => $request->country,
            // 'street_address' => $request->street_address,
            // 'city' => $request->city,

        ]);

         // cover with url
         $data->avatar = $data->avatar ? url($data->avatar) : null;



        // generate token
        $token = auth('api')->login($data);
        $data['token'] = $token;

        // prefernces
        $data['preferences'] = json_decode($data['preferences']);

        return $this->success($data, ' Sign Up Successfull.', 201);

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

        $otp = rand(100000, 999999);
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












    // __get user preferences
    public function preferences()
    {
        $user = auth('api')->user();

        // if not found
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        // return user preferences
        $preferences = json_decode($user->preferences);

        return $this->success($preferences, 'Preferences retrieved successfully.');
    }


    // __update user preferences
    public function update_preferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'preferences.*' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();

        // if not found
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $user->preferences = json_encode($request->preferences);
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
