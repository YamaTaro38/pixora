<?php
// app/Http/Controllers/Admin/ReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now('Asia/Jakarta')->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now('Asia/Jakarta')->endOfMonth();
        
        $bookingsData = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total, SUM(total_price) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $statusStats = [
            'pending' => Booking::whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'pending')->count(),
            'lunas' => Booking::whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'lunas')->count(),
            'dp_paid' => Booking::whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'dp_paid')->count(),
            'expired' => Booking::whereBetween('created_at', [$startDate, $endDate])->where('payment_status', 'expired')->count(),
        ];
        
        $topPackages = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->select('package_id', DB::raw('COUNT(*) as total'))
            ->with('package')
            ->groupBy('package_id')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        $totalRevenue = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'lunas')
            ->sum('total_price');
        
        return view('admin.reports.index', compact(
            'bookingsData', 'statusStats', 'topPackages', 'totalRevenue', 'startDate', 'endDate'
        ));
    }
    
    public function export(Request $request)
    {
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now('Asia/Jakarta')->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now('Asia/Jakarta')->endOfMonth();
        
        $bookings = Booking::with('package')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $filename = 'laporan_booking_' . $startDate->format('Ymd') . '_' . $endDate->format('Ymd') . '.csv';
        
        $handle = fopen('php://temp', 'w');
        fputcsv($handle, [
            'Kode Booking', 'Nama Customer', 'WhatsApp', 'Paket', 'Tanggal Sesi',
            'Jam Sesi', 'Total Harga', 'Status Pembayaran', 'Tanggal Booking'
        ]);
        
        foreach ($bookings as $booking) {
            fputcsv($handle, [
                $booking->booking_code,
                $booking->customer_name,
                $booking->customer_phone,
                $booking->package->name,
                $booking->booking_date,
                $booking->time_slot,
                $booking->total_price,
                $booking->payment_status,
                $booking->created_at,
            ]);
        }
        
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}