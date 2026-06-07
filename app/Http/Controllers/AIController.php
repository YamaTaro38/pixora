<?php
// app/Http/Controllers/AIController.php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Booking;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AIController extends Controller
{
    protected $aiService;
    
    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }
    
    /**
     * Chatbot AI - Menjawab pertanyaan customer
     */
    public function chat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Rate limiting
        $cacheKey = 'chat_limit_' . $request->ip();
        $requestCount = Cache::get($cacheKey, 0);

        if ($requestCount >= 20) {
            return response()->json([
                'reply' => 'Maaf, Anda sudah mencapai batas pertanyaan. Silakan coba lagi nanti atau hubungi admin.'
            ]);
        }

        Cache::put($cacheKey, $requestCount + 1, 3600);

        // Ambil data konteks yang akurat
        $context = $this->getChatbotContext();

        $reply = $this->aiService->chatbot($request->message, $context);

        return response()->json([
            'reply' => $reply,
            'timestamp' => now()->toIso8601String()
        ]);
    }

    /**
     * Ambil data konteks untuk AI Chatbot - AKURAT
     */
    protected function getChatbotContext()
    {
        // Set timezone ke WIB (Asia/Jakarta)
        $now = Carbon::now('Asia/Jakarta');
        $currentHour = (int)$now->format('H');
        $currentMinute = (int)$now->format('i');
        $todayStr = $now->toDateString();
        $currentTotalMinutes = ($currentHour * 60) + $currentMinute;
        
        // Ambil booking yang sudah confirmed (lunas/dp_paid) untuk 7 hari ke depan
        $confirmedBookings = Booking::whereBetween('booking_date', [Carbon::today(), Carbon::today()->addDays(7)])
            ->where(function($q) {
                $q->where('payment_status', 'lunas')
                  ->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->get()
            ->groupBy(function($booking) {
                return $booking->booking_date->format('Y-m-d');
            });
        
        // Definisikan slot waktu dengan END HOUR untuk menentukan lewat
        $timeSlots = [
            'morning' => [
                'label' => 'Pagi',
                'start' => '08:00',
                'end' => '11:00',
                'startHour' => 8,
                'endHour' => 11,
                'endMinute' => 0,
                'endTotalMinutes' => 11 * 60
            ],
            'afternoon' => [
                'label' => 'Siang',
                'start' => '13:00',
                'end' => '16:00',
                'startHour' => 13,
                'endHour' => 16,
                'endMinute' => 0,
                'endTotalMinutes' => 16 * 60
            ],
            'evening' => [
                'label' => 'Sore',
                'start' => '17:00',
                'end' => '20:00',
                'startHour' => 17,
                'endHour' => 20,
                'endMinute' => 0,
                'endTotalMinutes' => 20 * 60
            ],
        ];
        
        // Hitung slot yang tersedia untuk setiap tanggal
        $availableSlots = [];
        $unavailableSlots = []; // Untuk slot yang tidak tersedia (dibooking atau lewat)
        
        for ($i = 0; $i <= 7; $i++) {
            $date = Carbon::today()->addDays($i);
            $dateStr = $date->toDateString();
            $isToday = $date->isToday();
            $dayBookings = $confirmedBookings->get($dateStr, collect());
            
            $availableForDate = [];
            $unavailableForDate = [];
            
            foreach ($timeSlots as $slotKey => $config) {
                $isBooked = $dayBookings->contains('time_slot', $slotKey);
                
                // Cek apakah slot sudah lewat untuk hari ini
                $isPastSlot = false;
                if ($isToday) {
                    if ($currentTotalMinutes >= $config['endTotalMinutes']) {
                        $isPastSlot = true;
                    }
                }
                
                $slotInfo = $config['label'] . ' (' . $config['start'] . '-' . $config['end'] . ')';
                
                if ($isBooked) {
                    $unavailableForDate[] = $slotInfo . ' (sudah dibooking)';
                } elseif ($isPastSlot) {
                    $unavailableForDate[] = $slotInfo . ' (sudah lewat)';
                } else {
                    $availableForDate[] = $slotInfo;
                }
            }
            
            if (!empty($availableForDate)) {
                $availableSlots[$date->translatedFormat('d F Y')] = $availableForDate;
            }
            
            if (!empty($unavailableForDate)) {
                $unavailableSlots[$date->translatedFormat('d F Y')] = $unavailableForDate;
            }
        }
        
        // Ambil semua paket aktif
        $packages = Package::where('is_active', true)
            ->select('name', 'price', 'description', 'duration_hours', 'location_type', 'edited_photos')
            ->get();
        
        // Aturan umum studio
        $rules = [
            "Jam operasional studio: 08:00 - 20:00 WIB",
            "Lokasi studio: Kruwet, Teluk, Kec. Purwokerto Sel., Kabupaten Banyumas, Jawa Tengah 53145",
            "Kebijakan reschedule: maksimal H-3 sebelum sesi",
            "Kebijakan refund: DP tidak dapat dikembalikan jika pembatalan kurang dari H-7",
            "Proses edit foto: 14-21 hari kerja setelah sesi",
            "Cara booking: Pilih paket -> Pilih tanggal dan jam -> Isi data -> Bayar"
        ];
        
        return [
            'available_slots' => $availableSlots,
            'unavailable_slots' => $unavailableSlots,
            'packages' => $packages,
            'rules' => $rules,
            'current_time' => $now->format('H:i'),
            'current_date' => $now->translatedFormat('d F Y'),
            'current_total_minutes' => $currentTotalMinutes
        ];
    }
    
    /**
     * Generate pose menggunakan AI
     */
    public function generatePose(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'theme' => 'required|string|max:50',
            'prompt' => 'nullable|string|max:200',
            'count' => 'integer|min:1|max:4'
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        
        $count = min($request->input('count', 2), 4);
        $theme = $request->theme;
        $userPrompt = $request->input('prompt', '');
        
        // Coba generate dari AI
        $images = [];
        $aiSuccess = false;
        
        for ($i = 0; $i < $count; $i++) {
            $prompt = $this->buildPosePrompt($theme, $userPrompt, $i);
            $imageUrl = $this->aiService->generateImage($prompt);
            
            if ($imageUrl) {
                $aiSuccess = true;
                $images[] = [
                    'url' => $imageUrl,
                    'prompt' => $prompt,
                    'source' => 'ai'
                ];
            }
        }
        
        // Jika AI gagal, gunakan fallback dari database pose_references
        if (!$aiSuccess || empty($images)) {
            $fallbackImages = $this->getFallbackPoses($theme, $count);
            return response()->json([
                'images' => $fallbackImages,
                'source' => 'local',
                'message' => 'Menggunakan referensi dari koleksi kami'
            ]);
        }
        
        return response()->json([
            'images' => $images,
            'source' => 'ai_generated'
        ]);
    }
}