<?php
// app/Services/MidtransService.php

namespace App\Services;

use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;
use Midtrans\Transaction;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MidtransService
{
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Konfigurasi Midtrans
     *
     * PENTING: jangan replace Config::$curlOptions seluruhnya
     * karena SDK mengakses Config::$curlOptions[CURLOPT_HTTPHEADER]
     * (yang mana konstantanya = 10023). Kalau kita timpa dengan array
     * baru yang tidak punya key 10023, SDK akan throw
     * "Undefined array key 10023" → 500 error.
     * Solusinya: MODIFY key individual, bukan replace.
     */
    protected function configure()
    {
        Config::$serverKey    = config('midtrans.server_key');
        Config::$clientKey    = config('midtrans.client_key');
        Config::$isProduction = (bool) config('midtrans.is_production', false);
        Config::$isSanitized  = (bool) config('midtrans.is_sanitized', true);
        Config::$is3ds        = (bool) config('midtrans.is_3ds', true);

        // SSL verification: disable di environment lokal/sandbox supaya tidak
        // gagal handshake certificate, tapi JANGAN replace seluruh array.
        if (app()->environment('local', 'testing', 'development') || !Config::$isProduction) {
            Config::$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
            Config::$curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
        }

        // Pastikan CURLOPT_HTTPHEADER selalu ada (untuk mencegah warning
        // SDK saat request pertama). Kosongkan / tambahkan header tambahan
        // sesuai kebutuhan di sini.
        if (!isset(Config::$curlOptions[CURLOPT_HTTPHEADER])
            || !is_array(Config::$curlOptions[CURLOPT_HTTPHEADER])) {
            Config::$curlOptions[CURLOPT_HTTPHEADER] = [];
        }

        Log::info('Midtrans Config Check', [
            'server_key_exists' => !empty(Config::$serverKey),
            'server_key_preview' => substr(Config::$serverKey ?? '', 0, 10) . '...',
            'is_production'      => Config::$isProduction,
            'is_sanitized'       => Config::$isSanitized,
            'is_3ds'             => Config::$is3ds,
        ]);
    }

    /**
     * Membuat Snap Token untuk transaksi
     */
    public function createSnapToken(Booking $booking, $amount)
    {
        Log::info('Creating Midtrans Snap Token', [
            'booking_id' => $booking->id,
            'amount' => $amount
        ]);


        // Validasi Server Key
        if (empty(config('midtrans.server_key'))) {
            throw new \Exception('MIDTRANS_SERVER_KEY tidak ditemukan di .env');
        }

        $now = Carbon::now('Asia/Jakarta');

        $orderId = 'PIX-' . $booking->id . '-' . $now->timestamp . '-' . rand(100, 999);

        $grossAmount = max((int) $amount, 10000);

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name ?? 'Test Customer',
                'phone' => $booking->customer_phone ?? '081234567890',
                'email' => $booking->customer_email ?? 'test@example.com',
            ],
            'item_details' => [
                [
                    'id' => 'PKG-' . $booking->package_id,
                    'price' => $grossAmount,
                    'quantity' => 1,
                    'name' => substr($booking->package->name ?? 'Photo Package', 0, 50),
                ]
            ],
            // Tambahkan credit_card untuk testing
            'credit_card' => [
                'secure' => true,
            ]
        ];

        try {
            // Panggil API Midtrans
            $snapResponse = Snap::createTransaction($payload);

            // Validasi response
            if (!isset($snapResponse->token)) {
                throw new \Exception('Snap token tidak ditemukan dalam response Midtrans');
            }

            // Simpan ke database
            $booking->snap_token = $snapResponse->token;
            $booking->midtrans_order_id = $orderId;
            $booking->midtrans_response = json_encode($snapResponse);
            $booking->payment_expiry = now()->addMinutes(30);
            $booking->save();

            Log::info('Snap token created successfully', [
                'token' => $snapResponse->token,
                'redirect_url' => $snapResponse->redirect_url
            ]);

            return $snapResponse->token;
        } catch (\Exception $e) {
            Log::error('Midtrans Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw new \Exception('Gagal membuat token pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Mendapatkan status transaksi dari Midtrans
     */
    public function getTransactionStatus($orderId)
    {
        try {
            $status = Transaction::status($orderId);

            // Response bisa berupa object atau array, kita konversi ke object
            $statusObject = is_array($status) ? (object) $status : $status;

            Log::info('Midtrans Status Check', [
                'order_id' => $orderId,
                'status' => $statusObject->transaction_status ?? 'unknown'
            ]);

            return $statusObject;
        } catch (\Exception $e) {
            Log::error('Midtrans Status Check Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Mendapatkan daftar channel/metode pembayaran aktif dari Midtrans.
     * Method ini yang dipakai endpoint /payment-methods di mobile.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getActivePaymentChannels(): array
    {
        // Fallback statis jika API VT::getChannels() tidak tersedia
        // atau sandbox Midtrans mengembalikan daftar minimal.
        $fallback = [
            ['id' => 'credit_card',  'name' => 'Kartu Kredit',         'category' => 'credit_card',   'icon' => 'credit_card',  'fee' => 0, 'estimated_time' => 'Instan'],
            ['id' => 'bca_va',       'name' => 'BCA Virtual Account',  'category' => 'virtual_account','icon' => 'bca',          'fee' => 0, 'estimated_time' => '1-3 menit'],
            ['id' => 'bni_va',       'name' => 'BNI Virtual Account',  'category' => 'virtual_account','icon' => 'bni',          'fee' => 0, 'estimated_time' => '1-3 menit'],
            ['id' => 'bri_va',       'name' => 'BRI Virtual Account',  'category' => 'virtual_account','icon' => 'bri',          'fee' => 0, 'estimated_time' => '1-3 menit'],
            ['id' => 'mandiri_va',   'name' => 'Mandiri Virtual Account','category' => 'virtual_account','icon' => 'mandiri',   'fee' => 0, 'estimated_time' => '1-3 menit'],
            ['id' => 'permata_va',   'name' => 'Permata Virtual Account','category' => 'virtual_account','icon' => 'permata',  'fee' => 0, 'estimated_time' => '1-3 menit'],
            ['id' => 'gopay',        'name' => 'GoPay',                 'category' => 'ewallet',       'icon' => 'gopay',        'fee' => 0, 'estimated_time' => 'Instan'],
            ['id' => 'shopeepay',    'name' => 'ShopeePay',             'category' => 'ewallet',       'icon' => 'shopeepay',    'fee' => 0, 'estimated_time' => 'Instan'],
            ['id' => 'qris',         'name' => 'QRIS',                  'category' => 'qris',          'icon' => 'qris',         'fee' => 0, 'estimated_time' => 'Instan'],
            ['id' => 'indomaret',    'name' => 'Indomaret',             'category' => 'retail',        'icon' => 'indomaret',    'fee' => 0, 'estimated_time' => '1-3 menit'],
            ['id' => 'alfamart',     'name' => 'Alfamart',              'category' => 'retail',        'icon' => 'alfamart',     'fee' => 0, 'estimated_time' => '1-3 menit'],
        ];

        try {
            if (class_exists('\\Midtrans\\VT')) {
                $channels = \Midtrans\VT::getChannels();
                if (is_array($channels) && count($channels) > 0) {
                    $mapped = [];
                    foreach ($channels as $ch) {
                        $id = $ch['id'] ?? $ch->id ?? null;
                        if (!$id) continue;
                        $mapped[] = [
                            'id'              => $id,
                            'name'            => $ch['name'] ?? $ch->name ?? $id,
                            'category'        => $this->mapMethodCategory($id),
                            'icon'            => $id,
                            'fee'             => 0,
                            'estimated_time'  => $this->getEstimatedTime($id),
                        ];
                    }
                    if (count($mapped) > 0) {
                        return $mapped;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Midtrans VT::getChannels error, using fallback: ' . $e->getMessage());
        }

        return $fallback;
    }

    /**
     * Mapping kode metode Midtrans ke kategori yang dipakai Flutter.
     */
    public function mapMethodCategory(string $code): string
    {
        $code = strtolower($code);
        if (str_contains($code, '_va')) return 'virtual_account';
        if (in_array($code, ['gopay', 'ovo', 'dana', 'shopeepay', 'linkaja'])) return 'ewallet';
        if ($code === 'qris' || $code === 'other_qris') return 'qris';
        if (in_array($code, ['indomaret', 'alfamart'])) return 'retail';
        if ($code === 'credit_card') return 'credit_card';
        if (str_contains($code, 'bank_transfer') || str_contains($code, 'echannel')) return 'bank_transfer';
        return 'other';
    }

    /**
     * Estimasi waktu pembayaran untuk masing-masing channel.
     */
    public function getEstimatedTime(string $code): string
    {
        $code = strtolower($code);
        if (in_array($code, ['gopay', 'shopeepay', 'qris', 'credit_card', 'ovo', 'dana', 'linkaja'])) {
            return 'Instan';
        }
        if (str_contains($code, '_va') || $code === 'echannel') {
            return '1-3 menit';
        }
        if (in_array($code, ['indomaret', 'alfamart'])) {
            return '1-3 menit';
        }
        return '-';
    }

    /**
     * Mapping kode metode ke label Indonesia yang mudah dibaca user.
     */
    public function getReadablePaymentMethod(?string $code): ?string
    {
        if (!$code) return null;
        $map = [
            'credit_card'  => 'Kartu Kredit',
            'bank_transfer'=> 'Transfer Bank',
            'echannel'     => 'Mandiri Bill',
            'bca_va'       => 'BCA Virtual Account',
            'bni_va'       => 'BNI Virtual Account',
            'bri_va'       => 'BRI Virtual Account',
            'mandiri_va'   => 'Mandiri Virtual Account',
            'permata_va'   => 'Permata Virtual Account',
            'cimb_va'      => 'CIMB Virtual Account',
            'bsi_va'       => 'BSI Virtual Account',
            'gopay'        => 'GoPay',
            'shopeepay'    => 'ShopeePay',
            'qris'         => 'QRIS',
            'other_qris'   => 'QRIS',
            'alfamart'     => 'Alfamart',
            'indomaret'    => 'Indomaret',
        ];
        return $map[$code] ?? ucfirst(str_replace('_', ' ', $code));
    }

    /**
     * Map status transaksi Midtrans ke status internal Pixora.
     */
    public function mapInternalStatus(string $midtransStatus, bool $isFullPayment = true): string
    {
        switch ($midtransStatus) {
            case 'capture':
            case 'settlement':
                return $isFullPayment ? 'lunas' : 'dp_paid';
            case 'pending':
                return 'pending';
            case 'expire':
                return 'expired';
            case 'cancel':
            case 'deny':
                return 'cancelled';
            case 'refund':
            case 'partial_refund':
                return 'cancelled';
            default:
                return 'pending';
        }
    }

    /**
     * CHARGE VIA CORE API (WAJIB untuk Flutter native payment)
     *
     * ⚠️ PENTING: Flutter sudah menghapus Snap WebView.
     * Semua pembayaran WAJIB menggunakan Core API agar response
     * berisi data pembayaran asli (VA number, QR URL, deeplink, payment code).
     *
     * @param Booking $booking
     * @param float $amount
     * @param string $method Kode metode pembayaran (bca_va, gopay, qris, dll)
     * @return object Response dari Midtrans Core API
     * @throws \Exception
     */
    public function createCoreApiCharge(Booking $booking, float $amount, string $method): object
    {
        $now = Carbon::now('Asia/Jakarta');
        $orderId = 'PIX-' . $booking->id . '-' . $now->timestamp . '-' . rand(100, 999);
        $grossAmount = max((int) $amount, 10000);

        $params = [
            'payment_type' => $this->convertToMidtransPaymentType($method),
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name ?? 'Customer',
                'phone' => $booking->customer_phone ?? '081234567890',
                'email' => $booking->customer_email ?? 'noreply@pixora.com',
            ],
            'item_details' => [[
                'id' => 'PKG-' . $booking->package_id,
                'price' => $grossAmount,
                'quantity' => 1,
                'name' => substr($booking->package->name ?? 'Pixora Package', 0, 50),
            ]],
            'custom_field1' => 'public_token:' . $booking->public_token,
            'custom_field2' => 'method:' . $method,
            'custom_field3' => 'platform:mobile',
        ];

        // Tambahkan parameter spesifik berdasarkan metode
        if (str_contains($method, '_va')) {
            $bank = strtoupper(str_replace('_va', '', $method));
            if ($bank === 'MANDIRI' || $bank === 'PERMATA') {
                // Permata & Mandiri tidak pakai bank_transfer spesifik bank
                if ($bank === 'MANDIRI') {
                    $params['payment_type'] = 'echannel';
                    $params['echannel'] = [
                        'bill_info1' => 'Pembayaran Pixora',
                        'bill_info2' => 'Booking #' . $booking->booking_code,
                    ];
                }
                // Permata tidak perlu parameter tambahan
            } else {
                $params['bank_transfer'] = [
                    'bank' => $bank,
                ];
            }
        } elseif ($method === 'qris') {
            $params['qris'] = ['enable' => true];
        } elseif ($method === 'gopay') {
            $params['gopay'] = [];
        } elseif ($method === 'shopeepay') {
            $params['shopeepay'] = [];
        } elseif ($method === 'indomaret' || $method === 'alfamart') {
            $params['cstore'] = [
                'store' => $method === 'indomaret' ? 'Indomaret' : 'Alfamart',
                'message' => 'Pembayaran Pixora',
            ];
        }

        Log::info('CoreApi charge initiated', [
            'order_id' => $orderId,
            'method' => $method,
            'amount' => $grossAmount,
            'payment_type' => $params['payment_type'],
        ]);

        try {
            $charge = CoreApi::charge($params);

            Log::info('CoreApi charge success', [
                'order_id' => $orderId,
                'transaction_id' => $charge->transaction_id ?? null,
                'status' => $charge->transaction_status ?? null,
            ]);

            // Simpan order_id ke booking untuk tracking nanti
            $booking->midtrans_order_id = $orderId;
            $booking->midtrans_response = json_encode($charge);
            $booking->save();

            return $charge;
        } catch (\Exception $e) {
            Log::error('CoreApi charge error: ' . $e->getMessage());
            throw new \Exception('Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Convert kode metode internal ke payment_type Midtrans
     */
    private function convertToMidtransPaymentType(string $method): string
    {
        if (str_contains($method, '_va')) return 'bank_transfer';
        if ($method === 'indomaret' || $method === 'alfamart') return 'cstore';
        if ($method === 'credit_card') return 'credit_card';
        return $method;
    }

    /**
     * Format response Core API ke format yang Flutter harapkan.
     *
     * Flutter tidak bisa pakai snap_token, jadi kita harus mapping
     * data pembayaran asli dari Core API response.
     */
    public function formatCoreApiResponse($charge, $booking, string $method): array
    {
        $amount = $booking->down_payment > 0 ? $booking->down_payment : $booking->total_price;

        $payload = [
            'booking_token'   => $booking->public_token,
            'method_code'     => $method,
            'method_name'     => $this->getReadablePaymentMethod($method),
            'method_category' => $this->mapMethodCategory($method),
            'amount'          => (float) $amount,
            'fee'             => 0,
            'status'          => 'pending',
            'transaction_id'  => $charge->transaction_id ?? $booking->midtrans_order_id,
            'expired_at'      => isset($charge->expiry_time)
                ? Carbon::parse($charge->expiry_time)->toIso8601String()
                : now()->addHours(24)->toIso8601String(),
        ];

        // ===== VIRTUAL ACCOUNT: ambil VA number dari response Midtrans =====
        if (isset($charge->va_numbers) && count($charge->va_numbers) > 0) {
            $va = $charge->va_numbers[0];
            $payload['va_number'] = $va->va_number;
            $payload['bank_code'] = strtoupper($va->bank);
        }

        // Permata VA punya format berbeda
        if (isset($charge->permata_va_number)) {
            $payload['va_number'] = $charge->permata_va_number;
            $payload['bank_code'] = 'PERMATA';
        }

        // Mandiri echannel
        if (isset($charge->bill_key)) {
            $payload['va_number'] = $charge->bill_key;
            $payload['bank_code'] = 'MANDIRI';
        }

        // ===== QRIS: ambil QR code URL dari actions =====
        if (isset($charge->actions)) {
            foreach ($charge->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $payload['qr_url'] = $action->url;
                }
                if (in_array($action->name, ['deeplink-redirect', 'mobile-web-redirect'])) {
                    $payload['deeplink'] = $action->url;
                }
            }
        }

        // ===== RETAIL: ambil payment code =====
        if (isset($charge->payment_code)) {
            $payload['payment_code'] = $charge->payment_code;
        }

        // ===== QRIS string =====
        if (isset($charge->qr_string)) {
            $payload['qr_string'] = $charge->qr_string;
        }

        return $payload;
    }

    /**
     * VALIDASI SIGNATURE KEY DARI CALLBACK MIDTRANS
     * Method ini penting untuk verifikasi webhook dari Midtrans
     */
    public function validateSignature($orderId, $statusCode, $grossAmount, $signatureKey)
    {
        $serverKey = config('midtrans.server_key');
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($signature, $signatureKey);
    }

    /**
     * MENDAPATKAN NOTIFIKASI DARI MIDTRANS
     * Update status booking berdasarkan callback
     */
    public function handleNotification($payload)
    {
        try {
            $orderId = $payload['order_id'] ?? null;
            $transactionStatus = $payload['transaction_status'] ?? null;
            $paymentType = $payload['payment_type'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;

            if (!$orderId) {
                Log::warning('Midtrans notification: No order_id found');
                return false;
            }

            // Cari booking berdasarkan midtrans_order_id
            $booking = Booking::where('midtrans_order_id', $orderId)->first();

            if (!$booking) {
                Log::warning('Booking not found for order_id: ' . $orderId);
                return false;
            }

            // Update payment_details
            $paymentDetails = $booking->payment_details ?? [];
            $paymentDetails['midtrans_callback'] = $payload;
            $booking->payment_details = $paymentDetails;

            // Mapping payment type
            $paymentMethodMap = [
                'credit_card' => 'Kartu Kredit',
                'bank_transfer' => 'Transfer Bank',
                'bca_va' => 'BCA Virtual Account',
                'bni_va' => 'BNI Virtual Account',
                'bri_va' => 'BRI Virtual Account',
                'mandiri_va' => 'Mandiri Virtual Account',
                'permata_va' => 'Permata Virtual Account',
                'gopay' => 'GoPay',
                'shopeepay' => 'ShopeePay',
                'qris' => 'QRIS',
                'other_qris' => 'QRIS',
                'alfamart' => 'Alfamart',
                'indomaret' => 'Indomaret'
            ];

            $readablePaymentMethod = $paymentMethodMap[$paymentType] ?? ucfirst(str_replace('_', ' ', $paymentType));

            // Proses status berdasarkan transaction_status
            switch ($transactionStatus) {
                case 'capture':
                    if ($fraudStatus == 'accept') {
                        $this->updatePaymentSuccess($booking, $payload, $readablePaymentMethod);
                    }
                    break;

                case 'settlement':
                    $this->updatePaymentSuccess($booking, $payload, $readablePaymentMethod);
                    break;

                case 'pending':
                    $this->updatePaymentPending($booking, $payload, $readablePaymentMethod);
                    break;

                case 'deny':
                case 'cancel':
                case 'expire':
                    $this->updatePaymentFailed($booking, $payload, $transactionStatus);
                    break;

                case 'refund':
                case 'partial_refund':
                    $this->updatePaymentRefunded($booking, $payload);
                    break;

                default:
                    Log::info('Unhandled transaction status: ' . $transactionStatus, ['order_id' => $orderId]);
                    break;
            }

            $booking->save();

            // Sync Payment model jika ada
            try {
                $payment = Payment::where('transaction_id', $payload['transaction_id'] ?? null)
                    ->orWhere('booking_id', $booking->id)
                    ->first();
                if ($payment) {
                    $payment->status = $booking->payment_status;
                    $payment->paid_at = $booking->paid_at;
                    $payment->save();
                }
            } catch (\Exception $e) {
                Log::warning('Failed to sync Payment model in handleNotification: ' . $e->getMessage());
            }

            Log::info('Midtrans notification processed', [
                'order_id' => $orderId,
                'status' => $transactionStatus,
                'booking_id' => $booking->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Midtrans handleNotification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update booking ketika pembayaran sukses
     */
    protected function updatePaymentSuccess($booking, $payload, $readablePaymentMethod)
    {
        $grossAmount = $payload['gross_amount'] ?? 0;
        $isFullPayment = $grossAmount >= $booking->total_price;

        $booking->payment_status = $isFullPayment ? 'lunas' : 'dp_paid';
        $booking->session_status = 'upcoming';
        $booking->booking_status = 'confirmed';
        $booking->paid_at = now();
        $booking->payment_method = $readablePaymentMethod;
        $booking->payment_transaction_id = $payload['transaction_id'] ?? null;

        Log::info('Payment successful', [
            'booking_id' => $booking->id,
            'amount' => $grossAmount,
            'status' => $booking->payment_status,
            'method' => $readablePaymentMethod
        ]);
    }

    /**
     * Update booking ketika pembayaran pending
     */
    protected function updatePaymentPending($booking, $payload, $readablePaymentMethod)
    {
        $booking->payment_status = 'pending';
        $booking->payment_method = $readablePaymentMethod;
        $booking->payment_transaction_id = $payload['transaction_id'] ?? null;

        Log::info('Payment pending', ['booking_id' => $booking->id]);
    }

    /**
     * Update booking ketika pembayaran gagal
     */
    protected function updatePaymentFailed($booking, $payload, $reason)
    {
        $booking->payment_status = 'expired';
        $booking->session_status = 'cancelled';
        $booking->booking_status = 'cancelled';

        Log::warning('Payment failed', [
            'booking_id' => $booking->id,
            'reason' => $reason
        ]);
    }

    /**
     * Update booking ketika refund
     */
    protected function updatePaymentRefunded($booking, $payload)
    {
        Log::info('Payment refunded', ['booking_id' => $booking->id]);
    }
}
