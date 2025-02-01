<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Models\Faq;
use App\Models\User;
use App\Helper\Helper;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class UserAccountController extends Controller
{

    use ApiResponse;

    // __faq

    public function user_faq()
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
            'avatar' => $user->avatar ?  url($user->avatar) : '',
            'full_name' => $user->full_name,
            'email' => $user->email,
            'location' => $user->city ? $user->street_address : $user->city,
            'phone' => $user->phone ? $user->phone : '',


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
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,

        ];

        return $this->success($user, 'Profile retrieved successfully.');
    }

    public function update_profile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $user = auth('api')->user(); 

        
        if ($request->hasFile('avatar')) {
            $avatarPath = Helper::uploadImage($request->file('avatar'), 'user_profiles');
            $user->avatar = $avatarPath; 
        }

        
        if ($request->filled('full_name')) {
            $user->full_name = $request->full_name;
        }

        if ($request->filled('date_of_birth')) {
            $user->date_of_birth = $request->date_of_birth;
        }

        if ($request->filled('gender')) {
            $user->gender = $request->gender;
        }

        $user->save(); 

        
        $updatedUser = [
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'avatar' => $user->avatar ? url($user->avatar) : '',
        ];

        return $this->success($updatedUser, 'Profile updated successfully.');
    }
}
