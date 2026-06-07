<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;
use App\Models\PoseReference;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Admin User
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pixora.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        
        // Sample Customer
        User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@pixora.com',
            'password' => Hash::make('customer123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $settings = [
            // Hero Section
            ['key' => 'hero_title', 'value' => 'Abadikan Momen Terbaik Anda', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_subtitle', 'value' => 'Studio fotografi modern dengan sentuhan AI untuk hasil yang sempurna', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_bg_image', 'value' => 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1600', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_button_text', 'value' => 'Lihat Paket', 'type' => 'text', 'group' => 'hero'],
            ['key' => 'hero_button_link', 'value' => '/paket', 'type' => 'text', 'group' => 'hero'],
            
            // About Section
            ['key' => 'about_title', 'value' => 'Tentang Pixora', 'type' => 'text', 'group' => 'about'],
            ['key' => 'about_description', 'value' => 'Pixora adalah studio fotografi profesional yang berdedikasi untuk mengabadikan momen-momen berharga dalam hidup Anda. Dengan tim fotografer berpengalaman dan peralatan modern, kami siap memberikan hasil terbaik untuk setiap sesi foto.', 'type' => 'textarea', 'group' => 'about'],
            ['key' => 'about_image', 'value' => 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=600', 'type' => 'text', 'group' => 'about'],
            
            // Studio Info
            ['key' => 'studio_name', 'value' => 'Pixora Studio', 'type' => 'text', 'group' => 'studio'],
            ['key' => 'studio_address', 'value' => 'Jakarta, Indonesia', 'type' => 'text', 'group' => 'studio'],
            ['key' => 'studio_phone', 'value' => '+62 812-3456-7890', 'type' => 'text', 'group' => 'studio'],
            ['key' => 'studio_email', 'value' => 'hello@pixora.com', 'type' => 'text', 'group' => 'studio'],
            
            // Social Media
            ['key' => 'instagram', 'value' => '@pixora.studio', 'type' => 'text', 'group' => 'social'],
            ['key' => 'facebook', 'value' => 'pixora.studio', 'type' => 'text', 'group' => 'social'],
            ['key' => 'tiktok', 'value' => '@pixora', 'type' => 'text', 'group' => 'social'],
            ['key' => 'youtube', 'value' => 'pixora', 'type' => 'text', 'group' => 'social'],
            
            // Operational Hours
            ['key' => 'open_time', 'value' => '08:00', 'type' => 'time', 'group' => 'operational'],
            ['key' => 'close_time', 'value' => '20:00', 'type' => 'time', 'group' => 'operational'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta', 'type' => 'text', 'group' => 'operational'],
            
            // Policies
            ['key' => 'cancellation_policy', 'value' => 'Pembatalan maksimal H-7 untuk refund DP', 'type' => 'textarea', 'group' => 'policy'],
            ['key' => 'reschedule_policy', 'value' => 'Reschedule maksimal H-3 sebelum sesi', 'type' => 'textarea', 'group' => 'policy'],
            
            // Features
            ['key' => 'features', 'value' => json_encode([
                ['icon' => 'fa-camera', 'title' => 'Fotografer Profesional', 'description' => 'Tim berpengalaman dengan portofolio terbaik'],
                ['icon' => 'fa-robot', 'title' => 'AI Powered', 'description' => 'Teknologi AI untuk hasil maksimal'],
                ['icon' => 'fa-clock', 'title' => 'Booking Mudah', 'description' => 'Sistem booking online real-time']
            ]), 'type' => 'json', 'group' => 'content'],
            
            // Gallery
            ['key' => 'gallery_images', 'value' => json_encode([
                'https://images.unsplash.com/photo-1519741497674-611481863552?w=600',
                'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600',
                'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600',
                'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=600',
                'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=600',
                'https://images.unsplash.com/photo-1504208434309-cb69f4fe52b0?w=600'
            ]), 'type' => 'json', 'group' => 'content'],
            
            // Testimonials
            ['key' => 'testimonials', 'value' => json_encode([
                ['name' => 'Andi & Siska', 'text' => 'Hasil fotonya luar biasa! Tim Pixora sangat profesional dan ramah. Recomended!', 'rating' => 5],
                ['name' => 'Budi', 'text' => 'Proses booking mudah, AI pose generator-nya sangat membantu kami yang bingung mau pose apa.', 'rating' => 5],
                ['name' => 'Citra', 'text' => 'Fotonya aesthetic banget! Harga terjangkau dengan hasil premium.', 'rating' => 5]
            ]), 'type' => 'json', 'group' => 'content'],
            
            // Footer
            ['key' => 'footer_copyright', 'value' => '© 2024 Pixora Studio. All rights reserved.', 'type' => 'text', 'group' => 'footer'],
        ];
        
        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Seed Packages
        $packages = [
            [
                'name' => 'Basic Wedding',
                'slug' => 'basic-wedding',
                'description' => 'Paket wedding lengkap untuk hari bahagia Anda',
                'price' => 3500000,
                'down_payment' => 1000000,
                'duration_hours' => 4,
                'edited_photos' => 50,
                'location_type' => 'both',
                'inclusions' => json_encode(['2 fotografer', '50 foto edit', 'Soft file']),
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Prewedding Outdoor',
                'slug' => 'prewedding-outdoor',
                'description' => 'Sesi prewedding dengan konsep outdoor',
                'price' => 4200000,
                'down_payment' => 1500000,
                'duration_hours' => 5,
                'edited_photos' => 70,
                'location_type' => 'outdoor',
                'inclusions' => json_encode(['1 fotografer', '70 foto edit', 'Album cetak']),
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Paket Keluarga',
                'slug' => 'paket-keluarga',
                'description' => 'Sesi foto keluarga yang hangat',
                'price' => 1800000,
                'down_payment' => 500000,
                'duration_hours' => 2,
                'edited_photos' => 30,
                'location_type' => 'studio',
                'inclusions' => json_encode(['1 fotografer', '30 foto edit', 'Soft file']),
                'is_active' => true,
                'sort_order' => 3
            ]
        ];
        
        foreach ($packages as $data) {
            Package::updateOrCreate(['slug' => $data['slug']], $data);
        }
        
        $this->command->info('Packages seeded successfully!');
        
        // Seed Pose References
        $poses = [
            ['category' => 'wedding', 'title' => 'Romantic Couple', 'image_url' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=500', 'source' => 'local'],
            ['category' => 'prewedding', 'title' => 'Sunset Silhouette', 'image_url' => 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=500', 'source' => 'local'],
            ['category' => 'family', 'title' => 'Happy Family', 'image_url' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=500', 'source' => 'local'],
            ['category' => 'portrait', 'title' => 'Elegant Portrait', 'image_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=500', 'source' => 'local'],
        ];
        
        foreach ($poses as $pose) {
            PoseReference::updateOrCreate(
                ['category' => $pose['category'], 'title' => $pose['title']],
                $pose
            );
        }
        
        $this->command->info('Pose references seeded successfully!');
    }
}