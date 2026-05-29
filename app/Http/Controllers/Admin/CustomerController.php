<?php
// app/Http/Controllers/Admin/CustomerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->status == 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($request->status == 'active') {
            $query->where('is_blocked', false)->where('is_active', true);
        }
        
        $customers = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total' => User::where('role', 'customer')->count(),
            'active' => User::where('role', 'customer')->where('is_blocked', false)->where('is_active', true)->count(),
            'blocked' => User::where('role', 'customer')->where('is_blocked', true)->count(),
        ];
        
        return view('admin.customers.index', compact('customers', 'stats'));
    }
    
    public function toggleBlock($id)
    {
        $customer = User::findOrFail($id);
        
        if ($customer->is_blocked) {
            $customer->is_blocked = false;
            $customer->blocked_at = null;
            $customer->block_reason = null;
            $message = 'Customer berhasil diunblock';
        } else {
            $customer->is_blocked = true;
            $customer->blocked_at = now();
            $customer->block_reason = request('reason', 'Diblokir oleh admin');
            $message = 'Customer berhasil diblokir';
        }
        
        $customer->save();
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'is_blocked' => $customer->is_blocked
        ]);
    }
    
    public function destroy($id)
    {
        $customer = User::findOrFail($id);
        
        // Hapus semua booking customer
        Booking::where('user_id', $customer->id)->delete();
        $customer->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil dihapus'
        ]);
    }
    
    public function show($id)
    {
        $customer = User::findOrFail($id);
        $bookings = Booking::where('user_id', $customer->id)
            ->orWhere('customer_email', $customer->email)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('admin.customers.show', compact('customer', 'bookings'));
    }
}