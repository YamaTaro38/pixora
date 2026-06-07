<?php
// app/Http/Controllers/Admin/LoginController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }
    
    public function login(Request $request)
    {
        // Validasi input dengan pesan error
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            // Cek apakah user adalah admin
            if (Auth::user()->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
            }
            
            // Jika bukan admin, logout
            Auth::logout();
            return back()->withErrors(['email' => 'Akun Anda tidak memiliki akses admin.'])
                ->withInput();
        }
        
        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->withInput();
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')
            ->with('success', 'Anda telah berhasil logout.');
    }
}