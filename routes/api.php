<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CaseExampleController;
use App\Http\Controllers\ContactFormController;
use App\Http\Controllers\TestimonialController;

// Open Routes
Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);
Route::post('forgot-password', [ApiController::class, 'forgotPassword']);
Route::post('reset-password', [ApiController::class, 'resetPassword']);

Route::post('/contact-form', [ContactFormController::class, 'submit']);

Route::post('/contact-form-send', [ContactFormController::class, 'submitSend']);

Route::post('/chat', [ChatbotController::class, 'handleChat']);



// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function () {
    Route::get("users", [ApiController::class, "users"]);
    Route::post("users/create", [ApiController::class, "store"]);
    Route::get("users/show", [ApiController::class, "getAllUsers"]);
    Route::post("users/active-inactive/{id}", [ApiController::class, "activeInactive"]);
    Route::delete("users/delete/{id}", [ApiController::class, "destroy"]);
    Route::get("profile", [ApiController::class, "profile"]);
    Route::put("update-profile", [UserController::class, "updateProfile"]);
    Route::get("refresh-token", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    Route::get('users/graph/{year?}/{month?}', [ApiController::class, 'getGraphData']);

    Route::get('/contact', [ContactFormController::class, 'index']);
    Route::get('/contact-show/{id}', [ContactFormController::class, 'show']);
    Route::put('/contact-update/{id}', [ContactFormController::class, 'update']); // New route for updating
    Route::delete('/contact-delete/{id}', [ContactFormController::class, 'destroy']);

    Route::post('/blogs/{blog}', [BlogController::class, 'update']);

    Route::apiResource('blogs', BlogController::class);

    Route::apiResource('testimonials', TestimonialController::class);

    Route::get('case-examples', [CaseExampleController::class, 'index']);
    Route::post('case-examples', [CaseExampleController::class, 'store']);
    Route::get('case-examples/{id}', [CaseExampleController::class, 'show']);
    Route::post('case-examples/{id}', [CaseExampleController::class, 'update']);
    Route::delete('case-examples/{id}', [CaseExampleController::class, 'destroy']);
});
