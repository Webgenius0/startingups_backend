<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Models\Faq;
use App\Models\User;
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
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
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
}
