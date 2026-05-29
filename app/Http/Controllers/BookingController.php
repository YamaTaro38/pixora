<?php
// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Services\MidtransService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $midtransService;
    protected $whatsappService;

    public function __construct(
        MidtransService $midtransService,
        WhatsAppService $whatsappService
    ) {
        $this->midtransService = $midtransService;
        $this->whatsappService = $whatsappService;
    }

    /**
     * Cek ketersediaan slot (AJAX)
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,evening'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existingBooking = Booking::where('booking_date', $request->date)
            ->where('time_slot', $request->time_slot)
            ->where(function ($q) {
                $q->where('payment_status', 'lunas')
                    ->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->exists();

        return response()->json([
            'available' => !$existingBooking,
            'date' => $request->date,
            'time_slot' => $request->time_slot
        ]);
    }

    /**
     * Store booking baru
     */
    public function store(Request $request)
    {
        Log::info('=== STORE METHOD CALLED ===', $request->all());

        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'booking_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'special_requests' => 'nullable|string',
            'payment_type' => 'required|in:full,down_payment'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $package = Package::find($request->package_id);
        if (!$package) {
            return redirect()->back()->with('error', 'Paket tidak ditemukan')->withInput();
        }

        // CEK DOUBLE BOOKING - Cek booking yang sudah confirmed (lunas/dp_paid)
        $existingConfirmedBooking = Booking::where('booking_date', $request->booking_date)
            ->where('time_slot', $request->time_slot)
            ->where(function ($q) {
                $q->where('payment_status', 'lunas')
                    ->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->exists();

        if ($existingConfirmedBooking) {
            return redirect()->back()
                ->with('error', 'Maaf, slot untuk tanggal dan jam ini sudah dibooking dan lunas. Silakan pilih jadwal lain.')
                ->withInput();
        }

        // CEK BOOKING PENDING yang belum expired
        $existingPendingBooking = Booking::where('booking_date', $request->booking_date)
            ->where('time_slot', $request->time_slot)
            ->where('payment_status', 'pending')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existingPendingBooking) {
            return redirect()->back()
                ->with('error', 'Maaf, slot untuk tanggal dan jam ini sedang diproses oleh customer lain. Silakan pilih jadwal lain.')
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $amountToPay = $request->payment_type == 'full'
                ? $package->price
                : ($package->down_payment ?? $package->price * 0.5);

            $now = Carbon::now('Asia/Jakarta');
            $expiresAt = $now->copy()->addMinutes(30);

            $userId = null;
            if (Auth::check()) {
                $user = Auth::user();
                $userId = $user->id;
            }

            $booking = new Booking();
            $booking->user_id = $userId;
            $booking->booking_code = $this->generateBookingCode();
            $booking->public_token = $this->generatePublicToken();
            $booking->customer_name = $request->customer_name;
            $booking->customer_phone = $request->customer_phone;
            $booking->customer_email = $request->customer_email;
            $booking->package_id = $package->id;
            $booking->booking_date = $request->booking_date;
            $booking->time_slot = $request->time_slot;
            $booking->total_price = $package->price;
            $booking->down_payment = $request->payment_type == 'down_payment' ? $amountToPay : 0;
            $booking->payment_status = 'pending';
            $booking->session_status = 'upcoming';
            $booking->booking_status = 'draft';
            $booking->special_requests = $request->special_requests;
            $booking->expires_at = $expiresAt;
            $booking->slot_locked_until = $expiresAt;
            $booking->save();

            DB::commit();

            Log::info('Booking created', ['booking_id' => $booking->id, 'time_slot' => $booking->time_slot]);

            // Buat Snap Token
            $snapToken = $this->midtransService->createSnapToken($booking, $amountToPay);
            $booking->refresh();

            return redirect()->route('booking.payment', ['token' => $booking->public_token]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses booking: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Halaman pembayaran
     */
    public function payment($token)
    {
        Log::info('Payment page accessed', ['token' => $token]);

        $booking = Booking::where('public_token', $token)->first();

        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }

        if (in_array($booking->payment_status, ['lunas', 'dp_paid'])) {
            return redirect()->route('booking.show', $token)
                ->with('success', 'Booking sudah lunas.');
        }

        if ($booking->payment_status === 'expired') {
            return redirect()->route('booking.show', $token)
                ->with('error', 'Booking sudah kadaluarsa.');
        }

        $amountToPay = $booking->down_payment > 0
            ? $booking->down_payment
            : $booking->total_price;

        return view('booking-payment', [
            'booking' => $booking,
            'snapToken' => $booking->snap_token,
            'amountToPay' => $amountToPay,
            'clientKey' => config('midtrans.client_key')
        ]);
    }

    /**
     * Detail booking (public URL)
     */
    public function show($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();
        return view('booking-show', compact('booking'));
    }

    /**
     * Cek status payment via AJAX
     */
    public function checkPaymentStatus($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();

        $statusChanged = false;

        // Jika masih pending dan ada midtrans_order_id, cek ke Midtrans
        if ($booking->payment_status === 'pending' && $booking->midtrans_order_id) {
            try {
                $status = $this->midtransService->getTransactionStatus($booking->midtrans_order_id);

                // Validasi status dengan aman
                if ($status && isset($status->transaction_status)) {
                    $transactionStatus = $status->transaction_status;
                    $paymentType = $status->payment_type ?? 'unknown';
                    $transactionId = $status->transaction_id ?? null;

                    if (in_array($transactionStatus, ['capture', 'settlement'])) {
                        // UPDATE STATUS PEMBAYARAN
                        $booking->payment_status = 'lunas';
                        $booking->session_status = 'upcoming';
                        $booking->booking_status = 'confirmed';
                        $booking->paid_at = now();
                        $booking->payment_method = $this->getPaymentMethodName($paymentType);
                        $booking->payment_transaction_id = $transactionId;
                        $booking->save();

                        $statusChanged = true;

                        Log::info('Payment confirmed via check', [
                            'booking_id' => $booking->id,
                            'payment_method' => $booking->payment_method,
                            'transaction_status' => $transactionStatus
                        ]);
                    } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
                        $booking->payment_status = 'expired';
                        $booking->session_status = 'cancelled';
                        $booking->booking_status = 'cancelled';
                        $booking->save();

                        Log::info('Payment expired via check', [
                            'booking_id' => $booking->id,
                            'transaction_status' => $transactionStatus
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Check status error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'is_paid' => in_array($booking->payment_status, ['lunas', 'dp_paid']),
            'is_expired' => $booking->payment_status === 'expired',
            'status_changed' => $statusChanged
        ]);
    }

    /**
     * Handle payment callback dari Midtrans
     */
    public function paymentCallback(Request $request)
    {
        Log::info('=== MIDTRANS CALLBACK ===', $request->all());
        
        try {
            $orderId = $request->order_id;
            $transactionStatus = $request->transaction_status;
            $paymentType = $request->payment_type;
            $transactionId = $request->transaction_id;
            
            $booking = Booking::where('midtrans_order_id', $orderId)->first();
            
            if (!$booking) {
                Log::warning('Booking not found for order_id: ' . $orderId);
                return response()->json(['message' => 'Booking not found'], 404);
            }
            
            DB::beginTransaction();
            
            if (in_array($transactionStatus, ['capture', 'settlement'])) {
                $booking->payment_status = 'lunas';
                $booking->session_status = 'upcoming';
                $booking->booking_status = 'confirmed'; // INI PENTING!
                $booking->paid_at = now();
                $booking->payment_method = $this->getPaymentMethodName($paymentType);
                $booking->payment_transaction_id = $transactionId;
                $booking->save();
                
                Log::info('Payment SUCCESS - Booking confirmed', [
                    'booking_id' => $booking->id,
                    'time_slot' => $booking->time_slot,
                    'booking_status' => $booking->booking_status
                ]);
            } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
                $booking->payment_status = 'expired';
                $booking->session_status = 'cancelled';
                $booking->booking_status = 'cancelled';
                $booking->save();
                
                Log::info('Payment FAILED', ['booking_id' => $booking->id]);
            }
            
            DB::commit();
            
            return response()->json(['message' => 'OK'], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Callback error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }

    public function invoice($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();
        
        // Pastikan booking sudah lunas
        if (!in_array($booking->payment_status, ['lunas', 'dp_paid'])) {
            return redirect()->route('booking.show', $token)
                ->with('error', 'Invoice hanya tersedia untuk booking yang sudah lunas.');
        }
        
        return view('booking-invoice', compact('booking'));
    }

    /**
     * Update payment method dari frontend
     */
    public function updatePaymentMethod(Request $request, $token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();
        $booking->payment_method = $request->payment_method;
        $booking->save();

        return response()->json(['success' => true]);
    }

    /**
     * Generate kode booking unik
     */
    protected function generateBookingCode()
    {
        return 'PX/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Generate public token unik
     */
    protected function generatePublicToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Get payment method name
     */
    protected function getPaymentMethodName($paymentType)
    {
        $map = [
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
        return $map[$paymentType] ?? ucfirst(str_replace('_', ' ', $paymentType));
    }

    /**
     * Get waktu slot
     */
    protected function getSlotTime($slot)
    {
        $times = [
            'morning' => ['start' => '08:00:00', 'end' => '11:00:00'],
            'afternoon' => ['start' => '13:00:00', 'end' => '16:00:00'],
            'evening' => ['start' => '17:00:00', 'end' => '20:00:00'],
        ];
        return $times[$slot] ?? $times['morning'];
    }

    /**
     * Download invoice sebagai PDF
     */
    public function downloadInvoice($token)
    {
        $booking = Booking::where('public_token', $token)->firstOrFail();
        
        // Pastikan booking sudah lunas atau DP
        if (!in_array($booking->payment_status, ['lunas', 'dp_paid'])) {
            return redirect()->route('booking.show', $token)
                ->with('error', 'Invoice hanya tersedia untuk booking yang sudah dibayar.');
        }
        
        // Load view untuk PDF
        $pdf = Pdf::loadView('booking-invoice-pdf', compact('booking'));
        
        // Setting PDF
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);
        
        // Buat nama file yang valid (hilangkan karakter / dan \)
        $cleanBookingCode = str_replace(['/', '\\'], '-', $booking->booking_code);
        $filename = 'Invoice_' . $cleanBookingCode . '.pdf';
        
        // Download file
        return $pdf->download($filename);
    }
}
