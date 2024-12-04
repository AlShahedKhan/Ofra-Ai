<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\ContactFormController;

// Open Routes
Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);
Route::post('forgot-password', [ApiController::class, 'forgotPassword']);
Route::post('reset-password', [ApiController::class, 'resetPassword']);

Route::post('/contact-form', [ContactFormController::class, 'submit']);
// Route::get('/contact-show', [ContactFormController::class, 'show']);



// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function () {
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh-token", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    Route::get('/contact', [ContactFormController::class, 'index']);
    Route::get('/contact-show/{id}', [ContactFormController::class, 'show']);
    Route::delete('/contact-delete/{id}', [ContactFormController::class, 'destroy']);
});
