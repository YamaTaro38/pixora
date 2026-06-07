<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PoseReference;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // ========== HERO SECTION ==========
        $hero_title = Setting::get('hero_title', 'Abadikan Momen Terbaik Anda');
        $hero_subtitle = Setting::get('hero_subtitle', 'Studio fotografi modern dengan sentuhan AI untuk hasil yang sempurna');
        $hero_bg_image = Setting::get('hero_bg_image', 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1600');
        $hero_button_text = Setting::get('hero_button_text', 'Lihat Paket');
        $hero_button_link = Setting::get('hero_button_link', '/paket');
        $cta_icon = Setting::get('cta_icon', 'fa-calendar-check');
        
        // ========== STATS CARDS ==========
        $stat1_icon = Setting::get('stat1_icon', 'fa-smile');
        $stat1_value = Setting::get('stat1_value', '500+');
        $stat1_label = Setting::get('stat1_label', 'Klien Puas');
        
        $stat2_icon = Setting::get('stat2_icon', 'fa-camera-retro');
        $stat2_value = Setting::get('stat2_value', '1000+');
        $stat2_label = Setting::get('stat2_label', 'Sesi Foto');
        
        $stat3_icon = Setting::get('stat3_icon', 'fa-images');
        $stat3_value = Setting::get('stat3_value', '50+');
        $stat3_label = Setting::get('stat3_label', 'Portofolio');
        
        $stat4_icon = Setting::get('stat4_icon', 'fa-star');
        $stat4_value = Setting::get('stat4_value', '4.9');
        $stat4_label = Setting::get('stat4_label', 'Rating');
        
        // ========== ABOUT SECTION ==========
        $about_title = Setting::get('about_title', 'Tentang Pixora');
        $about_description = Setting::get('about_description', 'Pixora adalah studio fotografi profesional yang berdedikasi untuk mengabadikan momen-momen berharga dalam hidup Anda.');
        $about_image = Setting::get('about_image', 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=600');
        
        // About Stats
        $about_year = Setting::get('about_year', '2018');
        $about_year_label = Setting::get('about_year_label', 'Berdiri Sejak');
        $about_projects = Setting::get('about_projects', '1000+');
        $about_projects_label = Setting::get('about_projects_label', 'Project Selesai');
        $about_happy = Setting::get('about_happy', '500+');
        $about_happy_label = Setting::get('about_happy_label', 'Klien Bahagia');
        
        // ========== FEATURES SECTION ==========
        $features = json_decode(Setting::get('features', json_encode([
            ['icon' => 'fa-camera', 'title' => 'Fotografer Profesional', 'description' => 'Tim berpengalaman dengan portofolio terbaik'],
            ['icon' => 'fa-robot', 'title' => 'AI Powered', 'description' => 'Teknologi AI untuk hasil maksimal'],
            ['icon' => 'fa-clock', 'title' => 'Booking Mudah', 'description' => 'Sistem booking online real-time']
        ])), true);
        
        // ========== GALLERY SECTION ==========
        $gallery_images = json_decode(Setting::get('gallery_images', json_encode([
            'https://images.unsplash.com/photo-1519741497674-611481863552?w=600',
            'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600',
            'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600'
        ])), true);
        
        // ========== TESTIMONIALS SECTION ==========
        $testimonials = json_decode(Setting::get('testimonials', json_encode([
            ['name' => 'Andi & Siska', 'text' => 'Hasil fotonya luar biasa!', 'rating' => 5, 'photo' => null],
            ['name' => 'Budi', 'text' => 'Proses booking mudah!', 'rating' => 5, 'photo' => null],
            ['name' => 'Citra', 'text' => 'Fotonya aesthetic banget!', 'rating' => 5, 'photo' => null]
        ])), true);
        
        // ========== CTA SECTION ==========
        $cta_title = Setting::get('cta_title', 'Siap Mengabadikan Momen Anda?');
        $cta_subtitle = Setting::get('cta_subtitle', 'Booking sekarang dan dapatkan pengalaman fotografi terbaik bersama Pixora');
        $cta_button_text = Setting::get('cta_button_text', 'Booking Sekarang');
        $cta_bg_image = Setting::get('cta_bg_image', 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=1920');
        
        // ========== FOOTER ==========
        $studio_name = Setting::get('studio_name', 'Pixora');
        $studio_address = Setting::get('studio_address', 'Jakarta, Indonesia');
        $studio_phone = Setting::get('studio_phone', '+62 812-3456-7890');
        $studio_email = Setting::get('studio_email', 'hello@pixora.com');
        $instagram = Setting::get('instagram', '@pixora.studio');
        $facebook = Setting::get('facebook', 'pixora.studio');
        $tiktok = Setting::get('tiktok', '@pixora');
        $youtube = Setting::get('youtube', 'pixora');
        $open_time = Setting::get('open_time', '08:00');
        $close_time = Setting::get('close_time', '20:00');
        $footer_copyright = Setting::get('footer_copyright', '© 2024 Pixora Studio. All rights reserved.');
        
        // ========== PACKAGES ==========
        $packages = Package::where('is_active', true)->orderBy('sort_order')->take(3)->get();
        $popularPoses = PoseReference::where('is_active', true)->inRandomOrder()->take(8)->get();
        
        return view('home', compact(
            // Hero
            'hero_title', 'hero_subtitle', 'hero_bg_image', 'hero_button_text', 'hero_button_link', 'cta_icon',
            // Stats
            'stat1_icon', 'stat1_value', 'stat1_label',
            'stat2_icon', 'stat2_value', 'stat2_label',
            'stat3_icon', 'stat3_value', 'stat3_label',
            'stat4_icon', 'stat4_value', 'stat4_label',
            // About
            'about_title', 'about_description', 'about_image',
            'about_year', 'about_year_label', 'about_projects', 'about_projects_label',
            'about_happy', 'about_happy_label',
            // Features
            'features',
            // Gallery
            'gallery_images',
            // Testimonials
            'testimonials',
            // CTA
            'cta_title', 'cta_subtitle', 'cta_button_text', 'cta_bg_image',
            // Footer
            'studio_name', 'studio_address', 'studio_phone', 'studio_email',
            'instagram', 'facebook', 'tiktok', 'youtube',
            'open_time', 'close_time', 'footer_copyright',
            // Packages
            'packages', 'popularPoses'
        ));
    }
    
    public function packages()
    {
        $packages = Package::query()->where('is_active', true)->orderBy('sort_order')->paginate(9);
    return view('packages', compact('packages'));
        return view('packages', compact('packages'));
    }
    
    public function packageDetail($slug)
    {
        $package = Package::query()->where('slug', $slug)->where('is_active', true)->firstOrFail();
        return view('package-detail', compact('package'));
    }
    
    public function poseGenerator()
    {
        $categories = PoseReference::getCategories();
        $poses = PoseReference::query()->where('is_active', true)->orderBy('sort_order')->get();
        return view('pose-generator', compact('categories', 'poses'));
    }
}