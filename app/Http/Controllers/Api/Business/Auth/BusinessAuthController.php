<?php

namespace App\Http\Controllers\Api\Business\Auth;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Mail\OtpMailNotification;
use App\Models\User;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BusinessAuthController extends Controller
{

    // for json response

    use ApiResponse;

    public function register(Request $request)
    {

        // dd($request->all());
        $validator = Validator::make($request->all(), [
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

        // $validatedData = $validator->validated();

        if ($request->hasFile('cover')) {
            $coverPath = Helper::uploadImage($request->file('cover'), 'business_profiles');

        }
        $data = User::create([
            'full_name' => $request->full_name,
            'date_of_birth' => $request->date_of_birth,
            'country' => $request->country,
            'user_name' => $request->user_name,

            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'business',
            'avatar' => $coverPath
        ]);

        // cover with url
        $data->avatar = $data->avatar ? url($data->avatar) : null;


        // generate token
        $token = auth('api')->login($data);

        $data['token'] = $token;

        return $this->success($data, ' Sign Up Successfull.', 201);

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

        // if not found
        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }
        // return user profile
        $user = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            // 'user_name' => $user->user_name,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,

        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }

    // user profile edit
    public function edit()
    {
        $user = auth('api')->user();

        if (!$user) {
            return $this->error([], 'User not found.', 404);
        }

        $user = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,

        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }

    //  __update user profile
    public function update_profile(Request $request)
    {

        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'date_of_birth' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user();
        $user->full_name = $request->full_name;
        $user->email = $request->email;
        $user->date_of_birth = $request->date_of_birth;
        $user->gender = $request->gender;
        $user->phone = $request->phone;
        $user->save();

        $user = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,

        ];

        return $this->success($user, 'Profile updated successfully.');
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
