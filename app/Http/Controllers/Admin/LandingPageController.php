<?php
// app/Http/Controllers/Admin/LandingPageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class LandingPageController extends Controller
{
    public function index()
    {
        // ========== HERO SECTION ==========
        $hero_title = Setting::get('hero_title', 'Abadikan Momen Terbaik Anda');
        $hero_subtitle = Setting::get('hero_subtitle', 'Studio fotografi modern dengan sentuhan AI untuk hasil yang sempurna');
        $hero_bg_image = Setting::get('hero_bg_image', null);
        $hero_button_text = Setting::get('hero_button_text', 'Lihat Paket');
        $hero_button_link = Setting::get('hero_button_link', '/paket');
        $cta_icon = Setting::get('cta_icon', 'fa-calendar-check');

        // ========== STATS CARDS (HERO) ==========
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
        $about_description = Setting::get('about_description', 'Pixora adalah studio fotografi profesional yang berdedikasi untuk mengabadikan momen-momen berharga dalam hidup Anda. Dengan tim fotografer berpengalaman dan peralatan modern, kami siap memberikan hasil terbaik untuk setiap sesi foto.');
        $about_image = Setting::get('about_image', null);

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
            'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600',
            'https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=600',
            'https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=600',
            'https://images.unsplash.com/photo-1504208434309-cb69f4fe52b0?w=600'
        ])), true);

        // ========== TESTIMONIALS SECTION ==========
        $testimonials = json_decode(Setting::get('testimonials', json_encode([
            ['name' => 'Andi & Siska', 'text' => 'Hasil fotonya luar biasa! Tim Pixora sangat profesional dan ramah. Recomended!', 'rating' => 5, 'photo' => null],
            ['name' => 'Budi', 'text' => 'Proses booking mudah, AI pose generator-nya sangat membantu kami yang bingung mau pose apa.', 'rating' => 5, 'photo' => null],
            ['name' => 'Citra', 'text' => 'Fotonya aesthetic banget! Harga terjangkau dengan hasil premium.', 'rating' => 5, 'photo' => null]
        ])), true);

        // ========== CTA SECTION ==========
        $cta_title = Setting::get('cta_title', 'Siap Mengabadikan Momen Anda?');
        $cta_subtitle = Setting::get('cta_subtitle', 'Booking sekarang dan dapatkan pengalaman fotografi terbaik bersama Pixora');
        $cta_button_text = Setting::get('cta_button_text', 'Booking Sekarang');
        $cta_button_link = Setting::get('cta_button_link', route('calendar'));
        $cta_bg_image = Setting::get('cta_bg_image', null);

        // ========== FOOTER SECTION ==========
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

        // ========== COMPACT ALL DATA ==========
        $contents = compact(
            // Hero
            'hero_title',
            'hero_subtitle',
            'hero_bg_image',
            'hero_button_text',
            'hero_button_link',
            'cta_icon',
            // Stats
            'stat1_icon',
            'stat1_value',
            'stat1_label',
            'stat2_icon',
            'stat2_value',
            'stat2_label',
            'stat3_icon',
            'stat3_value',
            'stat3_label',
            'stat4_icon',
            'stat4_value',
            'stat4_label',
            // About
            'about_title',
            'about_description',
            'about_image',
            'about_year',
            'about_year_label',
            'about_projects',
            'about_projects_label',
            'about_happy',
            'about_happy_label',
            // Features
            'features',
            // Gallery
            'gallery_images',
            // Testimonials
            'testimonials',
            // CTA
            'cta_title',
            'cta_subtitle',
            'cta_button_text',
            'cta_button_link',
            'cta_bg_image',
            // Footer
            'studio_name',
            'studio_address',
            'studio_phone',
            'studio_email',
            'instagram',
            'facebook',
            'tiktok',
            'youtube',
            'open_time',
            'close_time',
            'footer_copyright'
        );

        return view('admin.landing-page.index', compact('contents'));
    }

    public function updateHero(Request $request)
    {
        // Hero Text
        Setting::set('hero_title', $request->hero_title);
        Setting::set('hero_subtitle', $request->hero_subtitle);

        // Stats Cards - Simpan semua nilai
        Setting::set('stat1_icon', $request->stat1_icon ?? 'fa-smile');
        Setting::set('stat1_value', $request->stat1_value ?? '500+');
        Setting::set('stat1_label', $request->stat1_label ?? 'Klien Puas');

        Setting::set('stat2_icon', $request->stat2_icon ?? 'fa-camera-retro');
        Setting::set('stat2_value', $request->stat2_value ?? '1000+');
        Setting::set('stat2_label', $request->stat2_label ?? 'Sesi Foto');

        Setting::set('stat3_icon', $request->stat3_icon ?? 'fa-images');
        Setting::set('stat3_value', $request->stat3_value ?? '50+');
        Setting::set('stat3_label', $request->stat3_label ?? 'Portofolio');

        Setting::set('stat4_icon', $request->stat4_icon ?? 'fa-star');
        Setting::set('stat4_value', $request->stat4_value ?? '4.9');
        Setting::set('stat4_label', $request->stat4_label ?? 'Rating');

        // CTA Button
        Setting::set('hero_button_text', $request->hero_button_text);
        Setting::set('hero_button_link', $request->hero_button_link);
        Setting::set('cta_icon', $request->cta_icon ?? 'fa-calendar-check');

        // Hero Background Image
        if ($request->hasFile('hero_bg_image')) {
            $oldImage = Setting::get('hero_bg_image');
            if ($oldImage && $oldImage != '/images/hero-bg.jpg') {
                $oldPath = str_replace('/storage/', '', $oldImage);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('hero_bg_image')->store('landing-page', 'public');
            Setting::set('hero_bg_image', '/storage/' . $path);
        }

        return redirect()->back()->with('success', 'Hero section berhasil diupdate.');
    }

    public function updateAbout(Request $request)
    {
        Setting::set('about_title', $request->about_title);
        Setting::set('about_description', $request->about_description);
        Setting::set('about_year', $request->about_year);
        Setting::set('about_year_label', $request->about_year_label);
        Setting::set('about_projects', $request->about_projects);
        Setting::set('about_projects_label', $request->about_projects_label);
        Setting::set('about_happy', $request->about_happy);
        Setting::set('about_happy_label', $request->about_happy_label);

        if ($request->hasFile('about_image')) {
            $oldImage = Setting::get('about_image');
            if ($oldImage) {
                $oldPath = str_replace('/storage/', '', $oldImage);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('about_image')->store('landing-page', 'public');
            Setting::set('about_image', '/storage/' . $path);
        }

        return redirect()->back()->with('success', 'About section berhasil diupdate.');
    }

    public function updateTestimonials(Request $request)
    {
        $testimonials = [];
        if ($request->has('testimonial_name')) {
            for ($i = 0; $i < count($request->testimonial_name); $i++) {
                if (!empty($request->testimonial_name[$i])) {
                    $testimonials[] = [
                        'name' => $request->testimonial_name[$i],
                        'text' => $request->testimonial_text[$i],
                        'rating' => (int)$request->testimonial_rating[$i],
                        'photo' => $request->testimonial_photo[$i] ?? null
                    ];
                }
            }
        }

        Setting::set('testimonials', json_encode($testimonials));

        return redirect()->back()->with('success', count($testimonials) . ' testimonial berhasil diupdate.');
    }


    public function uploadTestimonialPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $path = $request->file('photo')->store('testimonials', 'public');
        $photoUrl = '/storage/' . $path;

        return response()->json(['success' => true, 'photo_url' => $photoUrl]);
    }

    public function updateFeatures(Request $request)
    {
        Log::info('Update Features Request:', $request->all());

        // Cek dari form input biasa
        if ($request->has('feature_icon')) {
            $features = [];
            $icons = $request->feature_icon;
            $titles = $request->feature_title;
            $descriptions = $request->feature_description;

            for ($i = 0; $i < count($icons); $i++) {
                if (!empty($titles[$i])) {
                    $features[] = [
                        'icon' => $icons[$i] ?? 'fa-camera',
                        'title' => $titles[$i] ?? 'Fitur ' . ($i + 1),
                        'description' => $descriptions[$i] ?? 'Deskripsi fitur'
                    ];
                }
            }

            if (count($features) > 0) {
                Setting::set('features', json_encode($features));
                return redirect()->back()->with('success', count($features) . ' fitur berhasil diupdate.');
            }
        }

        return redirect()->back()->with('error', 'Tidak ada data fitur yang diterima.');
    }

    public function updateGallery(Request $request)
    {
        $galleryImages = [];

        // Proses gambar yang sudah ada (link)
        if ($request->has('existing_gallery_images')) {
            $galleryImages = array_merge($galleryImages, array_filter($request->existing_gallery_images));
        }

        // Proses upload gambar baru
        if ($request->hasFile('new_gallery_images')) {
            foreach ($request->file('new_gallery_images') as $image) {
                if ($image->isValid()) {
                    $path = $image->store('gallery', 'public');
                    $galleryImages[] = '/storage/' . $path;
                }
            }
        }

        Setting::set('gallery_images', json_encode($galleryImages));

        return redirect()->back()->with('success', 'Gallery berhasil diupdate.');
    }

    public function deleteGalleryImage(Request $request)
    {
        $galleryImages = json_decode(Setting::get('gallery_images', json_encode([])), true);
        $index = $request->index;

        if (isset($galleryImages[$index])) {
            // Hapus file dari storage jika itu file upload
            $imagePath = str_replace('/storage/', '', $galleryImages[$index]);
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            array_splice($galleryImages, $index, 1);
            Setting::set('gallery_images', json_encode($galleryImages));
        }

        return response()->json(['success' => true]);
    }

    public function updateFooter(Request $request)
    {
        Setting::set('studio_name', $request->studio_name);
        Setting::set('studio_address', $request->studio_address);
        Setting::set('studio_phone', $request->studio_phone);
        Setting::set('studio_email', $request->studio_email);
        Setting::set('instagram', $request->instagram);
        Setting::set('facebook', $request->facebook);
        Setting::set('tiktok', $request->tiktok);
        Setting::set('youtube', $request->youtube);
        Setting::set('open_time', $request->open_time);
        Setting::set('close_time', $request->close_time);
        Setting::set('footer_copyright', $request->footer_copyright);

        return redirect()->back()->with('success', 'Footer berhasil diupdate.');
    }
}
