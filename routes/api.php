<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{ AuthTokenController, ContactController, DashboardController, StripeController, UserController };

// Prefix: /api/

// Auth
Route::post("/register", [AuthTokenController::class, "register"]);
Route::post("/login", [AuthTokenController::class, "login"]);
Route::post("/logout", [AuthTokenController::class, "logout"])->middleware('auth:sanctum');

// User
Route::get('/dashboard', [DashboardController::class, "index"])->middleware('auth:sanctum');
Route::get('/user', [UserController::class, "index"])->middleware('auth:sanctum');

// Contact
Route::post('/contact',[ContactController::class, "send"]);

// Stripe Checkout
Route::post('/stripe/checkout', [StripeController::class, "checkout"])->middleware('auth:sanctum');
Route::get("/stripe/customer", [StripeController::class, "customer"])->middleware('auth:sanctum');
Route::post('/stripe/webhook', [StripeController::class, "webhook"]);
