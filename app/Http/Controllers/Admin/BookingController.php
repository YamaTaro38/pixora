<?php
// app/Http/Controllers/Admin/BookingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Package;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::with('package');
        
        if ($request->status && $request->status != 'all') {
            $query->where('payment_status', $request->status);
        }
        
        if ($request->date_from) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('booking_code', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $request->search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $request->search . '%');
            });
        }
        
        $bookings = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $statusCounts = [
            'all' => Booking::count(),
            'pending' => Booking::where('payment_status', 'pending')->count(),
            'lunas' => Booking::where('payment_status', 'lunas')->count(),
            'dp_paid' => Booking::where('payment_status', 'dp_paid')->count(),
            'expired' => Booking::where('payment_status', 'expired')->count(),
        ];
        
        return view('admin.bookings.index', compact('bookings', 'statusCounts'));
    }
    
    public function show($id)
    {
        $booking = Booking::with('package')->findOrFail($id);
        return view('admin.bookings.show', compact('booking'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        
        $request->validate([
            'payment_status' => 'required|in:pending,lunas,dp_paid,expired,cancelled',
            'session_status' => 'required|in:upcoming,ongoing,completed,cancelled',
        ]);
        
        $booking->payment_status = $request->payment_status;
        $booking->session_status = $request->session_status;
        
        if ($request->payment_status == 'lunas') {
            $booking->booking_status = 'confirmed';
            $booking->paid_at = now();
        }
        
        $booking->save();
        
        return redirect()->back()->with('success', 'Status booking berhasil diupdate.');
    }
    
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();
        
        return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil dihapus.');
    }
}