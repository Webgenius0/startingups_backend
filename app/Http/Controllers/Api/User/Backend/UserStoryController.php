<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Models\Story;
use App\Models\StoryLike;
use App\Models\StoryReview;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserStoryController extends Controller
{

    use ApiResponse;

    // __store user story
    public function store(Request $request)
    {

        // dd($request->all());
        // Step 1: Validate the incoming request data
        $validatedData = Validator::make($request->all(), [
            'cover' => 'required|image|mimes:jpg,jpeg,png|max:4096',
            'title' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',

        ]);



        if ($validatedData->fails()) {
            return $this->error([], $validatedData->errors()->first(), 422);
        }

        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover') ? Helper::uploadImage($request->file('cover'), 'stories') : null;

        }

        $story = Story::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'cover' => $coverPath ?? null,

        ]);

        $story->cover = $story->cover ? url($story->cover) : null;

        return $this->success($story, 'Story created successfully', 200);

    }

    //  __show user story
    public function show(string $story_id)
    {

        // validate the incoming request data
        $validatedData = Validator::make(['story_id' => $story_id], [
            'story_id' => 'required|integer|exists:stories,id',
        ]);

        if ($validatedData->fails()) {
            return $this->error([], $validatedData->errors()->first(), 422);
        }

        $story = Story::with('user', 'reviews', 'likes')->find($story_id);

        if (!$story) {
            return $this->error(null, 'Story not found', 404);
        }

        $story = [
            'id' => $story->id,
            'title' => $story->title,
            'description' => $story->description,
            'location' => $story->location,
            'cover' => url($story->cover),
            'likes_count' => $story->likes_count,
            // count review
            'reviews_count' => $story->reviews->count(),

            'user' => [
                'id' => $story->user->id,
                'full_name' => $story->user->full_name,
                'email' => $story->user->email,
                'phone' => $story->user->phone,
                'avatar' => $story->user->avatar ?  url($story->user->avatar) : null,

            ],
            'reviews' => $story->reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'review' => $review->review,
                    'user_name' => $review->user->full_name,

                ];
            }),


        ];

        return $this->success($story, 'Story details retrived successfully ', 200);

    }

    // __story like
    public function story_like($id)
    {
        $story = Story::findOrFail($id);

        $existingLike = StoryLike::where('user_id', Auth::id())->where('story_id', $story->id)->first();
        // dd($existingLike);

        if ($existingLike) {
            // If already liked, remove the like
            $existingLike->delete();
            $story->decrement('likes_count');

            return $this->success([], 'Story like removed successfully ', 200);

        }

        // Otherwise, add a new like
        StoryLike::create([
            'user_id' => Auth::id(),
            'story_id' => $story->id,
        ]);
        $story->increment('likes_count');

        return $this->success([], 'Story like  successfully ', 200);
    }

    public function story_review(Request $request, $id)
    {
        $validatedData = Validator::make($request->all(), [
            'review' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return $this->error([], $validatedData->errors()->first(), 422);
        }



        $story = Story::findOrFail($id);

        StoryReview::create([
            'user_id' => Auth::id(),
            'story_id' => $story->id,
            'review' => $request->review,
        ]);

        return $this->success([], 'Story reviewed successfully ', 200);

    }

}
