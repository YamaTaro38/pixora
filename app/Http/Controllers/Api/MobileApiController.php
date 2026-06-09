<?php
// app/Http/Controllers/Api/MobileApiController.php
// Controller API untuk aplikasi mobile Flutter Pixora
//
// ⚠️ PENTING: Flutter sudah 100% native payment (TANPA Snap WebView).
// Semua endpoint /pay WAJIB menggunakan Midtrans Core API (bukan Snap)
// agar response berisi data pembayaran ASLI (VA number, QR URL, deeplink, payment code).

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MobileApiController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * POST /api/mobile/auth/google
     *
     * Login atau daftar user baru via Google Sign-In dari Flutter.
     * Flutter menggunakan package `google_sign_in` untuk mendapatkan
     * Google ID token, lalu mengirimkannya ke backend ini.
     *
     * Request body: { "google_token": "ya29.xxx..." }
     *
     * Response:
     *   - user: data user (id, name, email, avatar, role)
     *   - token: Sanctum Bearer token untuk request selanjutnya
     */
    public function googleLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $googleUser = \Laravel\Socialite\Facades\Socialite::driver('google')
                ->stateless()
                ->userFromToken($request->google_token);

            // Cari user berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                // User baru → buat akun (daftar via Google)
                $user = User::create([
                    'name'                 => $googleUser->getName(),
                    'email'                => $googleUser->getEmail(),
                    'avatar'               => $googleUser->getAvatar(),
                    'google_id'            => $googleUser->getId(),
                    'google_token'         => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'role'                 => 'customer',
                    'is_active'            => true,
                ]);

                Log::info('New user created via Google (mobile)', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);
            } else {
                // User sudah ada → update token
                $user->update([
                    'google_id'            => $googleUser->getId(),
                    'google_token'         => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar'               => $googleUser->getAvatar() ?? $user->avatar,
                ]);

                Log::info('Existing user logged in via Google (mobile)', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);
            }

            // Revoke token lama, buat Sanctum token baru
            $user->tokens()->delete();
            $token = $user->createToken('pixora-mobile')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user'    => [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone,
                    'avatar'    => $user->avatar,
                    'role'      => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            Log::error('Google login error (mobile): ' . $e->getMessage());
            return response()->json([
                'message' => 'Login dengan Google gagal: ' . $e->getMessage(),
            ], 401);
        }
    }

    /**
     * Get all active packages
     */
    public function packages()
    {
        $packages = Package::query()->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'description' => $package->description,
                    'image' => $package->image,
                    'image_url' => $package->image_url,
                    'price' => $package->price,
                    'down_payment' => $package->down_payment,
                    'duration_hours' => $package->duration_hours,
                    'edited_photos' => $package->edited_photos,
                    'location_type' => $package->location_type,
                    'inclusions' => $package->inclusions,
                    'is_active' => $package->is_active,
                    'sort_order' => $package->sort_order,
                ];
            });

        return response()->json($packages);
    }

    /**
     * Get package detail by slug
     */
    public function packageDetail($slug)
    {
        $package = Package::query()->where('slug', $slug)->where('is_active', true)->first();

        if (!$package) {
            return response()->json(['error' => 'Paket tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'image' => $package->image,
                'image_url' => $package->image_url,
                'price' => $package->price,
                'down_payment' => $package->down_payment,
                'duration_hours' => $package->duration_hours,
                'edited_photos' => $package->edited_photos,
                'location_type' => $package->location_type,
                'inclusions' => $package->inclusions,
                'is_active' => $package->is_active,
                'sort_order' => $package->sort_order,
                'galleries' => $package->galleries->map(function ($g) {
                    return [
                        'id' => $g->id,
                        'package_id' => $g->package_id,
                        'image' => $g->image,
                        'sort_order' => $g->sort_order,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get calendar data for a month
     */
    public function calendarData(Request $request)
    {
        $today = Carbon::today();
        $year = (int) $request->input('year', $today->year);
        $month = (int) $request->input('month', $today->month);

        if ($year < 2020 || $year > 2030) $year = $today->year;
        if ($month < 1 || $month > 12) $month = $today->month;

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $now = Carbon::now();
        $currentHour = (int) $now->format('H');
        $currentMinute = (int) $now->format('i');

        $confirmedBookings = Booking::query()->where('booking_date', '>=', $startDate)
            ->where('booking_date', '<=', $endDate)
            ->where(function ($q) {
                $q->where('payment_status', 'lunas')->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->get()
            ->groupBy(function ($b) {
                return $b->booking_date->format('Y-m-d');
            });

        $timeSlotsConfig = [
            'morning' => ['label' => 'Pagi', 'start' => '08:00', 'end' => '11:00', 'endHour' => 11, 'endMinute' => 0, 'icon' => 'fa-sun'],
            'afternoon' => ['label' => 'Siang', 'start' => '13:00', 'end' => '16:00', 'endHour' => 16, 'endMinute' => 0, 'icon' => 'fa-cloud-sun'],
            'evening' => ['label' => 'Sore', 'start' => '17:00', 'end' => '20:00', 'endHour' => 20, 'endMinute' => 0, 'icon' => 'fa-moon'],
        ];

        $calendar = [];
        $currentDate = Carbon::create($year, $month, 1);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $isToday = $currentDate->isToday();
            $isPast = $currentDate->lt($today);
            $dayBookings = $confirmedBookings->get($dateStr, collect());

            $slotsData = [];
            $availableCount = 0;

            foreach ($timeSlotsConfig as $slotKey => $config) {
                $isBooked = $dayBookings->contains('time_slot', $slotKey);
                $isPastSlot = false;
                if ($isToday) {
                    $currentTotalMinutes = ($currentHour * 60) + $currentMinute;
                    $slotEndMinutes = ($config['endHour'] * 60) + $config['endMinute'];
                    if ($currentTotalMinutes >= $slotEndMinutes) $isPastSlot = true;
                }

                $isAvailable = !$isBooked && !$isPastSlot && !$isPast;
                if ($isAvailable) $availableCount++;

                $slotsData[$slotKey] = [
                    'available' => $isAvailable,
                    'label' => $config['label'],
                    'start_time' => $config['start'],
                    'end_time' => $config['end'],
                    'icon' => $config['icon'],
                    'is_booked' => $isBooked,
                    'is_past_slot' => $isPastSlot,
                    'is_date_past' => $isPast,
                ];
            }

            $calendar[$dateStr] = [
                'date' => $dateStr,
                'day' => $currentDate->day,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'total_available_slots' => $availableCount,
                'slots' => $slotsData,
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'calendarData' => $calendar,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Check slot availability
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,evening',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = Booking::query()->where('booking_date', $request->date)
            ->where('time_slot', $request->time_slot)
            ->where(function ($q) {
                $q->where('payment_status', 'lunas')->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->exists();

        return response()->json(['available' => !$exists, 'date' => $request->date, 'time_slot' => $request->time_slot]);
    }

    /**
     * GET /api/bookings (AUTHENTICATED)
     *
     * Ambil daftar booking milik user yang sedang login.
     * Digunakan oleh Flutter untuk menampilkan riwayat booking.
     * 
     * Filter opsional: ?status=lunas|pending|expired&page=1&per_page=10
     * 
     * Response sudah diformat dengan data yang sama seperti bookingDetail
     * agar Flutter bisa render konsisten.
     */
    public function myBookings(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $query = Booking::with('package')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Filter by payment status if provided (lunas, pending, expired, dp_paid, cancelled)
        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $bookings = $query->paginate($perPage);

        // Format data booking agar konsisten dengan response bookingDetail
        $formatted = collect($bookings->items())->map(function ($booking) {
            return [
                'id'                     => $booking->id,
                'booking_code'           => $booking->booking_code,
                'public_token'           => $booking->public_token,
                'customer_name'          => $booking->customer_name,
                'customer_phone'         => $booking->customer_phone,
                'customer_email'         => $booking->customer_email,
                'package_id'             => $booking->package_id,
                'booking_date'           => $booking->booking_date->toDateString(),
                'time_slot'              => $booking->time_slot,
                'total_price'            => (float) $booking->total_price,
                'down_payment'           => (float) ($booking->down_payment ?? 0),
                'payment_status'         => $booking->payment_status,
                'session_status'         => $booking->session_status,
                'booking_status'         => $booking->booking_status,
                'special_requests'       => $booking->special_requests,
                'paid_at'                => $booking->paid_at?->toIso8601String(),
                'payment_method'         => $booking->payment_method,
                'payment_transaction_id' => $booking->payment_transaction_id,
                'expires_at'             => $booking->expires_at?->toIso8601String(),
                'package'                => $booking->package ? [
                    'id'   => $booking->package->id,
                    'name' => $booking->package->name,
                    'slug' => $booking->package->slug,
                    'image_url' => $booking->package->image_url,
                    'price' => (float) $booking->package->price,
                ] : null,
            ];
        });

        return response()->json([
            'success'    => true,
            'data'       => $formatted,
            'pagination' => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    /**
     * Store booking (JSON API)
     *
     * Membuat booking baru. Jika user sedang login (via Sanctum token),
     * booking akan otomatis terhubung ke akun user sehingga muncul
     * di riwayat booking mobile.
     *
     * Snap token TIDAK LAGI dibuat di sini karena Flutter menggunakan
     * Core API native payment.
     */
    public function storeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'booking_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'special_requests' => 'nullable|string',
            'payment_type' => 'required|in:full,down_payment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package = Package::query()->find($request->package_id);
        $amountToPay = $request->payment_type == 'full' ? $package->price : ($package->down_payment ?? $package->price * 0.5);
        $expiresAt = Carbon::now('Asia/Jakarta')->addMinutes(30);

        // Ambil user_id dari token jika user sedang login
        $userId = $request->user()?->id;

        try {
            $booking = Booking::create([
                'user_id' => $userId, // Link ke akun user (nullable untuk guest)
                'booking_code' => 'PX/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -6)),
                'public_token' => bin2hex(random_bytes(32)),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'package_id' => $package->id,
                'booking_date' => $request->booking_date,
                'time_slot' => $request->time_slot,
                'total_price' => $package->price,
                'down_payment' => $request->payment_type == 'down_payment' ? $amountToPay : 0,
                'payment_status' => 'pending',
                'session_status' => 'upcoming',
                'booking_status' => 'draft',
                'special_requests' => $request->special_requests,
                'expires_at' => $expiresAt,
                'slot_locked_until' => $expiresAt,
            ]);

            // ⚠️ TIDAK LAGI membuat Snap token di sini
            // Flutter akan memanggil POST /api/booking/{token}/pay untuk
            // inisiasi pembayaran via Midtrans Core API.
            // Snap WebView sudah dihapus dari Flutter.

            return response()->json([
                'success' => true,
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'public_token' => $booking->public_token,
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'customer_email' => $booking->customer_email,
                    'package_id' => $booking->package_id,
                    'booking_date' => $booking->booking_date->toDateString(),
                    'time_slot' => $booking->time_slot,
                    'total_price' => $booking->total_price,
                    'down_payment' => $booking->down_payment,
                    'payment_status' => $booking->payment_status,
                    'session_status' => $booking->session_status,
                    'booking_status' => $booking->booking_status,
                    'snap_token' => null, // Tidak ada snap token — gunakan native payment
                    'expires_at' => $booking->expires_at?->toIso8601String(),
                    'package' => ['name' => $package->name],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Mobile booking store error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get booking detail
     */
    public function bookingDetail($token)
    {
        $booking = Booking::query()->where('public_token', $token)->with('package')->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'public_token' => $booking->public_token,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'customer_email' => $booking->customer_email,
                'package_id' => $booking->package_id,
                'booking_date' => $booking->booking_date->toDateString(),
                'time_slot' => $booking->time_slot,
                'total_price' => $booking->total_price,
                'down_payment' => $booking->down_payment,
                'payment_status' => $booking->payment_status,
                'session_status' => $booking->session_status,
                'booking_status' => $booking->booking_status,
                'special_requests' => $booking->special_requests,
                'paid_at' => $booking->paid_at?->toIso8601String(),
                'payment_method' => $booking->payment_method,
                'payment_transaction_id' => $booking->payment_transaction_id,
                'snap_token' => $booking->snap_token,
                'expires_at' => $booking->expires_at?->toIso8601String(),
                'package' => $booking->package ? ['name' => $booking->package->name] : null,
            ],
        ]);
    }

    /**
     * Check payment status (polling dari mobile setiap ±3 detik)
     * Method ini akan query ke Midtrans untuk status real-time
     * sehingga mobile tidak selalu bergantung pada webhook.
     */
    public function bookingStatus($token)
    {
        $booking = Booking::query()->where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        $statusChanged = false;

        // Cek ke Midtrans jika booking masih pending & punya order id
        if ($booking->payment_status === 'pending' && $booking->midtrans_order_id) {
            try {
                $midtransStatus = $this->midtransService->getTransactionStatus($booking->midtrans_order_id);
                if ($midtransStatus && isset($midtransStatus->transaction_status)) {
                    $trxStatus = $midtransStatus->transaction_status;
                    $internalStatus = $this->midtransService->mapInternalStatus(
                        $trxStatus,
                        (float) ($midtransStatus->gross_amount ?? 0) >= (float) $booking->total_price
                    );

                    if ($internalStatus !== $booking->payment_status) {
                        $booking->payment_status = $internalStatus;
                        if (in_array($internalStatus, ['lunas', 'dp_paid'])) {
                            $booking->booking_status = 'confirmed';
                            $booking->session_status = 'upcoming';
                            $booking->paid_at = now();
                        } elseif (in_array($internalStatus, ['expired', 'cancelled'])) {
                            $booking->booking_status = 'cancelled';
                            $booking->session_status = 'cancelled';
                        }
                        $booking->payment_method = $this->midtransService->getReadablePaymentMethod(
                            $midtransStatus->payment_type ?? null
                        );
                        $booking->save();
                        $statusChanged = true;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('bookingStatus midtrans check error: ' . $e->getMessage());
            }
        }

        // Auto-expire jika sudah lewat expires_at
        if ($booking->payment_status === 'pending'
            && $booking->expires_at
            && $booking->expires_at->isPast()) {
            $booking->payment_status = 'expired';
            $booking->booking_status = 'cancelled';
            $booking->session_status = 'cancelled';
            $booking->save();
            $statusChanged = true;
        }

        return response()->json([
            'payment_status'  => $booking->payment_status,
            'payment_method'  => $booking->payment_method,
            'transaction_id'  => $booking->payment_transaction_id,
            'paid_at'         => $booking->paid_at?->toIso8601String(),
            'is_paid'         => in_array($booking->payment_status, ['lunas', 'dp_paid']),
            'is_expired'      => $booking->payment_status === 'expired',
            'is_cancelled'    => $booking->payment_status === 'cancelled',
            'booking_status'  => $booking->booking_status,
            'session_status'  => $booking->session_status,
            'status_changed'  => $statusChanged,
        ]);
    }

    /**
     * GET /api/booking/{token}/payment-methods
     *
     * Mengembalikan daftar metode pembayaran aktif Midtrans.
     * Dipakai Flutter untuk render pilihan di halaman payment.
     */
    public function paymentMethods($token)
    {
        $booking = Booking::query()->where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        if (in_array($booking->payment_status, ['lunas', 'dp_paid'])) {
            return response()->json([
                'error' => 'Booking sudah dibayar',
                'payment_status' => $booking->payment_status,
            ], 400);
        }

        $channels = $this->midtransService->getActivePaymentChannels();

        $methods = array_map(function ($ch) {
            return [
                'code'           => $ch['id'],
                'name'           => $ch['name'] ?? $ch['id'],
                'category'       => $ch['category'] ?? $this->midtransService->mapMethodCategory($ch['id']),
                'icon'           => $ch['icon'] ?? $ch['id'],
                'fee'            => $ch['fee'] ?? 0,
                'estimated_time' => $ch['estimated_time'] ?? '-',
                'active'         => true,
            ];
        }, $channels);

        $amount = $booking->down_payment > 0 ? $booking->down_payment : $booking->total_price;

        return response()->json([
            'success'         => true,
            'booking_token'   => $booking->public_token,
            'amount'          => (float) $amount,
            'currency'        => 'IDR',
            'expires_at'      => $booking->expires_at?->toIso8601String(),
            'methods'         => $methods,
        ]);
    }

    /**
     * POST /api/booking/{token}/pay
     *
     * ⚠️ WAJIB PAKAI CORE API — BUKAN SNAP!
     *
     * Flutter sudah menghapus Snap WebView. Endpoint ini menggunakan
     * Midtrans Core API agar response berisi data pembayaran ASLI:
     *   - VA → va_number (va_numbers[0])
     *   - QRIS → qr_url (actions: generate-qr-code)
     *   - E-Wallet → deeplink (actions: deeplink-redirect)
     *   - Retail → payment_code
     *
     * JANGAN return snap_token — Flutter sudah TIDAK support!
     *
     * Body: { "method": "bca_va" | "gopay" | "qris" | ... }
     */
    public function pay(Request $request, $token)
    {
        $validator = Validator::make($request->all(), [
            'method'        => 'required|string|max:50',
            'payment_type'  => 'nullable|in:full,down_payment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booking = Booking::query()->where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        if (in_array($booking->payment_status, ['lunas', 'dp_paid'])) {
            return response()->json([
                'error'          => 'Booking sudah dibayar',
                'payment_status' => $booking->payment_status,
            ], 400);
        }

        $method = $request->input('method');
        $amount = $booking->down_payment > 0 ? $booking->down_payment : $booking->total_price;

        try {
            // ✅ PAKAI CORE API - BUKAN Snap::createTransaction()!
            $charge = $this->midtransService->createCoreApiCharge($booking, $amount, $method);

            // Simpan data payment ke tabel payments
            $payment = Payment::create([
                'booking_id'     => $booking->id,
                'method'         => $method,
                'transaction_id' => $charge->transaction_id ?? $booking->midtrans_order_id,
                'amount'         => $amount,
                'status'         => 'pending',
                'raw_response'   => json_encode($charge),
                'expired_at'     => isset($charge->expiry_time)
                    ? Carbon::parse($charge->expiry_time)
                    : now()->addHours(24),
            ]);

            // Update booking dengan informasi payment
            $booking->payment_method = $this->midtransService->getReadablePaymentMethod($method);
            $booking->payment_transaction_id = $payment->transaction_id;
            $booking->save();

            // Format response yang Flutter harapkan (dengan data pembayaran ASLI)
            $formatted = $this->midtransService->formatCoreApiResponse($charge, $booking, $method);

            Log::info('Mobile pay via Core API success', [
                'booking_id' => $booking->id,
                'order_id'   => $booking->midtrans_order_id,
                'method'     => $method,
                'transaction_id' => $charge->transaction_id ?? null,
            ]);

            return response()->json([
                'success' => true,
                'payment' => $formatted,
            ]);
        } catch (\Exception $e) {
            Log::error('Mobile pay error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/booking/{token}/confirm-payment
     *
     * Endpoint opsional: user klaim sudah bayar (untuk VA yang mungkin
     * belum ter-update statusnya). Backend akan cross-check ke Midtrans.
     */
    public function confirmPayment(Request $request, $token)
    {
        $booking = Booking::query()->where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        if (in_array($booking->payment_status, ['lunas', 'dp_paid'])) {
            return response()->json([
                'success'        => true,
                'payment_status' => $booking->payment_status,
                'message'        => 'Booking sudah dibayar',
            ]);
        }

        if (!$booking->midtrans_order_id) {
            return response()->json([
                'success' => false,
                'error'   => 'Belum ada transaksi Midtrans untuk booking ini',
            ], 400);
        }

        try {
            $midtransStatus = $this->midtransService->getTransactionStatus($booking->midtrans_order_id);
            if (!$midtransStatus || !isset($midtransStatus->transaction_status)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Tidak dapat memverifikasi status ke Midtrans',
                ], 502);
            }

            $trxStatus = $midtransStatus->transaction_status;
            $internalStatus = $this->midtransService->mapInternalStatus(
                $trxStatus,
                (float) ($midtransStatus->gross_amount ?? 0) >= (float) $booking->total_price
            );

            $booking->payment_status = $internalStatus;
            if (in_array($internalStatus, ['lunas', 'dp_paid'])) {
                $booking->booking_status = 'confirmed';
                $booking->session_status = 'upcoming';
                $booking->paid_at = now();
            }
            $booking->payment_method = $this->midtransService->getReadablePaymentMethod(
                $midtransStatus->payment_type ?? null
            );
            $booking->save();

            // Update payment record juga
            $payment = $booking->payment;
            if ($payment) {
                $payment->status = $internalStatus;
                $payment->paid_at = in_array($internalStatus, ['lunas', 'dp_paid']) ? now() : null;
                $payment->save();
            }

            return response()->json([
                'success'        => true,
                'payment_status' => $internalStatus,
                'message'        => 'Status pembayaran berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            Log::error('confirmPayment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => 'Gagal mengkonfirmasi pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * COD payment for mobile
     */
    public function bookingPayCod($token)
    {
        $booking = Booking::query()->where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        if ($booking->payment_status === 'pending') {
            $booking->payment_method = 'Bayar di Tempat (COD)';
            $booking->booking_status = 'confirmed';
            $booking->session_status = 'upcoming';
            $booking->save();

            Log::info('Mobile booking confirmed via COD', [
                'booking_id' => $booking->id,
                'time_slot' => $booking->time_slot
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dikonfirmasi dengan metode COD',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Status booking tidak valid untuk COD'], 400);
    }
}