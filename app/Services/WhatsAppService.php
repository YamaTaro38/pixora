<?php
// app/Services/WhatsAppService.php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendBookingLink(Booking $booking)
    {
        $message = "Halo {$booking->customer_name}! 🎉\n\n";
        $message .= "Terima kasih sudah booking di Pixora Studio.\n\n";
        $message .= "📋 *Detail Booking:*\n";
        $message .= "Kode: {$booking->booking_code}\n";
        $message .= "Paket: {$booking->package->name}\n";
        $message .= "Tanggal: " . $booking->booking_date->format('d/m/Y') . "\n";
        $message .= "Jam: {$booking->timeSlotLabel}\n\n";
        $message .= "🔗 *Link Bukti Booking Anda:*\n";
        $message .= $booking->public_url . "\n\n";
        $message .= "Simpan link tersebut untuk melihat detail booking kapan saja.\n";
        $message .= "Silakan selesaikan pembayaran Anda melalui link di atas.\n\n";
        $message .= "Terima kasih 🙏\n";
        $message .= "Pixora Studio";
        
        // Send via WhatsApp API (using WA Gateway like WATI, Fonnte, or WhatsApp Cloud API)
        return $this->sendViaWhatsApp($booking->customer_phone, $message);
    }
    
    protected function sendViaWhatsApp($phone, $message)
    {
        // Implementasi sesuai gateway WhatsApp yang dipilih
        // Contoh dengan Fonnte API
        $apiKey = config('services.whatsapp.api_key');
        
        if (!$apiKey) {
            Log::info('WhatsApp message would be sent:', ['phone' => $phone, 'message' => $message]);
            return true;
        }
        
        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey
            ])->post('https://api.fonnte.com/send', [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62'
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('WhatsApp Error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function resendBookingLink(Booking $booking)
    {
        return $this->sendBookingLink($booking);
    }
}