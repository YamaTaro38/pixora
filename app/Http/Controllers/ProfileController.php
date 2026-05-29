<?php
// app/Http/Controllers/ProfileController.php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        // Jika admin, redirect ke dashboard admin
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)
            ->orWhere('customer_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('profile.index', compact('user', 'bookings'));
    }
    
    public function update(Request $request)
    {
        // Jika admin, redirect ke dashboard admin
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);
        
        return back()->with('success', 'Profil berhasil diupdate.');
    }
    
    public function updatePassword(Request $request)
    {
        // Jika admin, redirect ke dashboard admin
        if (Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        
        $user = Auth::user();
        
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);
        
        return back()->with('success', 'Password berhasil diubah.');
    }
}