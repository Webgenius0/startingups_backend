<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\Auth\UserAuthController;
use App\Http\Controllers\Api\User\Backend\FollowController;
use App\Http\Controllers\Api\User\Backend\UserHomeController;
use App\Http\Controllers\Api\User\Backend\UserEventController;
use App\Http\Controllers\Api\User\Backend\UserStoryController;
use App\Http\Controllers\Api\User\Backend\UserAccountController;
use App\Http\Controllers\Api\User\Backend\EventBookingController;
use App\Http\Controllers\Api\User\Backend\UserRelationshipController;
use App\Http\Controllers\Api\User\Backend\UserSearchController;

// Public User API Routes
Route::prefix('user')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
});

// Protected User API Routes
Route::middleware(['auth:user', 'role:user'])->prefix('auth-user')->group(function () {
    // Authentication & Profile
    Route::post('refresh', [UserAuthController::class, 'refresh']);
    Route::post('logout', [UserAuthController::class, 'logout']);
    Route::get('profile', [UserAuthController::class, 'profile']);

    // location update
    Route::post('location/update', [UserAuthController::class, 'user_location']);


    // Follow and unfollow Management
    Route::post('users/{followeeId}/follow', [UserRelationshipController::class, 'follow']);
    Route::post('users/{followeeId}/unfollow', [UserRelationshipController::class, 'unfollow']);
    Route::get('users/{userId}/followers', [UserRelationshipController::class, 'followers']);
    Route::get('users/{userId}/followees', [UserRelationshipController::class, 'followees']);



    // Password Management
    Route::post('password/request-otp', [UserAuthController::class, 'requestOtp']);
    Route::post('password/verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('password/reset', [UserAuthController::class, 'resetPassword']);

    // Stories
    Route::post('story', [UserStoryController::class, 'store']);
    Route::get('story/{id}', [UserStoryController::class, 'show']);
    Route::post('story/{id}/like', [UserStoryController::class, 'story_like']);
    Route::post('story/{id}/review', [UserStoryController::class, 'story_review']);

    // story lists
    Route::get('/story-lists', [UserStoryController::class, 'story_lists']);

    // Events
    Route::get('event/upcoming', [UserHomeController::class, 'events']);
    Route::get('upcoming-freind-events', [UserHomeController::class, 'friend_events']);

    Route::get('upcoming-event/details/{id}', [UserHomeController::class, 'event_details']);


    // event booking
    Route::post('event/{id}/booking', [EventBookingController::class, 'event_book']);

    // event order summary
    Route::get('event-booking/{id}/order-summary', [EventBookingController::class, 'order_summary']);


    // event review
    Route::post('event/{id}/review', [UserEventController::class, 'event_review']);

    // Categories
    Route::get('categories', [UserHomeController::class, 'categories']);
    Route::get('categories/{id}/explore-events', [UserHomeController::class, 'explore_event']);

    Route::get('/categories/{id}/tailored-events', [UserHomeController::class, 'tailored_event']);
    Route::get('/categories/{id}/random-events', [UserHomeController::class, 'random_event']);

    Route::get('category-event/details/{id}', [UserHomeController::class, 'category_event_details']);


    // Account Management
    Route::get('profile/edit', [UserAccountController::class, 'edit']);
    Route::post('profile/update', [UserAccountController::class, 'update_profile']);
    Route::get('preferences', [UserAuthController::class, 'preferences']);
    Route::post('preferences', [UserAuthController::class, 'update_preferences']);
    Route::get('faq', [UserAccountController::class, 'user_faq']);




    // User search management
    Route::get('/search/histories', [UserSearchController::class, 'getSearchHistory']);
    Route::get('/search/results', [UserSearchController::class, 'searchUsers']);
    Route::get('/search/suggestions', [UserSearchController::class, 'getSuggestions']);






});
