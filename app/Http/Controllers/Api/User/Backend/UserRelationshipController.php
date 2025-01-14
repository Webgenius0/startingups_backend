<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class UserRelationshipController extends Controller
{

    use ApiResponse;
    // Follow a user
    public function follow(Request $request, $followeeId)
    {
        $follower = auth()->user();

        if ($follower->id === (int) $followeeId) {
            return response()->json(['message' => 'You cannot follow yourself'], 400);
        }

        $follower->follow($followeeId);


       return $this->success([], 'User followed successfully', 200);

    }

    // Unfollow a user
    public function unfollow(Request $request, $followeeId)
    {
        $follower = auth()->user();

        $follower->unfollow($followeeId);

        return $this->success([], 'User unfollowed successfully', 200);
    }

    // Retrieve followers of a given user
    public function followers($userId)
    {
        $user = User::findOrFail($userId);
        $followers = $user->followers()->with('follower')->get();

        return response()->json($followers);
    }

    // Retrieve followees of a given user
    public function followees($userId)
    {
        $user = User::findOrFail($userId);
        $followees = $user->followees()->with('followee')->get();

        return response()->json($followees);
    }
}
