<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\Auth\UserAuthController;
use App\Http\Controllers\Api\User\Backend\FollowController;
use App\Http\Controllers\Api\User\Backend\UserHomeController;
use App\Http\Controllers\Api\User\Backend\UserEventController;
use App\Http\Controllers\Api\User\Backend\UserStoryController;
use App\Http\Controllers\Api\User\Backend\UserSearchController;
use App\Http\Controllers\Api\User\Backend\GoogleLoginController;
use App\Http\Controllers\Api\User\Backend\UserAccountController;
use App\Http\Controllers\Api\User\Backend\UserPaymentController;
use App\Http\Controllers\Api\User\Backend\EventBookingController;
use App\Http\Controllers\Api\User\Backend\UserRelationshipController;

// Public User API Routes
Route::prefix('user')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
    
    Route::post('google/signin', [GoogleLoginController::class, 'googlesignin']);



    // Password Management
    Route::post('password/request-otp', [UserAuthController::class, 'requestOtp']);
    Route::post('password/verify-otp', [UserAuthController::class, 'verifyOtp']);
    Route::post('password/reset', [UserAuthController::class, 'resetPassword']);
});

Route::get('event/{id}/ticket-download', [EventBookingController::class, 'download_ticket']);



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
    Route::get('event-booking/{id}/order-summary', [EventBookingController::class, 'order_summary']);
    // event ticket
    Route::get('event/{id}/ticket', [EventBookingController::class, 'event_ticket']);
    Route::get('event/{id}/ticket-download', [EventBookingController::class, 'download_ticket']);

  

    Route::post('/stripe/create-payment-intent', [UserPaymentController::class, 'createPaymentIntent']);
    Route::post('/stripe/confirm-payment', [UserPaymentController::class, 'confirmPayment']);
    Route::post('/stripe/webhook', [UserPaymentController::class, 'webhookHandler']); // Optional for webhooks
    

    //event history

    Route::get('event/history', [UserHomeController::class, 'event_history']);
    Route::get('event/history/{id}/details', [UserHomeController::class, 'event_history_details']);

    // event review
    Route::post('event/{id}/review', [UserEventController::class, 'event_review']);

    // Categories
    Route::get('categories', [UserHomeController::class, 'categories']);
    Route::get('categories/{id}/explore-events', [UserHomeController::class, 'explore_event']);

    Route::get('/categories/{id}/tailored-events', [UserHomeController::class, 'tailored_event']);
    Route::get('/categories/{id}/random-events', [UserHomeController::class, 'random_event']);

    Route::get('category-event/details/{id}', [UserHomeController::class, 'category_event_details']);


    // Account Management
    Route::get('account/profile', [UserAccountController::class, 'account_profile']);
    Route::get('account/profile/edit', [UserAccountController::class, 'edit']);
    Route::post('account/profile/update', [UserAccountController::class, 'update_profile']);
    Route::get('account/preferences', [UserAuthController::class, 'preferences']);
    Route::post('account/preferences/update', [UserAuthController::class, 'update_preferences']);
    Route::get('account/faq', [UserAccountController::class, 'user_faq']);




    // User search management
    Route::get('/search/histories', [UserSearchController::class, 'getSearchHistory']);
    Route::get('/search/results', [UserSearchController::class, 'searchUsers']);
    Route::get('/search/suggestions', [UserSearchController::class, 'getSuggestions']);

    // delete single search history
    Route::delete('/search/history/{id}', [UserSearchController::class, 'deleteSearchHistory']); 
    // delete user all search history
    Route::delete('/all-search/history', [UserSearchController::class, 'deleteAllSearchHistory']);


    // user notification
  
    // get the notifications
    Route::get('today/notifications', [UserHomeController::class, 'notifications']);
    // previous notifications
    Route::get('previous/notifications', [UserHomeController::class, 'previousDayNotifications']);
});
