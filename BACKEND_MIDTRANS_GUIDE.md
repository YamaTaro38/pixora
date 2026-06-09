# 📋 PANDUAN LENGKAP BACKEND LARAVEL + MIDTRANS

> **PENTING**: Aplikasi Flutter sudah **100% native payment**. Semua pembayaran dilakukan langsung di dalam app tanpa membuka halaman Midtrans Snap/browser. Backend **WAJIB mengembalikan data pembayaran asli** (VA number, QR code URL, deeplink, payment code) dari Midtrans Core API. Jika backend hanya return `snap_token`, Flutter akan TETAP menampilkan error karena Snap WebView sudah dihapus.

---

## 🎯 Yang Harus Anda Lakukan di Backend

Anda perlu membuat **6 endpoint API** + **1 webhook handler** di Laravel:

| # | Endpoint | Fungsi |
|---|---|---|
| 0 | `POST /api/booking/store` | **WAJIB**: Simpan booking baru ke database |
| 0 | `GET /api/booking/my` | **WAJIB**: Ambil riwayat booking user yang login |
| 1 | `GET /api/booking/{token}/payment-methods` | Ambil daftar metode dari Midtrans |
| 2 | `POST /api/booking/{token}/pay` | Inisiasi pembayaran (generate VA/QR/deeplink) — **WAJIB return data asli** |
| 3 | `GET /api/booking/{token}/status` | Polling status pembayaran |
| 4 | `POST /api/booking/{token}/confirm-payment` | User konfirmasi "saya sudah bayar" |
| 5 | `POST /api/midtrans/notification` | Webhook dari Midtrans (WAJIB) |

### 🚨 Mengapa Riwayat Booking Kosong?

Saat ini aplikasi berjalan di **demo mode** — semua data booking hanya disimpan di memori Flutter (tidak di database). Akibatnya:

1. User booking → API `POST /api/booking/store` **gagal/return error** → booking tidak tersimpan di database
2. User bayar (demo) → status berubah jadi lunas → tapi tetap tidak ada di database
3. User buka Riwayat Booking → API `GET /api/booking/my` **kembalikan data kosong** → tidak muncul

**Solusi**: Implementasikan 2 endpoint di bawah ini, lalu set `kEnableDemoMode = false` di `BookingProvider` agar semua data benar-benar dari backend.

---

## 🗄️ STEP 0: Booking Controller — WAJIB Sebelum Payment

Sebelum implementasi pembayaran, pastikan 2 endpoint ini sudah jalan:

| Endpoint | Method | Fungsi |
|---|---|---|
| `/api/booking/store` | POST | Simpan booking baru ke database |
| `/api/booking/my` | GET | Ambil daftar booking milik user yang login |

### ✅ Model Booking Migration

```bash
php artisan make:migration create_bookings_table
```

```php
Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('booking_code')->unique();
    $table->string('public_token')->unique();
    $table->string('customer_name');
    $table->string('customer_phone');
    $table->string('customer_email')->nullable();
    $table->foreignId('package_id')->constrained();
    $table->date('booking_date');
    $table->string('time_slot'); // morning|afternoon|evening
    $table->decimal('total_price', 12, 2);
    $table->decimal('down_payment', 12, 2)->nullable();
    $table->string('payment_status')->default('pending'); // pending|lunas|dp_paid|cancelled|expired
    $table->string('session_status')->default('upcoming');
    $table->string('booking_status')->default('draft');
    $table->timestamp('expires_at')->nullable();
    $table->text('special_requests')->nullable();
    $table->text('admin_notes')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->string('payment_method')->nullable();
    $table->string('payment_transaction_id')->nullable();
    $table->timestamps();
});
```

### ✅ BookingController

```bash
php artisan make:controller BookingController
```

```php
<?php
// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * POST /api/booking/store
     * Simpan booking baru dari Flutter.
     * WAJIB return booking dengan public_token, booking_code, dll.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'booking_date' => 'required|date',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'special_requests' => 'nullable|string',
            'payment_type' => 'required|in:full,dp',
        ]);

        $package = Package::findOrFail($validated['package_id']);
        $totalPrice = $package->price;
        $downPayment = $validated['payment_type'] === 'dp' ? ($package->dp_price ?? $totalPrice * 0.5) : null;

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'booking_code' => 'PXR-' . strtoupper(Str::random(8)),
            'public_token' => Str::random(32),
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_email' => $validated['customer_email'],
            'package_id' => $validated['package_id'],
            'booking_date' => $validated['booking_date'],
            'time_slot' => $validated['time_slot'],
            'total_price' => $totalPrice,
            'down_payment' => $downPayment,
            'payment_status' => 'pending',
            'special_requests' => $validated['special_requests'],
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'booking' => $booking->load('package'),
            'message' => 'Booking berhasil dibuat',
        ], 201);
    }

    /**
     * GET /api/booking/my
     * Ambil semua booking milik user yang sedang login.
     * WAJIB filter by user_id dari token Sanctum!
     */
    public function myBookings(Request $request)
    {
        $bookings = Booking::with('package')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'booking_code' => $b->booking_code,
                    'public_token' => $b->public_token,
                    'customer_name' => $b->customer_name,
                    'customer_phone' => $b->customer_phone,
                    'customer_email' => $b->customer_email,
                    'package_id' => $b->package_id,
                    'booking_date' => $b->booking_date->format('Y-m-d'),
                    'time_slot' => $b->time_slot,
                    'total_price' => (double) $b->total_price,
                    'down_payment' => $b->down_payment ? (double) $b->down_payment : null,
                    'payment_status' => $b->payment_status,
                    'session_status' => $b->session_status,
                    'booking_status' => $b->booking_status,
                    'expires_at' => $b->expires_at?->toIso8601String(),
                    'special_requests' => $b->special_requests,
                    'admin_notes' => $b->admin_notes,
                    'paid_at' => $b->paid_at?->toIso8601String(),
                    'payment_method' => $b->payment_method,
                    'payment_transaction_id' => $b->payment_transaction_id,
                    'package' => $b->package ? [
                        'name' => $b->package->name,
                    ] : null,
                ];
            });

        return response()->json([
            'bookings' => $bookings,
        ]);
    }
}
```

### ✅ Tambahkan Routes

Di `routes/api.php`:

```php
use App\Http\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {
    // Booking
    Route::post('/booking/store', [BookingController::class, 'store']);
    Route::get('/booking/my', [BookingController::class, 'myBookings']);

    // ... Payment routes di bawah
});
```

### ✅ Langkah Terakhir di Flutter

Setelah backend booking selesai, **matikan demo mode** di `lib/providers/booking_provider.dart`:

```dart
// Ubah dari true → false
static const bool kEnableDemoMode = false;
```

Dengan itu, semua data booking akan tersimpan di database dan muncul di riwayat booking.

---

## 📦 STEP 1: Install Midtrans SDK di Laravel

```bash
composer require midtrans/midtrans-php
```

### Konfigurasi `.env`:
```env
MIDTRANS_SERVER_KEY=SB-Mid-server-XXXXXXXXXXXX
MIDTRANS_CLIENT_KEY=SB-Mid-client-XXXXXXXXXXXX
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_NOTIFICATION_URL=https://yourdomain.com/api/midtrans/notification
```

> 💡 Ambil `SB-Mid-server-...` dan `SB-Mid-client-...` dari dashboard Midtrans Sandbox: https://dashboard.sandbox.midtrans.com/

### Konfigurasi `config/midtrans.php`:
```php
<?php
return [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),
];
```

### Setup di `AppServiceProvider.php`:
```php
use Midtrans\Config;

public function boot()
{
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$notifUrl = config('midtrans.notification_url');
    // Opsional: set untuk sanitasi SSL saat development
    // Config::$curlOptions = [
    //     CURLOPT_SSL_VERIFYHOST => 0,
    //     CURLOPT_SSL_VERIFYPEER => 0,
    // ];
}
```

---

## 🗄️ STEP 2: Tabel `payments` (Migration)

```bash
php artisan make:migration create_payments_table
```

```php
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('booking_id')->constrained()->onDelete('cascade');
    $table->string('method');                    // ex: 'bca_va', 'gopay'
    $table->string('transaction_id')->nullable(); // ID dari Midtrans
    $table->string('snap_token')->nullable();    // Snap token
    $table->decimal('amount', 12, 2);
    $table->decimal('fee', 12, 2)->default(0);
    $table->string('status')->default('pending'); // pending|lunas|expired|cancelled
    $table->string('va_number')->nullable();     // VA dari Midtrans
    $table->string('bank_code')->nullable();
    $table->string('payment_code')->nullable();  // untuk retail
    $table->text('qr_url')->nullable();
    $table->text('qr_string')->nullable();
    $table->text('deeplink')->nullable();
    $table->text('raw_response')->nullable();    // simpan response mentah Midtrans
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('expired_at')->nullable();
    $table->timestamps();
});
```

---

## 🎮 STEP 3: Controller `PaymentController` — WAJIB PAKAI CORE API

**⚠️ PENTING**: Flutter sudah menghapus dukungan untuk Midtrans Snap. Anda **WAJIB** menggunakan `Midtrans\CoreApi::charge()` (bukan Snap) agar response berisi VA number / QR URL / deeplink asli.

```bash
php artisan make:controller PaymentController
```

### ✅ Controller Lengkap (Copy-Paste):

```php
<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;
use App\Models\Booking;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
    }

    /**
     * GET /api/booking/{token}/payment-methods
     * Ambil daftar metode pembayaran yang AKTIF di Midtrans merchant Anda.
     */
    public function paymentMethods($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();

        // Pakai API Midtrans untuk cek channel AKTIF di akun Anda
        try {
            $channels = CoreApi::channels() ?? [];
        } catch (\Exception $e) {
            return response()->json(['methods' => [], 'error' => $e->getMessage()]);
        }

        $methods = collect($channels)->map(function ($ch) {
            if (!($ch['active'] ?? false)) return null;
            return [
                'code' => $ch['id'],
                'name' => $ch['name'],
                'category' => $this->mapCategory($ch['id']),
                'icon' => $this->extractIcon($ch['id']),
                'fee' => 0,
                'estimated_time' => $this->estimateTime($ch['id']),
            ];
        })->filter()->values();

        return response()->json(['methods' => $methods]);
    }

    /**
     * POST /api/booking/{token}/pay
     * ⚠️ WAJIB pakai Core API, BUKAN Snap!
     * 
     * Midtrans Core API return data pembayaran ASLI:
     * - VA → va_number di va_numbers[0]
     * - QRIS → actions: generate-qr-code
     * - E-Wallet → actions: deeplink-redirect
     * - Retail → payment_code
     * 
     * Response di-MAPPING ke format yang Flutter harapkan.
     * Jangan return snap_token — Flutter sudah TIDAK support Snap WebView!
     */
    public function pay(Request $request, $token)
    {
        $request->validate(['method' => 'required|string']);
        $booking = Booking::where('public_token', $token)->firstOrFail();

        // Parameter untuk Midtrans Core API
        $params = [
            'payment_type' => $this->convertToMidtransType($request->method),
            'transaction_details' => [
                'order_id' => $booking->booking_code . '-' . time(),
                'gross_amount' => (int) $booking->total_price,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'email' => $booking->customer_email ?? 'noreply@pixora.com',
                'phone' => $booking->customer_phone,
            ],
            'item_details' => [[
                'id' => 'BOOKING-' . $booking->id,
                'price' => (int) $booking->total_price,
                'quantity' => 1,
                'name' => 'Booking ' . ($booking->package->name ?? 'Pixora'),
            ]],
        ];

        // Tambahkan parameter spesifik sesuai metode
        if (str_contains($request->method, '_va')) {
            $params['bank_transfer'] = [
                'bank' => strtoupper(str_replace('_va', '', $request->method)),
            ];
        } elseif ($request->method === 'qris') {
            $params['qris'] = ['enable' => true];
        } elseif ($request->method === 'gopay') {
            $params['gopay'] = [];
        } elseif ($request->method === 'shopeepay') {
            $params['shopeepay'] = [];
        }

        try {
            // ✅ PAKAI CORE API, BUKAN SNAP!
            $charge = CoreApi::charge($params);

            // Simpan payment ke database
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'method' => $request->method,
                'transaction_id' => $charge->transaction_id,
                'amount' => $booking->total_price,
                'status' => 'pending',
                'raw_response' => json_encode($charge),
                'expired_at' => isset($charge->expiry_time)
                    ? \Carbon\Carbon::parse($charge->expiry_time)
                    : now()->addHours(24),
            ]);

            // ===== WAJIB: Mapping response Midtrans ke format Flutter =====
            return response()->json([
                'payment' => $this->formatPaymentForFlutter($charge, $payment, $booking)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal inisiasi pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/booking/{token}/status
     * Polling status dari Midtrans.
     */
    public function status($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();
        $payment = $booking->payment;

        if (!$payment || !$payment->transaction_id) {
            return response()->json([
                'payment_status' => 'pending',
                'payment_method' => null,
                'transaction_id' => null,
                'paid_at' => null,
            ]);
        }

        try {
            $status = Transaction::status($payment->transaction_id);

            $internalStatus = match ($status->transaction_status) {
                'capture', 'settlement' => 'lunas',
                'pending' => 'pending',
                'expire' => 'expired',
                'cancel', 'deny', 'failure' => 'cancelled',
                default => 'pending',
            };

            $payment->update([
                'status' => $internalStatus,
                'paid_at' => $internalStatus === 'lunas' ? now() : null,
            ]);

            // Update booking juga
            $booking->update(['payment_status' => $internalStatus]);
        } catch (\Exception $e) {
            // Tetap return data terakhir yang disimpan
        }

        return response()->json([
            'payment_status' => $payment->status,
            'payment_method' => $payment->method,
            'transaction_id' => $payment->transaction_id,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ]);
    }

    /**
     * POST /api/booking/{token}/confirm-payment
     * User tap "Saya Sudah Bayar" - paksa cek status ke Midtrans.
     */
    public function confirmPayment(Request $request, $token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();
        $payment = $booking->payment;

        if (!$payment || !$payment->transaction_id) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada transaksi',
            ], 400);
        }

        try {
            $status = Transaction::status($payment->transaction_id);

            $internalStatus = match ($status->transaction_status) {
                'capture', 'settlement' => 'lunas',
                'pending' => 'pending',
                'expire' => 'expired',
                'cancel', 'deny', 'failure' => 'cancelled',
                default => 'pending',
            };

            $payment->update([
                'status' => $internalStatus,
                'paid_at' => $internalStatus === 'lunas' ? now() : null,
            ]);
            $booking->update(['payment_status' => $internalStatus]);

            return response()->json([
                'success' => $internalStatus === 'lunas',
                'payment_status' => $internalStatus,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/midtrans/notification
     * Webhook dari Midtrans - WAJIB untuk auto-update status.
     */
    public function notification(Request $request)
    {
        try {
            $notif = new \Midtrans\Notification();

            $transactionStatus = $notif->transaction_status;
            $orderId = $notif->order_id;
            $transactionId = $notif->transaction_id;

            $payment = Payment::where('transaction_id', $transactionId)->first();
            if (!$payment) {
                return response()->json(['ok' => false, 'message' => 'Payment not found']);
            }

            $internalStatus = match ($transactionStatus) {
                'capture', 'settlement' => 'lunas',
                'pending' => 'pending',
                'expire' => 'expired',
                'cancel', 'deny', 'failure' => 'cancelled',
                default => 'pending',
            };

            $payment->update([
                'status' => $internalStatus,
                'paid_at' => $internalStatus === 'lunas' ? now() : null,
            ]);
            $payment->booking->update(['payment_status' => $internalStatus]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ====================================================================
    // ⚠️ INI YANG PALING PENTING: Mapping Response Midtrans → Flutter
    // ====================================================================
    // Flutter mengharapkan format JSON dengan field-field berikut:
    //
    // Virtual Account:
    //   - va_number: "8808000012345678" (WAJIB - ambil dari va_numbers[0])
    //   - bank_code: "BCA" (WAJIB)
    //
    // E-Wallet:
    //   - deeplink: "gojek://..." (WAJIB - ambil dari actions)
    //
    // QRIS:
    //   - qr_url: "https://api.midtrans.com/..." (WAJIB - ambil dari actions)
    //
    // Retail:
    //   - payment_code: "14780012345678" (WAJIB)
    //
    // ❌ JANGAN return snap_token - Flutter sudah TIDAK support!
    // ====================================================================

    private function formatPaymentForFlutter($charge, $payment, $booking)
    {
        $payload = [
            'booking_token' => $booking->public_token,
            'method_code' => $payment->method,
            'method_name' => $this->getMethodDisplayName($payment->method),
            'method_category' => $this->mapCategory($payment->method),
            'amount' => (float) $payment->amount,
            'fee' => 0,
            'status' => 'pending',
            'transaction_id' => $payment->transaction_id,
            'expired_at' => $payment->expired_at?->toIso8601String(),
        ];

        // ===== VIRTUAL ACCOUNT: ambil VA number dari response Midtrans =====
        if (isset($charge->va_numbers) && count($charge->va_numbers) > 0) {
            $va = $charge->va_numbers[0];
            $payload['va_number'] = $va->va_number;
            $payload['bank_code'] = strtoupper($va->bank);
        }

        // ===== QRIS: ambil QR code URL dari actions =====
        if (isset($charge->actions)) {
            foreach ($charge->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $payload['qr_url'] = $action->url;
                }
            }
        }

        // ===== E-WALLET: ambil deeplink dari actions =====
        if (isset($charge->actions)) {
            foreach ($charge->actions as $action) {
                if (in_array($action->name, ['deeplink-redirect', 'mobile-web-redirect'])) {
                    $payload['deeplink'] = $action->url;
                }
            }
        }

        // ===== RETAIL: ambil payment code =====
        if (isset($charge->payment_code)) {
            $payload['payment_code'] = $charge->payment_code;
        }

        return $payload;
    }

    private function getMethodDisplayName($method)
    {
        $names = [
            'bca_va' => 'BCA Virtual Account',
            'bni_va' => 'BNI Virtual Account',
            'bri_va' => 'BRI Virtual Account',
            'mandiri_va' => 'Mandiri Virtual Account',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            'qris' => 'QRIS',
            'indomaret' => 'Indomaret',
            'alfamart' => 'Alfamart',
        ];
        return $names[$method] ?? strtoupper($method);
    }

    // ============== HELPER METHODS ==============

    private function mapCategory($code)
    {
        if (str_contains($code, '_va')) return 'virtual_account';
        if (in_array($code, ['gopay', 'ovo', 'dana', 'shopeepay', 'linkaja'])) return 'ewallet';
        if ($code === 'qris') return 'qris';
        if (in_array($code, ['indomaret', 'alfamart'])) return 'retail';
        if ($code === 'credit_card') return 'credit_card';
        return 'other';
    }

    private function extractIcon($code)
    {
        if (str_contains($code, '_va')) return str_replace('_va', '', $code);
        return $code;
    }

    private function estimateTime($code)
    {
        if (in_array($code, ['gopay', 'shopeepay', 'qris'])) return 'Instan';
        if (str_contains($code, '_va')) return '1-3 menit';
        return '1-5 menit';
    }

    private function convertToMidtransType($method)
    {
        if (str_contains($method, '_va')) return 'bank_transfer';
        return $method;
    }
}
```

---

## 📄 STEP 3.5: Invoice Controller — Generate Data Invoice via API

Flutter sudah memiliki halaman invoice native (tidak perlu redirect ke browser). Backend hanya perlu menyediakan endpoint untuk mengambil detail booking yang sudah lunas.

### ✅ Endpoint

| Endpoint | Method | Fungsi |
|---|---|---|
| `GET /api/booking/{token}/invoice` | GET | Ambil data invoice booking (WAJIB return data booking + payment) |

Backend cukup return data booking dengan status `lunas` — Flutter akan render invoice secara native.

### ✅ Contoh Response

```json
{
  "booking": {
    "id": 1,
    "booking_code": "PXR-ABC12345",
    "customer_name": "John Doe",
    "customer_phone": "081234567890",
    "customer_email": "john@example.com",
    "booking_date": "2026-06-15",
    "time_slot": "morning",
    "total_price": 1500000.00,
    "down_payment": null,
    "payment_status": "lunas",
    "paid_at": "2026-06-15T10:30:00+07:00",
    "payment_method": "BCA Virtual Account",
    "package": { "name": "Paket Silver" }
  }
}
```

### ✅ Route

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/booking/{token}/invoice', [BookingController::class, 'invoice']);
});
```

> **Catatan**: Flutter **tidak perlu** endpoint ini untuk demo mode. Data invoice diambil dari `BookingProvider.currentBooking`. Endpoint ini hanya diperlukan jika `kEnableDemoMode = false`.

---

## 🛣️ STEP 4: Routes di `routes/api.php`

```php
<?php
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Pembayaran
    Route::get('/booking/{token}/payment-methods', [PaymentController::class, 'paymentMethods']);
    Route::post('/booking/{token}/pay', [PaymentController::class, 'pay']);
    Route::get('/booking/{token}/status', [PaymentController::class, 'status']);
    Route::post('/booking/{token}/confirm-payment', [PaymentController::class, 'confirmPayment']);

    // Webhook (JANGAN pakai auth:sanctum, karena dipanggil Midtrans)
});
// Webhook Midtrans (di luar middleware auth)
Route::post('/midtrans/notification', [PaymentController::class, 'notification']);
```

> ⚠️ Untuk development, jika Flutter Anda login, gunakan middleware `auth:sanctum`. Jika tidak, hapus middleware tersebut.

---

## 🧪 STEP 5: Test dengan Postman SEBELUM test di Flutter

```
POST http://localhost:8000/api/booking/{token}/pay
Content-Type: application/json
Authorization: Bearer {token_user}
Body: { "method": "bca_va" }
```

### ✅ Response yang HARUS muncul (untuk BCA VA):

```json
{
  "payment": {
    "booking_token": "abc123def456",
    "method_code": "bca_va",
    "method_name": "BCA Virtual Account",
    "method_category": "virtual_account",
    "amount": 1500000,
    "fee": 0,
    "status": "pending",
    "transaction_id": "9aed5974-5b40-4291-9001-7aaf0085725b",
    "expired_at": "2026-06-10T23:59:59+07:00",
    "va_number": "8808000012345678",
    "bank_code": "BCA"
  }
}
```

Ciri-ciri response BENAR:
- ✅ Ada `va_number` (nomor VA asli dari Midtrans)
- ✅ Ada `bank_code` (BCA/BNI/BRI/MANDIRI)
- ❌ TIDAK ada `snap_token` (karena Flutter tidak support)

### ✅ Response untuk QRIS:

```json
{
  "payment": {
    "booking_token": "abc123def456",
    "method_code": "qris",
    "method_name": "QRIS",
    "method_category": "qris",
    "amount": 1500000,
    "status": "pending",
    "transaction_id": "abc-123",
    "qr_url": "https://api.midtrans.com/v2/qris/abc-qr-code",
    "qr_string": "00020101021226670016..."
  }
}
```

Ciri-ciri response BENAR:
- ✅ Ada `qr_url` (URL gambar QR code dari Midtrans)
- ✅ Ada `qr_string` (string QRIS untuk di-copy)

### ✅ Response untuk GoPay (E-Wallet):

```json
{
  "payment": {
    "booking_token": "abc123def456",
    "method_code": "gopay",
    "method_name": "GoPay",
    "method_category": "ewallet",
    "amount": 1500000,
    "status": "pending",
    "transaction_id": "abc-123",
    "deeplink": "gojek://gopay/merchanttransfer?tref_id=abc"
  }
}
```

Ciri-ciri response BENAR:
- ✅ Ada `deeplink` (URL deeplink GoPay dari Midtrans)

### ✅ Response untuk Indomaret (Retail):

```json
{
  "payment": {
    "booking_token": "abc123def456",
    "method_code": "indomaret",
    "method_name": "Indomaret",
    "method_category": "retail",
    "amount": 1500000,
    "status": "pending",
    "transaction_id": "abc-123",
    "payment_code": "14780012345678"
  }
}
```

Ciri-ciri response BENAR:
- ✅ Ada `payment_code` (kode bayar dari Midtrans)

### ❌ Response SALAH (Jangan seperti ini!):

```json
{
  "success": true,
  "payment": {
    "method_code": "bca_va",
    "amount": 1500000
    // ⚠️ TIDAK ADA va_number → Flutter akan error!
  }
}
```

---

## 🧪 Test di Midtrans Simulator

1. Buka: https://simulator.sandbox.midtrans.com/
2. Pilih metode yang sesuai dengan payment Anda (misal "BCA Virtual Account")
3. **Paste VA number** yang muncul di Flutter (JANGAN generate sendiri!)
4. Klik "Pay" → harus sukses
5. Setelah sukses, polling Flutter akan mendeteksi status "lunas"

---

## ⚠️ PENTING: Jangan Generate VA Sendiri!

❌ **SALAH**:
```php
// JANGAN PERNAH LAKUKAN INI — VA number harus dari Midtrans!
$va_number = '8808' . rand(1000000, 9999999);
```

✅ **BENAR**:
```php
// AMBIL DARI RESPONSE MIDTRANS CORE API
$va_number = $charge->va_numbers[0]->va_number;
```

Jika Anda generate sendiri, Midtrans simulator akan menolak dengan error:
```
Virtual account number not found / incorrect
```

---

## ⚡ Perbedaan Core API vs Snap (Kenapa Harus Core API?)

| Aspek | Core API (`CoreApi::charge()`) ✅ | Snap (`Snap::createTransaction()`) ❌ |
|---|---|---|
| Return VA number | ✅ Langsung di response | ❌ Return snap_token |
| Return QR code | ✅ Langsung di response | ❌ Return snap_token |
| Return deeplink | ✅ Langsung di response | ❌ Return snap_token |
| Perlu buka browser? | ❌ Tidak | ✅ Ya (Snap WebView) |
| UX di Flutter | ✅ Native (lebih baik) | ❌ Buka halaman web |
| Didukung Flutter? | ✅ **WAJIB** | ❌ **SUDAH DIHAPUS** |

---

## 🐛 Troubleshooting

### ❌ "Metode tidak tersedia" di Flutter
**Penyebab**: Endpoint `GET /api/booking/{token}/payment-methods` belum dibuat atau return array kosong.
**Fix**: Buat endpoint-nya. Response HARUS:
```json
{ "methods": [...] }
```
BUKAN `[{...}]` (langsung array tanpa wrapper).

### ❌ "Gagal memproses pembayaran" setelah pilih metode
**Penyebab**: Endpoint `POST /api/booking/{token}/pay` return error atau response tidak memiliki data pembayaran yang valid.
**Fix**:
1. Cek log Laravel: `storage/logs/laravel.log`
2. Test endpoint dengan Postman
3. Pastikan response mengandung `va_number` (untuk VA), `qr_url` (untuk QRIS), `deeplink` (untuk e-wallet), atau `payment_code` (untuk retail)
4. Pastikan Anda pakai `CoreApi::charge()` BUKAN `Snap::createTransaction()`

### ❌ "Virtual account number not found" di Simulator Midtrans
**Penyebab**: VA number di-generate manual di backend, bukan dari Midtrans.
**Fix**: Pastikan `$charge->va_numbers[0]->va_number` di-return ke Flutter. Cek response Midtrans di `raw_response` di DB.

### ❌ Status tidak berubah setelah bayar di simulator
**Penyebab**: Webhook `POST /api/midtrans/notification` belum jalan.
**Fix**:
1. Pastikan route webhook terdaftar
2. Untuk development lokal, gunakan ngrok: `ngrok http 8000`
3. Set `MIDTRANS_NOTIFICATION_URL` di .env ke URL ngrok

### ❌ Connection refused / timeout
**Penyebab**: Server Laravel tidak jalan atau `baseUrl` di Flutter salah.
**Fix**:
1. Jalankan `php artisan serve --host=0.0.0.0 --port=8000`
2. Cek `lib/config/api_config.dart` → `baseUrl`:
   - Android emulator: `http://10.0.2.2:8000`
   - iOS simulator: `http://localhost:8000`
   - Device fisik: `http://<IP_KOMPUTER>:8000`

---

## 📋 Checklist WAJIB Backend

### ✅ Checklist Booking (WAJIB SEBELUM PAYMENT)

- [ ] Buat migration tabel `bookings` (copy dari STEP 0)
- [ ] Buat model `Booking` dengan relasi ke `User` dan `Package`
- [ ] Buat `BookingController` dengan method `store()` dan `myBookings()`
- [ ] Implementasi `POST /api/booking/store` — simpan booking ke database
- [ ] Implementasi `GET /api/booking/my` — ambil riwayat booking user login (WAJIB filter by user_id)
- [ ] Daftarkan routes booking di `routes/api.php` (di dalam middleware `auth:sanctum`)

### ✅ Checklist Payment & Midtrans

- [ ] `composer require midtrans/midtrans-php`
- [ ] Set `MIDTRANS_SERVER_KEY` & `MIDTRANS_CLIENT_KEY` di `.env` (Sandbox)
- [ ] Konfigurasi `Config::$serverKey` di `AppServiceProvider`
- [ ] Buat migration tabel `payments`
- [ ] Buat model `Payment` dengan relasi ke `Booking`
- [ ] Buat controller `PaymentController` dengan 5 method di atas
- [ ] **WAJIB**: Pakai `CoreApi::charge()` — JANGAN GUNAKAN `Snap::createTransaction()`
- [ ] **WAJIB**: Mapping response dengan method `formatPaymentForFlutter()`
- [ ] **WAJIB**: Extract `va_number` dari `$charge->va_numbers[0]` (untuk VA)
- [ ] **WAJIB**: Extract `qr_url` dari `$charge->actions` (untuk QRIS)
- [ ] **WAJIB**: Extract `deeplink` dari `$charge->actions` (untuk E-Wallet)
- [ ] **WAJIB**: Extract `payment_code` dari `$charge->payment_code` (untuk Retail)
- [ ] Test dengan Postman → response harus berisi data pembayaran asli
- [ ] Test di Midtrans simulator → VA/QR harus diterima
- [ ] Implementasi webhook `/api/midtrans/notification`
- [ ] Daftarkan routes payment di `routes/api.php`


### ✅ Checklist Flutter (Setelah Backend Siap)

- [ ] Set `kEnableDemoMode = false` di `lib/providers/booking_provider.dart`
- [ ] Restart aplikasi Flutter → semua data dari backend
- [ ] Test booking → harus muncul di riwayat ✅
- [ ] Test bayar → status terupdate dari webhook Midtrans ✅

---

## 🔄 Alur Lengkap (End-to-End)

```
[Flutter] User pilih "BCA VA" dari daftar metode
  ↓
[Flutter] POST /api/booking/{token}/pay {method: "bca_va"}
  ↓
[Laravel] Midtrans::CoreApi::charge() dengan payment_type=bank_transfer, bank=bca
  ↓
[Midtrans] Return: transaction_id + va_numbers[0].va_number + expiry_time
  ↓
[Laravel] formatPaymentForFlutter() → mapping response → return ke Flutter
  ↓
[Flutter] TAMPILKAN VA NUMBER ASLI + tombol copy + cara bayar step-by-step
  ↓         (SEMUA DI APP, TANPA BUKA BROWSER!)
[User] Buka Midtrans simulator, paste VA number, klik "Pay"
  ↓
[Midtrans] Kirim webhook ke Laravel: POST /api/midtrans/notification
  ↓
[Laravel] Update tabel payments + bookings → status jadi "lunas"
  ↓
[Flutter] Polling GET /api/booking/{token}/status → dapat "lunas"
  ↓
[Flutter] Tampilkan sukses, navigasi ke MainScreen ✅
```

---

## 💡 Tips untuk Testing

1. **Test endpoint `/pay` dulu dengan Postman** — sebelum test di Flutter
2. **Pastikan VA number muncul** di response Postman
3. **Copy VA number** dari response Postman
4. **Buka Midtrans simulator** di browser: https://simulator.sandbox.midtrans.com/
5. **Paste VA number** di simulator, klik Pay
6. **Cek polling** di Flutter atau panggil `GET /status` manual
7. Jika semua berhasil → Anda siap production!

---

## 🔐 STEP 6: Google Sign-In / Register (Laravel Backend)

Aplikasi Flutter sudah mendukung **Login & Register via Google** menggunakan `google_sign_in` package. Backend Laravel perlu membuat endpoint untuk memverifikasi token Google dan membuat/mengambil user.

### 📱 Flow di Flutter

```
[Flutter] User tap "Masuk dengan Google" / "Daftar dengan Google"
  ↓
[Flutter] Google Sign-In → dapat id_token + access_token
  ↓
[Flutter] POST /api/login/google { id_token, access_token, email, name, photo_url }
  ↓
[Laravel] Verifikasi token Google → cek apakah email sudah terdaftar
  ├─ Sudah ada → login user, return token + data user
  └─ Belum ada → buat user baru (register), return token + data user
  ↓
[Flutter] Simpan token + user data ke SharedPreferences
  ↓
[Flutter] Navigasi ke MainScreen ✅
```

### ✅ Endpoint yang Dibutuhkan

| Endpoint | Method | Fungsi |
|---|---|---|
| `POST /api/login/google` | POST | Login/Register via Google — terima token Google, return token Sanctum + user data |

### ✅ Install Package Laravel (Opsional, untuk verifikasi ID Token)

```bash
composer require google/apiclient
```

### ✅ Google OAuth Console Setup

1. Buka https://console.cloud.google.com/
2. Buat Project baru (atau pakai yang sudah ada)
3. Aktifkan **Google+ API** dan **Google People API**
4. Buka **Credentials** → **Create Credentials** → **OAuth 2.0 Client ID**
5. Isi:
   - Application type: **Web application** atau **Android** (untuk Android, perlu SHA-1 fingerprint)
   - Authorized redirect URIs: `https://yourdomain.com/auth/google/callback`
6. Copy **Client ID** → simpan di `.env` Laravel dan di Flutter

### ✅ Tambahkan Environment Variable di `.env`

```env
GOOGLE_CLIENT_ID=your-google-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-google-client-secret
```

### ✅ Migration: Tambahkan Kolom Google di Tabel Users

```bash
php artisan make:migration add_google_columns_to_users_table
```

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('google_id')->nullable()->unique()->after('email');
    $table->string('avatar')->nullable()->after('google_id');
    $table->enum('auth_provider', ['manual', 'google'])->default('manual')->after('avatar');
});
```

### ✅ AuthController — Method `loginWithGoogle`

```php
<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /api/login/google
     * Login atau Register via Google Sign-In.
     *
     * Flutter mengirim:
     * - id_token: Google ID Token (untuk verifikasi)
     * - access_token: Google Access Token
     * - email: Email dari Google
     * - name: Nama dari Google
     * - photo_url: Foto profil dari Google
     *
     * Backend:
     * 1. (Opsional) Verifikasi id_token dengan Google API
     * 2. Cari user berdasarkan email atau google_id
     * 3. Jika belum ada → buat user baru
     * 4. Return token Sanctum + data user
     */
    public function loginWithGoogle(Request $request)
    {
        $request->validate([
            'id_token' => 'nullable|string',
            'access_token' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string|max:255',
            'photo_url' => 'nullable|string',
        ]);

        // ===== OPSIONAL: Verifikasi ID Token =====
        // Untuk production, verifikasi id_token dengan Google API:
        //
        // $client = new \Google\Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
        // $payload = $client->verifyIdToken($request->id_token);
        // if (!$payload) {
        //     return response()->json(['message' => 'Token Google tidak valid'], 401);
        // }
        //
        // Untuk development, kita percaya data dari Flutter.

        $email = $request->email;
        $googleId = $request->id_token ?? $request->access_token;

        // Cari user berdasarkan email atau google_id
        $user = User::where('email', $email)
            ->orWhere('google_id', $googleId)
            ->first();

        if ($user) {
            // User sudah ada → update google_id jika belum ada
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleId,
                    'auth_provider' => 'google',
                    'avatar' => $request->photo_url ?? $user->avatar,
                ]);
            }
        } else {
            // User belum ada → buat baru (auto-register via Google)
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $request->photo_url,
                'auth_provider' => 'google',
                'password' => null, // Tidak perlu password untuk Google auth
                'phone' => null,
            ]);
        }

        // Generate Sanctum token
        $token = $user->createToken('pixora-mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'auth_provider' => $user->auth_provider,
            ],
        ]);
    }

    // ... method login, register, logout yang sudah ada
}
```

### ✅ Route

```php
// routes/api.php

// Public routes (tanpa auth)
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login/google', [AuthController::class, 'loginWithGoogle']); // ← TAMBAHKAN INI

// Protected routes (perlu auth)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    // ... routes lainnya
});
```

### ✅ User Model — Update Fillable

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'google_id',     // ← TAMBAHKAN
        'avatar',        // ← TAMBAHKAN
        'auth_provider', // ← TAMBAHKAN
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

### 📱 Konfigurasi di Flutter (Android)

Untuk Android, perlu konfigurasi SHA-1 fingerprint agar Google Sign-In bekerja:

1. Dapatkan SHA-1 fingerprint:
```bash
# Debug key
keytool -list -v -keystore ~/.android/debug.keystore -alias androiddebugkey -storepass android -keypass android

# Release key
keytool -list -v -keystore ~/your-release-key.keystore -alias your-alias
```

2. Buka Firebase Console → Project Settings → Android app
3. Tambahkan SHA-1 fingerprint
4. Download `google-services.json` → taruh di `android/app/`

### 📱 Konfigurasi di Flutter (iOS)

1. Buka Google Cloud Console → Credentials → iOS OAuth 2.0 Client ID
2. Bundle ID harus cocok dengan `ios/Runner.xcodeproj` (biasanya `com.example.pixoraMobile`)
3. Download `GoogleService-Info.plist` → taruh di `ios/Runner/`
4. Tambahkan di `ios/Runner/Info.plist`:

```xml
<key>CFBundleURLTypes</key>
<array>
  <dict>
    <key>CFBundleTypeRole</key>
    <string>Editor</string>
    <key>CFBundleURLSchemes</key>
    <array>
      <!-- Ganti dengan YOUR_REVERSED_CLIENT_ID dari GoogleService-Info.plist -->
      <string>com.googleusercontent.apps.YOUR-CLIENT-ID</string>
    </array>
  </dict>
</array>
```

### ⚠️ Catatan Penting

1. **Password bisa null** untuk user Google — pastikan login manual mengecek `auth_provider` sebelum meminta password
2. **Email unik** — Google selalu return email unik, tidak ada duplikasi
3. **Verifikasi ID Token** — Untuk production, WAJIB verifikasi `id_token` dengan Google API. Untuk development, cukup percaya data dari Flutter.
4. **Login/Register Gabungan** — Endpoint yang sama (`POST /api/login/google`) menangani login DAN register. Jika email belum ada → auto-register. Jika sudah ada → login.

---

## 📋 Checklist Google Auth

### ✅ Backend Laravel

- [ ] Install `google/apiclient` (opsional untuk verifikasi token)
- [ ] Set `GOOGLE_CLIENT_ID` & `GOOGLE_CLIENT_SECRET` di `.env`
- [ ] Buat migration tambah kolom `google_id`, `avatar`, `auth_provider` di tabel users
- [ ] Jalankan `php artisan migrate`
- [ ] Buat endpoint `POST /api/login/google` di AuthController
- [ ] Update User model: tambah `google_id`, `avatar`, `auth_provider` ke fillable
- [ ] Daftarkan route `POST /api/login/google` (public, tanpa auth middleware)
- [ ] Test dengan Postman → kirim `{ "email": "...", "name": "...", "access_token": "..." }`

### ✅ Flutter

- [ ] Install `google_sign_in` package (sudah di pubspec.yaml)
- [ ] Untuk Android: konfigurasi SHA-1 fingerprint di Firebase
- [ ] Untuk iOS: tambah URL scheme di Info.plist
- [ ] Login screen: tombol "Masuk dengan Google" sudah aktif ✅
- [ ] Register screen: tombol "Daftar dengan Google" sudah aktif ✅

### ✅ Contoh Response yang Diharapkan

```json
{
  "token": "1|abc123def456...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@gmail.com",
    "phone": null,
    "avatar": "https://lh3.googleusercontent.com/...",
    "auth_provider": "google"
  }
}
```
