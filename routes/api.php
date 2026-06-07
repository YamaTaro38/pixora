<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\Api\MobileApiController;
use Illuminate\Support\Facades\Route;

// ========== AUTH ROUTES (PUBLIC) ==========
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/google-signin', [AuthController::class, 'googleSignInApi']);

// ========== AUTH ROUTES (PROTECTED - butuh Sanctum token) ==========
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);
});

// ========== MOBILE API ROUTES (PUBLIC) ==========
// Packages
Route::get('/packages', [MobileApiController::class, 'packages']);
Route::get('/packages/{slug}', [MobileApiController::class, 'packageDetail']);

// Calendar
Route::get('/calendar/data', [MobileApiController::class, 'calendarData']);

// Booking
Route::post('/booking/check-availability', [MobileApiController::class, 'checkAvailability']);
Route::post('/booking/store', [MobileApiController::class, 'storeBooking']);
Route::get('/booking/{token}', [MobileApiController::class, 'bookingDetail']);
Route::get('/booking/{token}/status', [MobileApiController::class, 'bookingStatus']);
Route::post('/booking/{token}/cod', [MobileApiController::class, 'bookingPayCod']);

// ========== AI CHATBOT (PUBLIC - tanpa CSRF) ==========
Route::post('/ai/chat', [AIController::class, 'chat']);

// ========== HEALTH CHECK ==========
Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'Pixora API is running',
        'timestamp' => now()->toIso8601String(),
    ]);
});