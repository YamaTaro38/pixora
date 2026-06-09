<?php
// app/Http/Controllers/Api/MidtransWebhookController.php
// Controller untuk menerima notifikasi pembayaran dari Midtrans.
// Endpoint ini WAJIB di-expose secara publik (tidak butuh auth) karena
// dipanggil langsung oleh server Midtrans.

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * POST /api/midtrans/notification
     *
     * Midtrans akan mengirim HTTP POST setiap kali status transaksi berubah.
     * Kita harus validasi signature lalu update status booking.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', [
            'order_id' => $payload['order_id'] ?? null,
            'status'   => $payload['transaction_status'] ?? null,
        ]);

        // 1. Validasi signature key (wajib untuk keamanan)
        $orderId     = $payload['order_id'] ?? null;
        $statusCode  = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signature   = $payload['signature_key'] ?? null;

        if ($orderId && $statusCode && $grossAmount && $signature) {
            if (!$this->midtransService->validateSignature($orderId, $statusCode, $grossAmount, $signature)) {
                Log::warning('Midtrans webhook: invalid signature', [
                    'order_id' => $orderId,
                ]);
                return response()->json(['ok' => false, 'message' => 'Invalid signature'], 403);
            }
        }

        // 2. Delegasi ke service untuk update booking
        try {
            $result = $this->midtransService->handleNotification($payload);
            return response()->json(['ok' => $result]);
        } catch (\Throwable $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * GET /api/midtrans/ping  — health check untuk Midtrans dashboard.
     */
    public function ping()
    {
        return response()->json([
            'ok'      => true,
            'message' => 'Midtrans webhook endpoint ready',
            'time'    => now()->toIso8601String(),
        ]);
    }
}
