<?php
// app/Http/Controllers/Api/MobileApiController.php
// Controller API untuk aplikasi mobile Flutter Pixora

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Booking;
use App\Services\MidtransService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MobileApiController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Get all active packages
     */
    public function packages()
    {
        $packages = Package::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'description' => $package->description,
                    'image' => $package->image,
                    'image_url' => $package->image_url,
                    'price' => $package->price,
                    'down_payment' => $package->down_payment,
                    'duration_hours' => $package->duration_hours,
                    'edited_photos' => $package->edited_photos,
                    'location_type' => $package->location_type,
                    'inclusions' => $package->inclusions,
                    'is_active' => $package->is_active,
                    'sort_order' => $package->sort_order,
                ];
            });

        return response()->json($packages);
    }

    /**
     * Get package detail by slug
     */
    public function packageDetail($slug)
    {
        $package = Package::where('slug', $slug)->where('is_active', true)->first();

        if (!$package) {
            return response()->json(['error' => 'Paket tidak ditemukan'], 404);
        }

        return response()->json([
            'data' => [
                'id' => $package->id,
                'name' => $package->name,
                'slug' => $package->slug,
                'description' => $package->description,
                'image' => $package->image,
                'image_url' => $package->image_url,
                'price' => $package->price,
                'down_payment' => $package->down_payment,
                'duration_hours' => $package->duration_hours,
                'edited_photos' => $package->edited_photos,
                'location_type' => $package->location_type,
                'inclusions' => $package->inclusions,
                'is_active' => $package->is_active,
                'sort_order' => $package->sort_order,
                'galleries' => $package->galleries->map(function ($g) {
                    return [
                        'id' => $g->id,
                        'package_id' => $g->package_id,
                        'image' => $g->image,
                        'sort_order' => $g->sort_order,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Get calendar data for a month
     */
    public function calendarData(Request $request)
    {
        $today = Carbon::today();
        $year = (int) $request->input('year', $today->year);
        $month = (int) $request->input('month', $today->month);

        if ($year < 2020 || $year > 2030) $year = $today->year;
        if ($month < 1 || $month > 12) $month = $today->month;

        $startDate = Carbon::create($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();
        $now = Carbon::now();
        $currentHour = (int) $now->format('H');
        $currentMinute = (int) $now->format('i');

        $confirmedBookings = Booking::whereBetween('booking_date', [$startDate, $endDate])
            ->where(function ($q) {
                $q->where('payment_status', 'lunas')->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->get()
            ->groupBy(function ($b) {
                return $b->booking_date->format('Y-m-d');
            });

        $timeSlotsConfig = [
            'morning' => ['label' => 'Pagi', 'start' => '08:00', 'end' => '11:00', 'endHour' => 11, 'endMinute' => 0, 'icon' => 'fa-sun'],
            'afternoon' => ['label' => 'Siang', 'start' => '13:00', 'end' => '16:00', 'endHour' => 16, 'endMinute' => 0, 'icon' => 'fa-cloud-sun'],
            'evening' => ['label' => 'Sore', 'start' => '17:00', 'end' => '20:00', 'endHour' => 20, 'endMinute' => 0, 'icon' => 'fa-moon'],
        ];

        $calendar = [];
        $currentDate = Carbon::create($year, $month, 1);

        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->toDateString();
            $isToday = $currentDate->isToday();
            $isPast = $currentDate->lt($today);
            $dayBookings = $confirmedBookings->get($dateStr, collect());

            $slotsData = [];
            $availableCount = 0;

            foreach ($timeSlotsConfig as $slotKey => $config) {
                $isBooked = $dayBookings->contains('time_slot', $slotKey);
                $isPastSlot = false;
                if ($isToday) {
                    $currentTotalMinutes = ($currentHour * 60) + $currentMinute;
                    $slotEndMinutes = ($config['endHour'] * 60) + $config['endMinute'];
                    if ($currentTotalMinutes >= $slotEndMinutes) $isPastSlot = true;
                }

                $isAvailable = !$isBooked && !$isPastSlot && !$isPast;
                if ($isAvailable) $availableCount++;

                $slotsData[$slotKey] = [
                    'available' => $isAvailable,
                    'label' => $config['label'],
                    'start_time' => $config['start'],
                    'end_time' => $config['end'],
                    'icon' => $config['icon'],
                    'is_booked' => $isBooked,
                    'is_past_slot' => $isPastSlot,
                    'is_date_past' => $isPast,
                ];
            }

            $calendar[$dateStr] = [
                'date' => $dateStr,
                'day' => $currentDate->day,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'total_available_slots' => $availableCount,
                'slots' => $slotsData,
            ];

            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'calendarData' => $calendar,
            'year' => $year,
            'month' => $month,
        ]);
    }

    /**
     * Check slot availability
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,evening',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $exists = Booking::where('booking_date', $request->date)
            ->where('time_slot', $request->time_slot)
            ->where(function ($q) {
                $q->where('payment_status', 'lunas')->orWhere('payment_status', 'dp_paid');
            })
            ->where('booking_status', 'confirmed')
            ->exists();

        return response()->json(['available' => !$exists, 'date' => $request->date, 'time_slot' => $request->time_slot]);
    }

    /**
     * Store booking (JSON API)
     */
    public function storeBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|exists:packages,id',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:100',
            'booking_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|in:morning,afternoon,evening',
            'special_requests' => 'nullable|string',
            'payment_type' => 'required|in:full,down_payment',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package = Package::find($request->package_id);
        $amountToPay = $request->payment_type == 'full' ? $package->price : ($package->down_payment ?? $package->price * 0.5);
        $expiresAt = Carbon::now('Asia/Jakarta')->addMinutes(30);

        try {
            $booking = Booking::create([
                'booking_code' => 'PX/' . date('Ym') . '/' . strtoupper(substr(uniqid(), -6)),
                'public_token' => bin2hex(random_bytes(32)),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'package_id' => $package->id,
                'booking_date' => $request->booking_date,
                'time_slot' => $request->time_slot,
                'total_price' => $package->price,
                'down_payment' => $request->payment_type == 'down_payment' ? $amountToPay : 0,
                'payment_status' => 'pending',
                'session_status' => 'upcoming',
                'booking_status' => 'draft',
                'special_requests' => $request->special_requests,
                'expires_at' => $expiresAt,
                'slot_locked_until' => $expiresAt,
            ]);

            // Create Midtrans snap token
            try {
                $this->midtransService->createSnapToken($booking, $amountToPay);
                $booking->refresh();
            } catch (\Exception $e) {
                Log::warning('Failed to create snap token for mobile booking: ' . $e->getMessage());
                // Continue without snap token — payment page will generate one
            }

            return response()->json([
                'booking' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'public_token' => $booking->public_token,
                    'customer_name' => $booking->customer_name,
                    'customer_phone' => $booking->customer_phone,
                    'customer_email' => $booking->customer_email,
                    'package_id' => $booking->package_id,
                    'booking_date' => $booking->booking_date->toDateString(),
                    'time_slot' => $booking->time_slot,
                    'total_price' => $booking->total_price,
                    'down_payment' => $booking->down_payment,
                    'payment_status' => $booking->payment_status,
                    'session_status' => $booking->session_status,
                    'booking_status' => $booking->booking_status,
                    'snap_token' => $booking->snap_token,
                    'expires_at' => $booking->expires_at?->toIso8601String(),
                    'package' => ['name' => $package->name],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Mobile booking store error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat booking: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get booking detail
     */
    public function bookingDetail($token)
    {
        $booking = Booking::where('public_token', $token)->with('package')->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        return response()->json([
            'booking' => [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'public_token' => $booking->public_token,
                'customer_name' => $booking->customer_name,
                'customer_phone' => $booking->customer_phone,
                'customer_email' => $booking->customer_email,
                'package_id' => $booking->package_id,
                'booking_date' => $booking->booking_date->toDateString(),
                'time_slot' => $booking->time_slot,
                'total_price' => $booking->total_price,
                'down_payment' => $booking->down_payment,
                'payment_status' => $booking->payment_status,
                'session_status' => $booking->session_status,
                'booking_status' => $booking->booking_status,
                'special_requests' => $booking->special_requests,
                'paid_at' => $booking->paid_at?->toIso8601String(),
                'payment_method' => $booking->payment_method,
                'payment_transaction_id' => $booking->payment_transaction_id,
                'snap_token' => $booking->snap_token,
                'expires_at' => $booking->expires_at?->toIso8601String(),
                'package' => $booking->package ? ['name' => $booking->package->name] : null,
            ],
        ]);
    }

    /**
     * Check payment status
     */
    public function bookingStatus($token)
    {
        $booking = Booking::where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        return response()->json([
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'is_paid' => in_array($booking->payment_status, ['lunas', 'dp_paid']),
            'is_expired' => $booking->payment_status === 'expired',
            'booking_status' => $booking->booking_status,
        ]);
    }

    /**
     * COD payment for mobile
     */
    public function bookingPayCod($token)
    {
        $booking = Booking::where('public_token', $token)->first();
        if (!$booking) return response()->json(['error' => 'Booking tidak ditemukan'], 404);

        if ($booking->payment_status === 'pending') {
            $booking->payment_method = 'Bayar di Tempat (COD)';
            $booking->booking_status = 'confirmed';
            $booking->session_status = 'upcoming';
            $booking->save();

            Log::info('Mobile booking confirmed via COD', [
                'booking_id' => $booking->id,
                'time_slot' => $booking->time_slot
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dikonfirmasi dengan metode COD',
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Status booking tidak valid untuk COD'], 400);
    }
}
