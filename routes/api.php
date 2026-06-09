<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\MobileApiController;
use App\Http\Controllers\Api\MidtransWebhookController;
use Illuminate\Support\Facades\Route;

// ========== AUTH ROUTES (PUBLIC) ==========
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/google-signin', [AuthController::class, 'googleSignInApi']);

// Mobile Google login/daftar (opsional — pakai MobileApiController juga)
Route::post('/mobile/auth/google', [MobileApiController::class, 'googleLogin']);

// ========== AUTH ROUTES (PROTECTED - butuh Sanctum token) ==========
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);

    // Booking history for authenticated user (riwayat booking di mobile)
    // Flutter request ke: GET /api/booking/my
    Route::get('/bookings', [MobileApiController::class, 'myBookings']);
    Route::get('/booking/my', [MobileApiController::class, 'myBookings']);

    // Store booking (WAJIB login — agar user_id terisi dan muncul di riwayat)
    Route::post('/booking/store', [MobileApiController::class, 'storeBooking']);
});

// ========== MOBILE API ROUTES (PUBLIC) ==========
// Packages
Route::get('/packages', [MobileApiController::class, 'packages']);
Route::get('/packages/{slug}', [MobileApiController::class, 'packageDetail']);

// Calendar
Route::get('/calendar/data', [MobileApiController::class, 'calendarData']);

// Booking (public - tanpa auth)
Route::post('/booking/check-availability', [MobileApiController::class, 'checkAvailability']);
Route::get('/booking/{token}', [MobileApiController::class, 'bookingDetail']);
Route::get('/booking/{token}/status', [MobileApiController::class, 'bookingStatus']);
Route::post('/booking/{token}/cod', [MobileApiController::class, 'bookingPayCod']);

// Mobile native payment flow (Midtrans Core API — WAJIB, bukan Snap)
Route::get('/booking/{token}/payment-methods', [MobileApiController::class, 'paymentMethods']);
Route::post('/booking/{token}/pay', [MobileApiController::class, 'pay']);
Route::post('/booking/{token}/confirm-payment', [MobileApiController::class, 'confirmPayment']);

// Midtrans webhook (WAJIB publik, dipanggil server Midtrans)
Route::post('/midtrans/notification', [MidtransWebhookController::class, 'handle']);
Route::get('/midtrans/ping', [MidtransWebhookController::class, 'ping']);

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