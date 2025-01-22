<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSearchHistory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSearchController extends Controller
{
    use ApiResponse;

    public function getSearchHistory(Request $request)
    {
        $user = $request->user();

        $histories = UserSearchHistory::where('user_id', $user->id)->latest()->take(15)->get();

        $histories = $histories->map(function ($history) {
            return [
                'id' => $history->id,
                'search' => $history->query,
            ];
        });

        return $this->success($histories, 'Search history retrieved successfully', 200);

    }

    public function searchUsers(Request $request)
    {
        $query = $request->input('query');

        $results = User::where('full_name', 'like', '%' . $query . '%')
            ->orWhere('email', 'like', '%' . $query . '%')
            ->orWhere('user_name', 'like', '%' . $query . '%')
            ->get();

        $request->user()->user_search()->create(['query' => $query]);

        $results = $results->map(function ($user) use ($query) {
          
            return [
                'id' => $user->id,
                'name' => $user->full_name == null ? $user->user_name : $user->full_name,
                'location' => $user->location == null ? $user->city : $user->location,
                'cover' => $user->avatar ? url($user->avatar) : null,

            ];

        });

        return $this->success($results, 'Search results retrieved successfully', 200);
    }

    public function getSuggestions(Request $request)
    {
        $user = Auth::user();

        $suggestion_users = User::where('location','like', '%'. $user->location . '%')
                                ->orWhere('city', 'like', '%' . $user->location . '%')
                                ->where('id', '!=', $user->id)
                                ->take(10)
                                ->get();

        $histories = $suggestion_users->map(function ($user)  {
            return [
                'id' => $user->id,
                'name' => $user->full_name == null ? $user->user_name : $user->full_name,
                'location' => $user->location == null ? $user->city : $user->location,
                'cover' => $user->avatar ? url($user->avatar) : null,
            ];
        });

        return $this->success($histories, 'You may know this user', 200);

    }

    // delete single search history
    public function deleteSearchHistory(Request $request, $history_id)
    {
        $user = $request->user();
        $history = UserSearchHistory::where('id', $history_id)->where('user_id', $user->id)->first();

        if (!$history) {
            return $this->error('Search history not found', 404);
        }

        $history->delete();

        return $this->success([], 'Search history deleted successfully', 200);
    }

    // delete user all history
    public function deleteAllSearchHistory(Request $request)
    {
        $user = $request->user();

        UserSearchHistory::where('user_id', $user->id)->delete();

        return $this->success([], 'All search history deleted successfully', 200);
    }
}
