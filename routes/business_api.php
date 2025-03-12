<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\Backend\UserHomeController;
use App\Http\Controllers\Api\Business\Backend\EventController;
use App\Http\Controllers\Api\Business\Auth\BusinessAuthController;
use App\Http\Controllers\Api\Business\Backend\EventReportController;
use App\Http\Controllers\Api\Business\Backend\GoogleLoginController;
use App\Http\Controllers\Api\Business\Backend\SubscriptionController;
use App\Http\Controllers\Api\Business\Backend\BusinessPayoutController;
use App\Http\Controllers\Api\Business\Backend\BusinessAccountController;
use App\Http\Controllers\Api\Business\Backend\BusinessProfileController;
use App\Http\Controllers\Api\Business\Backend\StripeOnboardingController;

// Public Business API Routes
Route::prefix('business')->group(function () {
    Route::post('register', [BusinessAuthController::class, 'register']);
    Route::post('login', [BusinessAuthController::class, 'login']);

    Route::post('google/signin', [GoogleLoginController::class, 'googlesignin']);

    // categories and sub categories
    Route::get('categories', [UserHomeController::class, 'categories']);
    Route::get('categories/{id}/sub-categories', [UserHomeController::class, 'sub_categories']);
});

// countires routes
Route::get('/countries', [UserHomeController::class, 'countries']);



Route::get('/user-profile/{id}', [UserHomeController::class, 'user_profile']);
Route::get('/user/{id}/recent-places', [UserHomeController::class, 'user_recent_places']);
Route::get('/user/{id}/interested', [UserHomeController::class, 'user_interested']);


// Password Management
Route::post('password/request-otp', [BusinessAuthController::class, 'requestOtp']);
Route::post('password/verify-otp', [BusinessAuthController::class, 'verifyOtp']);
Route::post('password/reset', [BusinessAuthController::class, 'resetPassword']);



// Protected Business API Routes
Route::middleware(['auth:business', 'role:business'])->prefix('auth-business')->group(function () {

    // Authentication & Profile
    Route::post('refresh', [BusinessAuthController::class, 'refresh']);
    Route::post('logout', [BusinessAuthController::class, 'logout']);
    // Route::get('profile', [BusinessAuthController::class, 'profile']);
    // Route::post('profile', [BusinessAuthController::class, 'update_profile']);


    // Subscription Management
    Route::get('subscription/plans', [SubscriptionController::class, 'index']);


    // Business Profile Management
    Route::post('business-profile/create', [BusinessProfileController::class, 'store']);
    Route::get('business-profile/show/{id}', [BusinessProfileController::class, 'business_profile_details']);
    Route::post('business-profile/update/{id}', [BusinessProfileController::class, 'business_profile_update']);


    // Events
    Route::post('event/create', [EventController::class, 'store']);
    // Route::post('event/send-invite', [EventController::class, 'send_invite']);


    // events reports
    Route::get('event-reports', [EventReportController::class, 'event_details'])->name('business.event_reports');
    Route::get('all-event-reports', [EventReportController::class, 'all_event_reports']);
    Route::get('single-event-reports/{id}', [EventReportController::class, 'signle_event_reports']);
    Route::get('event/analysis', [EventReportController::class, 'event_analysis']);

    // schedule events
    Route::post('schedule/events', [EventReportController::class, 'schedule_events']);


    // event ratings
    Route::get('/event/ratings/{id}', [EventReportController::class, 'event_ratings']);


    // Account Management
    Route::get('account/profile', [BusinessAccountController::class, 'account_profile']);
    Route::get('account/profile/edit', [BusinessAccountController::class, 'edit']);
    Route::post('account/profile/update', [BusinessAccountController::class, 'update_profile']);
    Route::get('account/faq', [BusinessAccountController::class, 'business_faq']);


    // stripe onboarding
    Route::post('account/user/onboarding', [StripeOnboardingController::class, 'onboard']);
    
    // stripe payout
    Route::get('account/balance', [BusinessPayoutController::class, 'getBalance']);
    Route::post('account/payout/withdraw', [BusinessPayoutController::class, 'withdraw']);
    Route::get('account/transactions', [BusinessPayoutController::class, 'getAllTransactionHistory']);



    // get the notifications
    Route::get('today/notifications', [BusinessAuthController::class, 'notifications']);
    // previous notifications
    Route::get('previous/notifications', [BusinessAuthController::class, 'previousDayNotifications']);
});
