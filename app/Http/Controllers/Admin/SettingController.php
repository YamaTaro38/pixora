<?php
// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'studio_name' => Setting::get('studio_name', 'Pixora Studio'),
            'studio_address' => Setting::get('studio_address', 'Jakarta, Indonesia'),
            'studio_phone' => Setting::get('studio_phone', '+62 812-3456-7890'),
            'studio_email' => Setting::get('studio_email', 'hello@pixora.com'),
            'instagram' => Setting::get('instagram', '@pixora.studio'),
            'facebook' => Setting::get('facebook', 'pixora.studio'),
            'tiktok' => Setting::get('tiktok', '@pixora'),
            'youtube' => Setting::get('youtube', 'pixora'),
            'open_time' => Setting::get('open_time', '08:00'),
            'close_time' => Setting::get('close_time', '20:00'),
            'timezone' => Setting::get('timezone', 'Asia/Jakarta'),
            'cancellation_policy' => Setting::get('cancellation_policy', 'Pembatalan maksimal H-7 untuk refund DP'),
            'reschedule_policy' => Setting::get('reschedule_policy', 'Reschedule maksimal H-3 sebelum sesi'),
        ];
        
        return view('admin.settings.index', compact('settings'));
    }
    
    public function update(Request $request)
    {
        foreach ($request->except(['_token', '_method']) as $key => $value) {
            Setting::set($key, $value);
        }
        
        return redirect()->back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}