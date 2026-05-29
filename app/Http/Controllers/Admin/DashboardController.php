<?php
// app/Http/Controllers/Admin/DashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik hari ini
        $today = Carbon::today('Asia/Jakarta');
        $todayBookings = Booking::whereDate('created_at', $today)->count();
        $todayRevenue = Booking::whereDate('created_at', $today)
            ->where('payment_status', 'lunas')
            ->sum('total_price');
        
        // Statistik bulan ini
        $monthStart = Carbon::now('Asia/Jakarta')->startOfMonth();
        $monthBookings = Booking::where('created_at', '>=', $monthStart)->count();
        $monthRevenue = Booking::where('created_at', '>=', $monthStart)
            ->where('payment_status', 'lunas')
            ->sum('total_price');
        
        // Statistik status
        $pendingBookings = Booking::where('payment_status', 'pending')
            ->where('expires_at', '>', now())
            ->count();
        
        $confirmedBookings = Booking::where('booking_status', 'confirmed')->count();
        $totalPackages = Package::where('is_active', true)->count();
        $totalCustomers = User::where('role', 'customer')->count();
        
        // Status counts untuk chart
        $statusCounts = [
            'pending' => Booking::where('payment_status', 'pending')->count(),
            'lunas' => Booking::where('payment_status', 'lunas')->count(),
            'dp_paid' => Booking::where('payment_status', 'dp_paid')->count(),
            'expired' => Booking::where('payment_status', 'expired')->count(),
        ];
        
        // Booking terbaru
        $recentBookings = Booking::with('package')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Debug: cek apakah data ada
        Log::info('Dashboard Data:', [
            'todayBookings' => $todayBookings,
            'todayRevenue' => $todayRevenue,
            'monthBookings' => $monthBookings,
            'monthRevenue' => $monthRevenue,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'totalPackages' => $totalPackages,
            'totalCustomers' => $totalCustomers,
            'statusCounts' => $statusCounts,
            'recentBookingsCount' => $recentBookings->count()
        ]);
        
        return view('admin.dashboard', compact(
            'todayBookings', 'todayRevenue', 'monthBookings', 'monthRevenue',
            'pendingBookings', 'confirmedBookings', 'totalPackages', 'totalCustomers',
            'statusCounts', 'recentBookings'
        ));
    }
}