<?php
// database/seeders/PackageSeeder.php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackageSeeder extends Seeder
{
    public function run()
    {
        // Data paket fotografi
        $packages = [
            [
                'name' => 'Basic Wedding',
                'description' => 'Paket dokumentasi pernikahan yang lengkap untuk hari bahagia Anda. Termasuk foto pre-wedding singkat dan dokumentasi akad serta resepsi.',
                'price' => 3500000,
                'down_payment' => 1000000,
                'duration_hours' => 4,
                'edited_photos' => 50,
                'location_type' => 'both',
                'inclusions' => [
                    '1 Fotografer professional',
                    '1 Asisten fotografer',
                    'Dokumentasi akad dan resepsi',
                    '50 foto hasil edit',
                    'Soft file via Google Drive',
                    'Cetak 10x15 sebanyak 20 lembar'
                ],
                'thumbnail' => '/storage/packages/basic-wedding.jpg',
                'is_active' => true,
                'sort_order' => 1
            ],
            [
                'name' => 'Prewedding Outdoor',
                'description' => 'Sesi prewedding dengan konsep outdoor yang romantis. Cocok untuk pasangan yang ingin foto dengan latar alam atau kota.',
                'price' => 4200000,
                'down_payment' => 1500000,
                'duration_hours' => 5,
                'edited_photos' => 70,
                'location_type' => 'outdoor',
                'inclusions' => [
                    '1 Fotografer senior',
                    '1 Asisten fotografer',
                    'Makeup & hairstyle untuk 1 orang',
                    '70 foto hasil edit premium',
                    'Album cetak 20x30 sebanyak 20 lembar',
                    'Soft file via Google Drive',
                    'Free 1 frame besar'
                ],
                'thumbnail' => '/storage/packages/prewedding-outdoor.jpg',
                'is_active' => true,
                'sort_order' => 2
            ],
            [
                'name' => 'Prewedding Indoor',
                'description' => 'Sesi prewedding dengan konsep indoor di studio kami. Tersedia berbagai background dan properti.',
                'price' => 3800000,
                'down_payment' => 1000000,
                'duration_hours' => 4,
                'edited_photos' => 60,
                'location_type' => 'studio',
                'inclusions' => [
                    '1 Fotografer professional',
                    'Studio dengan 3 background',
                    'Properti foto sesuai tema',
                    '60 foto hasil edit',
                    'Soft file via Google Drive',
                    'Cetak 15x20 sebanyak 15 lembar'
                ],
                'thumbnail' => '/storage/packages/prewedding-indoor.jpg',
                'is_active' => true,
                'sort_order' => 3
            ],
            [
                'name' => 'Paket Keluarga',
                'description' => 'Sesi foto keluarga yang hangat dan berkesan. Cocok untuk dokumentasi momen kebersamaan keluarga tercinta.',
                'price' => 1800000,
                'down_payment' => 500000,
                'duration_hours' => 2,
                'edited_photos' => 30,
                'location_type' => 'studio',
                'inclusions' => [
                    '1 Fotografer professional',
                    'Studio dengan tema keluarga',
                    '30 foto hasil edit',
                    'Soft file via Google Drive',
                    'Cetak 10x15 sebanyak 10 lembar'
                ],
                'thumbnail' => '/storage/packages/family.jpg',
                'is_active' => true,
                'sort_order' => 4
            ],
            [
                'name' => 'Portrait Professional',
                'description' => 'Sesi foto portrait untuk personal branding, modeling, atau keperluan profesional lainnya.',
                'price' => 1200000,
                'down_payment' => 500000,
                'duration_hours' => 1.5,
                'edited_photos' => 20,
                'location_type' => 'studio',
                'inclusions' => [
                    '1 Fotografer professional',
                    'Studio dengan lighting professional',
                    '20 foto hasil edit',
                    'Soft file via Google Drive',
                    '2 look berbeda'
                ],
                'thumbnail' => '/storage/packages/portrait.jpg',
                'is_active' => true,
                'sort_order' => 5
            ],
            [
                'name' => 'Maternity Shoot',
                'description' => 'Sesi foto khusus untuk ibu hamil. Abadikan momen indah kehamilan Anda dengan konsep yang hangat dan natural.',
                'price' => 2200000,
                'down_payment' => 750000,
                'duration_hours' => 2.5,
                'edited_photos' => 35,
                'location_type' => 'both',
                'inclusions' => [
                    '1 Fotografer professional',
                    'Makeup & hairstyle untuk 1 orang',
                    '35 foto hasil edit',
                    'Soft file via Google Drive',
                    'Album cetak 15x20 sebanyak 15 lembar',
                    'Free 1 frame'
                ],
                'thumbnail' => '/storage/packages/maternity.jpg',
                'is_active' => true,
                'sort_order' => 6
            ],
            [
                'name' => 'Graduation',
                'description' => 'Abadikan momen kelulusan Anda dengan foto toga yang profesional. Tersedia kostum toga dan properti.',
                'price' => 1500000,
                'down_payment' => 500000,
                'duration_hours' => 2,
                'edited_photos' => 25,
                'location_type' => 'both',
                'inclusions' => [
                    '1 Fotografer professional',
                    'Kostum toga dan properti',
                    '25 foto hasil edit',
                    'Soft file via Google Drive',
                    'Cetak 10x15 sebanyak 10 lembar'
                ],
                'thumbnail' => '/storage/packages/graduation.jpg',
                'is_active' => true,
                'sort_order' => 7
            ],
            [
                'name' => 'Couple / Anniversary',
                'description' => 'Sesi foto romantis untuk pasangan, baik untuk anniversary, pre-proposal, atau dokumentasi kebersamaan.',
                'price' => 2000000,
                'down_payment' => 750000,
                'duration_hours' => 2.5,
                'edited_photos' => 40,
                'location_type' => 'both',
                'inclusions' => [
                    '1 Fotografer professional',
                    '40 foto hasil edit premium',
                    'Soft file via Google Drive',
                    'Album cetak 15x20 sebanyak 10 lembar',
                    'Free konsep foto sesuai request'
                ],
                'thumbnail' => '/storage/packages/couple.jpg',
                'is_active' => true,
                'sort_order' => 8
            ],
            [
                'name' => 'Personal Branding',
                'description' => 'Paket eksklusif untuk profesional, influencer, atau pebisnis yang ingin membangun personal branding.',
                'price' => 2800000,
                'down_payment' => 1000000,
                'duration_hours' => 3,
                'edited_photos' => 50,
                'location_type' => 'both',
                'inclusions' => [
                    '1 Fotografer senior',
                    '1 Asisten fotografer',
                    'Makeup & hairstyle untuk 1 orang',
                    '50 foto hasil edit premium',
                    'Soft file via Google Drive',
                    'Free konsultasi konsep',
                    'Bebas ganti outfit 3 look'
                ],
                'thumbnail' => '/storage/packages/personal-branding.jpg',
                'is_active' => true,
                'sort_order' => 9
            ]
        ];
        
        // Insert data ke database
        foreach ($packages as $data) {
            // Generate slug dari nama
            $data['slug'] = Str::slug($data['name']);
            
            // Convert inclusions dari array ke JSON
            $data['inclusions'] = json_encode($data['inclusions']);
            
            // Cek apakah package sudah ada (by slug) untuk menghindari duplikat
            Package::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
        
        $this->command->info('PackageSeeder: ' . count($packages) . ' packages seeded successfully!');
    }
}