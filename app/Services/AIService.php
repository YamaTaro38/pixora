<?php
// app/Services/AIService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $apiUrl;
    protected $imageUrl;

    public function __construct()
    {
        $this->apiUrl = 'https://text.pollinations.ai';
        $this->imageUrl = 'https://image.pollinations.ai';
    }

    public function chatbot($message, $context)
    {
        $systemPrompt = $this->buildStrictSystemPrompt($context);

        try {
            $response = Http::timeout(20)
                ->post($this->apiUrl . '/openai', [
                    'model' => 'openai',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $message]
                    ],
                    'temperature' => 0.5,
                    'max_tokens' => 300
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? null;

                if ($reply) {
                    $cleaned = $this->cleanChatResponse($reply);
                    if ($this->isResponseValid($cleaned, $context)) {
                        return $cleaned;
                    }
                }
            }

            return $this->getFallbackResponse($message, $context);
        } catch (\Exception $e) {
            Log::error('AI Chatbot Error: ' . $e->getMessage());
            return $this->getFallbackResponse($message, $context);
        }
    }

    protected function buildStrictSystemPrompt($context)
    {
        $availableSlots = $context['available_slots'] ?? [];
        $unavailableSlots = $context['unavailable_slots'] ?? [];
        $packages = $context['packages'] ?? [];
        $rules = $context['rules'] ?? [];
        $currentDate = $context['current_date'] ?? '';
        $currentTime = $context['current_time'] ?? '';

        $prompt = "Anda adalah asisten customer service untuk studio foto Pixora Studio.

INFORMASI PENTING:
- Hari ini: $currentDate, jam: $currentTime WIB
- Untuk slot yang sudah LEWAT (melewati jam operasional) TIDAK BISA dipilih
- Untuk slot yang sudah DIBOOKING TIDAK BISA dipilih

JADWAL YANG TERSEDIA (HANYA SLOT YANG BISA DIPILIH):\n";

        if (count($availableSlots) > 0) {
            foreach ($availableSlots as $date => $slots) {
                $slotList = implode(', ', $slots);
                $prompt .= "- $date: $slotList\n";
            }
        } else {
            $prompt .= "Tidak ada jadwal yang tersedia.\n";
        }

        $prompt .= "\nJADWAL YANG TIDAK TERSEDIA (sudah dibooking atau sudah lewat):\n";
        if (count($unavailableSlots) > 0) {
            foreach ($unavailableSlots as $date => $slots) {
                $slotList = implode(', ', $slots);
                $prompt .= "- $date: $slotList\n";
            }
        } else {
            $prompt .= "Tidak ada data slot tidak tersedia.\n";
        }

        $prompt .= "\nDAFTAR PAKET FOTOGRAFI:\n";
        foreach ($packages as $package) {
            $price = 'Rp ' . number_format($package['price'], 0, ',', '.');
            $prompt .= "- {$package['name']}: $price\n";
            $prompt .= "  Durasi: {$package['duration_hours']} jam, Foto edit: {$package['edited_photos']} foto\n";
        }

        $prompt .= "\nATURAN STUDIO:\n";
        foreach ($rules as $rule) {
            $prompt .= "- $rule\n";
        }

        $prompt .= "\nINSTRUKSI:
1. Jawab dengan BAHASA INDONESIA yang baik dan ramah
2. JIKA ada jadwal, sebutkan dengan jelas tanggal dan slot yang tersedia
3. JANGAN pernah bilang tidak ada jadwal jika data menunjukkan ada jadwal
4. Berikan rekomendasi paket sesuai tema yang disebutkan user
5. Jawab singkat, maksimal 3-4 kalimat
6. JANGAN sebutkan API, kode program, atau informasi teknis apapun
7. JANGAN gunakan simbol asterisk (*) atau underscore (_)

MULAI JAWAB DENGAN RAMAH DAN MENYENANGKAN!";

        return $prompt;
    }

    protected function cleanChatResponse($text)
    {
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        $text = preg_replace('/`[^`]+`/', '', $text);
        $text = preg_replace('/\{[^{}]*\}/', '', $text);
        $text = preg_replace('/\[[^\[\]]*\]/', '', $text);
        $text = preg_replace('/https?:\/\/[^\s]+/', '', $text);
        $text = preg_replace('/\b(API|endpoint|curl|json|http|POST|GET|GitHub|Pollinations)\b/i', '', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        if (strlen($text) > 500) {
            $text = substr($text, 0, 500) . '...';
        }

        return trim($text);
    }

    protected function isResponseValid($text, $context)
    {
        $lowerText = strtolower($text);

        $forbidden = ['github', 'api', 'pollinations', 'endpoint', 'curl', 'json'];
        foreach ($forbidden as $word) {
            if (strpos($lowerText, $word) !== false) {
                return false;
            }
        }

        // Cek apakah response kontradiksi dengan data
        $availableSlots = $context['available_slots'] ?? [];
        $hasSlots = count($availableSlots) > 0;

        if ($hasSlots && strpos($lowerText, 'tidak ada jadwal') !== false) {
            return false; // Response kontradiksi
        }

        return strlen($text) >= 10;
    }

    protected function getFallbackResponse($message, $context)
    {
        $messageLower = strtolower($message);
        $availableSlots = $context['available_slots'] ?? [];
        $unavailableSlots = $context['unavailable_slots'] ?? [];
        $packages = $context['packages'] ?? [];
        $currentDate = $context['current_date'] ?? '';
        $currentTime = $context['current_time'] ?? '';

        // CEK JADWAL
        if (str_contains($messageLower, 'jadwal') || str_contains($messageLower, 'slot') || str_contains($messageLower, 'tanggal') || str_contains($messageLower, 'hari ini')) {

            // Cek apakah user menanyakan "hari ini" secara spesifik
            $askToday = str_contains($messageLower, 'hari ini');

            if ($askToday) {
                // Filter hanya untuk hari ini
                $todayAvailable = [];
                $todayUnavailable = [];

                foreach ($availableSlots as $date => $slots) {
                    if (str_contains($date, $currentDate)) {
                        $todayAvailable = $slots;
                        break;
                    }
                }

                foreach ($unavailableSlots as $date => $slots) {
                    if (str_contains($date, $currentDate)) {
                        $todayUnavailable = $slots;
                        break;
                    }
                }

                if (count($todayAvailable) > 0) {
                    $slotList = implode(', ', $todayAvailable);
                    return "Untuk hari ini ($currentDate), tersedia slot: $slotList. VWIB.\n\nSilakan segera booking melalui halaman Kalender Booking karena slot terbatas!";
                } else {
                    $reasonText = '';
                    if (count($todayUnavailable) > 0) {
                        $reasonText = " Slot yang tersedia: " . implode(', ', $todayUnavailable);
                    }
                    return "Maaf, untuk hari ini ($currentDate) tidak ada slot yang tersedia.$reasonText\n\nSilakan cek jadwal untuk tanggal berikutnya atau hubungi admin untuk informasi lebih lanjut.";
                }
            }

            // Tampilkan semua jadwal
            if (count($availableSlots) > 0) {
                $response = "Berikut jadwal yang tersedia dalam 7 hari ke depan:\n";
                foreach ($availableSlots as $date => $slots) {
                    $slotList = implode(', ', $slots);
                    $response .= "• $date: $slotList\n";
                }
                $response .= "\nSilakan pilih tanggal dan jam yang diinginkan untuk booking melalui halaman Kalender Booking.";
                return $response;
            } else {
                return "Maaf, untuk saat ini tidak ada jadwal yang tersedia dalam 7 hari ke depan. Silakan cek kembali nanti atau hubungi admin untuk informasi lebih lanjut.";
            }
        }

        // REKOMENDASI PAKET
        if (str_contains($messageLower, 'paket') || str_contains($messageLower, 'rekomendasi')) {
            if (str_contains($messageLower, 'prewedding') || str_contains($messageLower, 'wedding')) {
                $package = $packages->firstWhere('name', 'like', '%Prewedding%');
                if ($package) {
                    return "Untuk prewedding, saya rekomendasikan " . $package['name'] . " dengan harga " . number_format($package['price'], 0, ',', '.') . ". Paket ini termasuk " . $package['duration_hours'] . " jam sesi dan " . $package['edited_photos'] . " foto edit. Apakah Anda tertarik?";
                }
            }

            $response = "Berikut paket yang tersedia:\n";
            foreach ($packages as $package) {
                $response .= "• " . $package['name'] . ": Rp " . number_format($package['price'], 0, ',', '.') . " (" . $package['duration_hours'] . " jam, " . $package['edited_photos'] . " foto edit)\n";
            }
            return $response . "\nUntuk detail lebih lanjut, silakan cek halaman Paket Fotografi.";
        }

        // HARGA
        if (str_contains($messageLower, 'harga') || str_contains($messageLower, 'biaya')) {
            $response = "Berikut daftar harga paket kami:\n";
            foreach ($packages as $package) {
                $response .= "• " . $package['name'] . ": Rp " . number_format($package['price'], 0, ',', '.') . "\n";
            }
            return $response . "\nUntuk promo dan diskon, silakan hubungi admin kami.";
        }

        // LOKASI
        if (str_contains($messageLower, 'lokasi') || str_contains($messageLower, 'alamat')) {
            return "Studio Pixora berlokasi di Purwokerto Selatan. Untuk alamat lengkap, silakan hubungi admin kami melalui WhatsApp di 0812-3456-7890.";
        }

        // CARA BOOKING
        if (str_contains($messageLower, 'booking') || str_contains($messageLower, 'cara')) {
            return "Cara booking di Pixora sangat mudah:\n1. Pilih paket yang diinginkan\n2. Pilih tanggal dan jam di kalender\n3. Isi data diri\n4. Lakukan pembayaran\n\nLink bukti booking akan dikirim ke WhatsApp Anda. Selamat mencoba!";
        }

        // DEFAULT
        $hasSlots = count($availableSlots) > 0;
        $slotHint = $hasSlots ? "Cek jadwal hari ini" : "Maaf saat ini tidak ada jadwal";

        return "Halo! Saya asisten Pixora. Saat ini jam {$currentTime} WIB.\n\n" .
            "Saya bisa membantu informasi:\n" .
            "• " . $slotHint . "\n" .
            "• Paket fotografi dan harga\n" .
            "• Cara booking\n" .
            "• Lokasi studio\n\n" .
            "Ada yang ingin ditanyakan? Contoh: 'Cek jadwal hari ini' atau 'Harga paket prewedding'";
    }

    public function generateImage($prompt)
    {
        try {
            $encodedPrompt = urlencode($prompt);
            $url = $this->imageUrl . "/prompt/" . $encodedPrompt . "?width=512&height=512&nologo=true";
            $response = Http::timeout(25)->get($url);
            if ($response->successful()) {
                return $url;
            }
            return null;
        } catch (\Exception $e) {
            Log::error('AI Image Error: ' . $e->getMessage());
            return null;
        }
    }
}
