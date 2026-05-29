<?php
// app/Services/MidtransService.php

namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use App\Models\Booking;
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
     */
    protected function configure()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$clientKey = config('midtrans.client_key');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        Log::info('Midtrans Config Check', [
            'server_key_exists' => !empty(Config::$serverKey),
            'server_key_preview' => substr(Config::$serverKey ?? '', 0, 10) . '...',
            'is_production' => Config::$isProduction
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

        // Buat order ID UNIK dan PASTIKAN BERBEDA setiap kali
        $orderId = 'PIX-' . $booking->id . '-' . $now->timestamp . '-' . rand(100, 999);


        // Validasi gross_amount (minimal 10000 atau sesuai ketentuan Midtrans)
        $grossAmount = max((int) $amount, 10000);

        // Siapkan payload minimal untuk testing
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
