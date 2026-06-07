<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\AIController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\LandingPageController;


// ========== CUSTOMER ROUTES ==========
// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google Login
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// Profile Routes (Auth Required)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Halaman Customer (Tanpa Login)
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/paket', [HomeController::class, 'packages'])->name('packages');
Route::get('/paket/{slug}', [HomeController::class, 'packageDetail'])->name('package.detail');
Route::get('/kalender', [CalendarController::class, 'index'])->name('calendar');
Route::get('/kalender/data', [CalendarController::class, 'getMonthData'])->name('calendar.data');

// Booking Flow (Tanpa Login)
Route::prefix('booking')->name('booking.')->group(function () {
    Route::post('/check-availability', [BookingController::class, 'checkAvailability'])->name('check');
    Route::post('/store', [BookingController::class, 'store'])->name('store');
    Route::get('/{token}/payment', [BookingController::class, 'payment'])->name('payment');
    Route::get('/{token}', [BookingController::class, 'show'])->name('show');
    Route::get('/{token}/checkout', [BookingController::class, 'checkout'])->name('checkout');
    Route::get('/{token}/status', [BookingController::class, 'checkPaymentStatus'])->name('check-status');
    Route::post('/{token}/update-method', [BookingController::class, 'updatePaymentMethod'])->name('payment.update-method');
    Route::post('/{token}/cod', [BookingController::class, 'payCod'])->name('cod');
    
});
Route::get('/booking/{token}/invoice', [BookingController::class, 'invoice'])->name('booking.invoice');
Route::get('/booking/{token}/download-invoice', [BookingController::class, 'downloadInvoice'])->name('booking.download-invoice');

Route::post('/payment/midtrans-callback', [BookingController::class, 'paymentCallback'])->name('payment.callback');
Route::post('/api/midtrans-callback', [BookingController::class, 'paymentCallback'])->name('api.payment.callback');

// AI Endpoints
Route::prefix('ai')->name('ai.')->group(function () {
    Route::post('/chat', [AIController::class, 'chat'])->name('chat');
});

// ========== ADMIN ROUTES ==========
Route::prefix('admin')->name('admin.')->group(function () {
    // Login (tanpa middleware auth)
    Route::get('login', [App\Http\Controllers\Admin\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Admin\LoginController::class, 'login']);
    Route::post('logout', [App\Http\Controllers\Admin\LoginController::class, 'logout'])->name('logout');
    
    // Protected routes with auth and admin middleware
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('bookings', AdminBookingController::class);
        Route::post('bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.update-status');
        
        Route::resource('packages', AdminPackageController::class);
        Route::post('packages/update-order', [AdminPackageController::class, 'updateOrder'])->name('packages.update-order');
        Route::delete('package-gallery/{id}', [AdminPackageController::class, 'deleteGalleryImage'])->name('package-gallery.delete');
        Route::put('packages/{id}/deactivate', [AdminPackageController::class, 'deactivate'])->name('packages.deactivate');

        Route::get('reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [AdminReportController::class, 'export'])->name('reports.export');


        Route::resource('customers', CustomerController::class);
        Route::post('customers/{id}/toggle-block', [CustomerController::class, 'toggleBlock'])->name('customers.toggle-block');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('landing-page', [LandingPageController::class, 'index'])->name('landing-page.index');
        Route::post('landing-page/hero', [LandingPageController::class, 'updateHero'])->name('landing-page.hero');
        Route::post('landing-page/about', [LandingPageController::class, 'updateAbout'])->name('landing-page.about');
        Route::post('landing-page/features', [LandingPageController::class, 'updateFeatures'])->name('landing-page.features');
        Route::post('landing-page/gallery', [LandingPageController::class, 'updateGallery'])->name('landing-page.gallery');
        Route::post('landing-page/testimonials', [LandingPageController::class, 'updateTestimonials'])->name('landing-page.testimonials');
        Route::post('landing-page/upload-testimonial-photo', [LandingPageController::class, 'uploadTestimonialPhoto'])->name('landing-page.upload-testimonial-photo');
        Route::post('landing-page/gallery-delete', [LandingPageController::class, 'deleteGalleryImage'])->name('landing-page.gallery-delete');
    });
});