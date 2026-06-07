<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // Show login page
    public function showLogin()
    {
        return view('auth.login');
    }
    
    // Show register page
    public function showRegister()
    {
        return view('auth.register');
    }
    
    // Process login — support both Web (redirect) and API (JSON + Sanctum token)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        
        // ===== API REQUEST (Flutter) =====
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Email atau password salah.',
                ], 401);
            }

            $user = Auth::user();

            // Revoke old tokens lalu buat yang baru
            $user->tokens()->delete();
            $token = $user->createToken('pixora-mobile')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
            ]);
        }

        // ===== WEB REQUEST (Browser) =====
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            if (Auth::user()->isAdmin()) {
                return redirect()->intended('/admin/dashboard');
            }
            
            return redirect()->intended('/');
        }
        
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput();
    }
    
    // Process register — support both Web and API
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);
        
        // ===== API REQUEST (Flutter) =====
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role' => 'customer',
                'is_active' => true,
            ]);

            $token = $user->createToken('pixora-mobile')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
            ]);
        }

        // ===== WEB REQUEST (Browser) =====
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => 'customer',
            'is_active' => true,
        ]);
        
        Auth::login($user);
        
        return redirect('/')->with('success', 'Registrasi berhasil! Selamat datang ' . $user->name);
    }
    
    // Logout — support both Web and API
    public function logout(Request $request)
    {
        // ===== API REQUEST (Flutter) =====
        if ($request->expectsJson() || $request->is('api/*')) {
            // Revoke token saat ini
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => 'Logout berhasil',
            ]);
        }

        // ===== WEB REQUEST (Browser) =====
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }

    // Get user profile (API only)
    public function profile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    // Update profile (API only)
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diupdate',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'role' => $user->role,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    // Change password (API only)
    public function changePassword(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password saat ini salah',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah',
        ]);
    }

    // Delete account (API only)
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Revoke all tokens
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Akun berhasil dihapus',
        ]);
    }

    // Redirect to Google
    public function redirectToGoogle()
    {
        // Validasi Google credentials sebelum redirect
        $clientId = config('services.google.client_id');
        if (empty($clientId) || $clientId === 'YOUR_GOOGLE_CLIENT_ID') {
            return redirect('/login')->with('error', 'Google Sign-In belum dikonfigurasi. Silakan hubungi administrator untuk menambahkan Google Client ID & Secret di file .env.');
        }

        return Socialite::driver('google')->redirect();
    }
    
    // Callback from Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Cari user berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();
            
            if (!$user) {
                // Buat user baru
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'role' => 'customer',
                    'is_active' => true,
                ]);
            } else {
                // Update token jika user sudah ada
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }
            
            Auth::login($user);
            
            return redirect('/')->with('success', 'Login berhasil! Selamat datang ' . $user->name);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google login error (web): ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect('/login')->with('error', 'Login dengan Google gagal: ' . $e->getMessage());
        }
    }

    // Google Sign-In via API (Mobile) — accepts Google access token from Flutter
    public function googleSignInApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->google_token);

            // Cari user berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                // Buat user baru
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'role' => 'customer',
                    'is_active' => true,
                ]);
            } else {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            // Revoke old tokens then create new one
            $user->tokens()->delete();
            $token = $user->createToken('pixora-mobile')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                    'is_active' => $user->is_active,
                ],
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google login error (API): ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Login dengan Google gagal: ' . $e->getMessage(),
            ], 401);
        }
    }

    // Forgot password
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // TODO: Implement actual password reset email
        return response()->json([
            'message' => 'Link reset password telah dikirim ke email Anda',
        ]);
    }

    // Reset password
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        // TODO: Implement actual password reset
        return response()->json([
            'message' => 'Password berhasil direset',
        ]);
    }
}