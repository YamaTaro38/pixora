<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Snap;
use Midtrans\Transaction;
use App\Models\Booking;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$notifUrl = config('midtrans.notification_url');
    }

    public function paymentMethods($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();

        $channels = CoreApi::channels() ?? [];

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

    public function pay(Request $request, $token)
    {
        $request->validate(['method' => 'required|string']);
        $booking = Booking::where('public_token', $token)->firstOrFail();

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
                'name' => 'Booking ' . $booking->package->name ?? 'Pixora',
            ]],
        ];

        if (str_contains($request->method, '_va')) {
            $params['bank_transfer'] = [
                'bank' => strtoupper(str_replace('_va', '', $request->method)),
            ];
        } elseif ($request->method === 'qris') {
            $params['qris'] = ['enable' => true];
        } elseif (in_array($request->method, ['gopay', 'shopeepay'])) {
            $params[$request->method] = [];
        }

        try {
            $charge = CoreApi::charge($params);

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

            return response()->json([
                'payment' => $this->formatPayment($payment, $charge, $booking)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal inisiasi pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

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

            $booking->update(['payment_status' => $internalStatus]);
        } catch (\Exception $e) {
        }

        return response()->json([
            'payment_status' => $payment->status,
            'payment_method' => $payment->method,
            'transaction_id' => $payment->transaction_id,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ]);
    }

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

    private function formatPayment($payment, $charge, $booking)
    {
        $payload = [
            'booking_token' => $booking->public_token,
            'method_code' => $payment->method,
            'method_name' => $charge->payment_type ?? $payment->method,
            'method_category' => $this->mapCategory($payment->method),
            'amount' => (float) $payment->amount,
            'fee' => 0,
            'status' => 'pending',
            'transaction_id' => $payment->transaction_id,
            'expired_at' => $payment->expired_at?->toIso8601String(),
        ];

        if (isset($charge->va_numbers) && count($charge->va_numbers) > 0) {
            $va = $charge->va_numbers[0];
            $payload['va_number'] = $va->va_number;
            $payload['bank_code'] = strtoupper($va->bank);
        }

        if (isset($charge->actions)) {
            foreach ($charge->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $payload['qr_url'] = $action->url;
                }
            }
        }

        if (isset($charge->actions)) {
            foreach ($charge->actions as $action) {
                if (in_array($action->name, ['deeplink-redirect', 'mobile-web-redirect'])) {
                    $payload['deeplink'] = $action->url;
                }
            }
        }

        if (isset($charge->payment_code)) {
            $payload['payment_code'] = $charge->payment_code;
        }

        return $payload;
    }
}
