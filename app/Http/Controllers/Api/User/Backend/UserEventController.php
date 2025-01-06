<?php

namespace App\Http\Controllers\Api\User\Backend;

use App\Models\Event;
use App\Helper\Helper;
use App\Models\EventReview;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserEventController extends Controller
{

    use ApiResponse;



    // __store event review
    public function event_review(Request $request, $id)
    {

        $validatedData = Validator::make($request->all(), [
            'rating' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            return $this->error([], $validatedData->errors()->first(), 422);
        }

        $event = Event::findOrFail($id);

        if (!$event) {
            return $this->error([], 'Event not found', 404);
        }

        if ($request->hasFile('cover')) {
            $coverPath = $request->hasFile('cover')
            ? Helper::uploadImage($request->file('cover'), 'business_profiles')
            : null;;

        }

        $data =  EventReview::create([
            'user_id' => Auth::id(),
            'event_id' => $event->id,
            'review' => $request->review,
            'rating' => $request->rating,
            'cover' => $coverPath ?? null,

        ]);
        $data->cover = $data->cover ?  url($data->cover) : null;


        return $this->success($data, 'Event reviewed successfully ', 200);


    }
}
