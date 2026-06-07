<?php
// app/Http/Controllers/CalendarController.php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $year = $request->input('year', $today->year);
        $month = $request->input('month', $today->month);
        
        $calendarData = $this->generateCalendarData($year, $month);
        
        $packages = Package::where('is_active', true)->get();
        
        return view('calendar', [
            'calendarData' => $calendarData,
            'packages' => $packages,
            'year' => $year,
            'month' => $month,
            'currentYear' => $today->year,
            'currentMonth' => $today->month,
        ]);
    }
    
    public function getMonthData(Request $request)
    {
        $year = (int)$request->input('year');
        $month = (int)$request->input('month');
        
        if ($year < 2020 || $year > 2030) {
            $year = Carbon::today()->year;
        }
        if ($month < 1 || $month > 12) {
            $month = Carbon::today()->month;
        }
        
        $calendarData = $this->generateCalendarData($year, $month);
        
        return response()->json([
            'success' => true,
            'calendarData' => $calendarData,
            'year' => $year,
            'month' => $month
        ]);
    }
    
    protected function generateCalendarData($year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        
        $calendar = [];
        $currentDate = Carbon::create($year, $month, 1);
        $today = Carbon::today();
        $now = Carbon::now();
        
        $currentHour = (int)$now->format('H');
        $currentMinute = (int)$now->format('i');
        
        // AMBIL BOOKING YANG SUDAH CONFIRMED - FORMAT TANGGAL UNTUK GROUP BY
        $confirmedBookings = Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->where(function($q) {
                $q->where('payment_status', 'lunas')
                  ->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->get()
            ->map(function($booking) {
                // FORMAT TANGGAL MENJADI Y-m-d SAJA
                $booking->date_key = $booking->booking_date->format('Y-m-d');
                return $booking;
            })
            ->groupBy('date_key');
        
        // Definisikan slot waktu
        $timeSlotsConfig = [
            'morning' => [
                'label' => 'Pagi',
                'start' => '08:00',
                'end' => '11:00',
                'endHour' => 11,
                'endMinute' => 0,
                'icon' => 'fa-sun'
            ],
            'afternoon' => [
                'label' => 'Siang',
                'start' => '13:00',
                'end' => '16:00',
                'endHour' => 16,
                'endMinute' => 0,
                'icon' => 'fa-cloud-sun'
            ],
            'evening' => [
                'label' => 'Sore',
                'start' => '17:00',
                'end' => '20:00',
                'endHour' => 20,
                'endMinute' => 0,
                'icon' => 'fa-moon'
            ]
        ];
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString(); // Format: 2026-04-27
            $isToday = $currentDate->isToday();
            $isPast = $currentDate->lt($today);
            
            // Ambil booking untuk tanggal ini - PAKAI FORMAT YANG SAMA
            $dayConfirmedBookings = $confirmedBookings->get($dateStr, collect());
            
            $slotsData = [];
            $availableCount = 0;
            
            foreach ($timeSlotsConfig as $slotKey => $config) {
                // Cek apakah slot sudah dibooking
                $isBooked = $dayConfirmedBookings->contains('time_slot', $slotKey);
                
                // Cek apakah slot sudah lewat untuk hari ini
                $isPastSlot = false;
                if ($isToday) {
                    $currentTotalMinutes = ($currentHour * 60) + $currentMinute;
                    $slotEndMinutes = ($config['endHour'] * 60) + $config['endMinute'];
                    
                    if ($currentTotalMinutes >= $slotEndMinutes) {
                        $isPastSlot = true;
                    }
                }
                
                // Cek apakah tanggal sudah lewat
                $isDatePast = $isPast;
                
                // Slot tersedia jika: TIDAK dibooking, TIDAK lewat, dan BUKAN tanggal lewat
                $isAvailable = !$isBooked && !$isPastSlot && !$isDatePast;
                
                if ($isAvailable) {
                    $availableCount++;
                }
                
                $slotsData[$slotKey] = [
                    'available' => $isAvailable,
                    'label' => $config['label'],
                    'start_time' => $config['start'],
                    'end_time' => $config['end'],
                    'icon' => $config['icon'],
                    'is_booked' => $isBooked,
                    'is_past_slot' => $isPastSlot,
                    'is_date_past' => $isDatePast,
                    'endHour' => $config['endHour']
                ];
            }
            
            $calendar[$dateStr] = [
                'date' => $dateStr,
                'day' => $currentDate->day,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'total_available_slots' => $availableCount,
                'slots' => $slotsData
            ];
            
            $currentDate->addDay();
        }
        
        return $calendar;
    }
}