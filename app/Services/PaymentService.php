<?php
// app/Services/PaymentService.php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $serverKey;
    protected $isProduction;
    protected $apiUrl;
    
    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key');
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->apiUrl = $this->isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }
    
    public function createTransaction(Booking $booking, $amount)
    {
        $orderId = $booking->booking_code . '-' . time();
        
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'phone' => $booking->customer_phone,
                'email' => $booking->customer_email ?? 'guest@pixora.com'
            ],
            'item_details' => [
                [
                    'id' => $booking->package_id,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => $booking->package->name . ' - ' . $booking->booking_date
                ]
            ],
            'credit_card' => [
                'secure' => true
            ]
        ];
        
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl, $payload);
                
            if ($response->successful()) {
                $result = $response->json();
                
                $booking->update([
                    'payment_transaction_id' => $orderId,
                    'payment_details' => $result
                ]);
                
                return $result;
            }
            
            throw new \Exception('Midtrans Error: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Payment Error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function getPaymentData(Booking $booking)
    {
        $paymentDetails = $booking->payment_details;
        
        if ($paymentDetails && isset($paymentDetails['redirect_url'])) {
            return [
                'snap_token' => $paymentDetails['token'] ?? null,
                'redirect_url' => $paymentDetails['redirect_url']
            ];
        }
        
        return null;
    }
    
    public function handleCallback($payload)
    {
        // Verifikasi signature
        // Update booking status berdasarkan notifikasi dari Midtrans
        // Implementation depends on Midtrans webhook structure
    }
}