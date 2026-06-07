{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Pixora Studio - Abadikan Momen Terbaik dengan Gaya Modern')

@section('content')
<!-- Hero Section -->
<section class="relative h-screen min-h-[600px] flex items-center justify-center overflow-hidden">
    <div class="absolute inset-0 z-0">
        <img src="{{ $hero_bg_image ?? 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=1600&h=900&fit=crop' }}"
            alt="Photography Studio Hero"
            class="w-full h-full object-cover">
        <div class="absolute inset-0 hero-gradient"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10 text-center" data-aos="fade-up">
        <h1 class="text-5xl md:text-7xl font-display font-bold text-white mb-6 leading-tight">
            {{ $hero_title ?? 'Abadikan Momen Terbaik Anda' }}
        </h1>
        <p class="text-xl md:text-2xl text-gray-200 mb-8 max-w-2xl mx-auto">
            {{ $hero_subtitle ?? 'Studio fotografi modern dengan sentuhan AI untuk hasil yang sempurna' }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ $hero_button_link ?? '/paket' }}"
                class="group bg-gradient-to-r from-rose-500 to-pink-600 text-white px-8 py-4 rounded-full font-semibold hover:shadow-2xl transition-all inline-flex items-center gap-2">
                <i class="fas fa-camera"></i>
                <span>{{ $hero_button_text ?? 'Lihat Paket' }}</span>
                <i class="fas fa-arrow-right group-hover:translate-x-1 transition"></i>
            </a>
            <a href="{{ route('calendar') }}"
                class="glass text-white px-8 py-4 rounded-full font-semibold hover:bg-white/20 transition-all inline-flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i>
                <span>Booking Sekarang</span>
            </a>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mt-16 pt-8">
            <div class="stats-card rounded-2xl p-5 text-center" data-aos="fade-up" data-aos-delay="100">
                <i class="fas {{ $stat1_icon }} text-3xl text-rose-400 mb-2"></i>
                <div class="text-3xl font-bold text-white">{{ $stat1_value }}</div>
                <div class="text-gray-300 text-sm">{{ $stat1_label }}</div>
            </div>
            <div class="stats-card rounded-2xl p-5 text-center" data-aos="fade-up" data-aos-delay="200">
                <i class="fas {{ $stat2_icon }} text-3xl text-rose-400 mb-2"></i>
                <div class="text-3xl font-bold text-white">{{ $stat2_value }}</div>
                <div class="text-gray-300 text-sm">{{ $stat2_label }}</div>
            </div>
            <div class="stats-card rounded-2xl p-5 text-center" data-aos="fade-up" data-aos-delay="300">
                <i class="fas {{ $stat3_icon }} text-3xl text-rose-400 mb-2"></i>
                <div class="text-3xl font-bold text-white">{{ $stat3_value }}</div>
                <div class="text-gray-300 text-sm">{{ $stat3_label }}</div>
            </div>
            <div class="stats-card rounded-2xl p-5 text-center" data-aos="fade-up" data-aos-delay="400">
                <i class="fas {{ $stat4_icon }} text-3xl text-yellow-400 mb-2"></i>
                <div class="text-3xl font-bold text-white">{{ $stat4_value }}</div>
                <div class="text-gray-300 text-sm">{{ $stat4_label }}</div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
        <i class="fas fa-chevron-down text-white text-2xl"></i>
    </div>
</section>

<!-- About Section -->
<section class="py-20 container mx-auto px-4">
    <div class="grid md:grid-cols-2 gap-12 items-center">
        <div data-aos="fade-right">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-gray-800 mb-4">
                {{ $about_title }}
            </h2>
            <p class="text-gray-600 leading-relaxed mb-6">
                {{ $about_description }}
            </p>
            <div class="flex gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-rose-600">{{ $about_year }}</div>
                    <div class="text-sm text-gray-500">{{ $about_year_label }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-rose-600">{{ $about_projects }}</div>
                    <div class="text-sm text-gray-500">{{ $about_projects_label }}</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-rose-600">{{ $about_happy }}</div>
                    <div class="text-sm text-gray-500">{{ $about_happy_label }}</div>
                </div>
            </div>
        </div>
        <div data-aos="fade-left">
            <div class="relative">
                <div class="absolute -top-4 -left-4 w-32 h-32 bg-rose-200 rounded-full opacity-50"></div>
                <div class="absolute -bottom-4 -right-4 w-40 h-40 bg-pink-200 rounded-full opacity-50"></div>
                <img src="{{ $about_image }}" alt="About Pixora" class="relative z-10 rounded-2xl shadow-xl w-full">
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-gray-800 mb-4">
                Kenapa <span class="bg-gradient-to-r from-rose-500 to-pink-600 bg-clip-text text-transparent">Memilih Kami</span>
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Kami memberikan pelayanan terbaik untuk momen spesial Anda
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach($features as $feature)
            <div class="glass-card rounded-2xl p-6 text-center" data-aos="fade-up">
                <div class="w-16 h-16 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas {{ $feature['icon'] }} text-2xl text-rose-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ $feature['title'] }}</h3>
                <p class="text-gray-600">{{ $feature['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Gallery Section -->
<section class="py-20 container mx-auto px-4">
    <div class="text-center mb-12" data-aos="fade-up">
        <h2 class="text-4xl md:text-5xl font-display font-bold text-gray-800 mb-4">
            Galeri <span class="bg-gradient-to-r from-rose-500 to-pink-600 bg-clip-text text-transparent">Karya Kami</span>
        </h2>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Lihat hasil karya terbaik dari para klien kami
        </p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($gallery_images as $img)
        @if($img)
        <div class="group relative overflow-hidden rounded-xl cursor-pointer" data-aos="fade-up">
            <a href="{{ $img }}" data-lightbox="gallery" data-title="Gallery">
                <img src="{{ $img }}"
                    alt="Gallery"
                    class="w-full h-64 object-cover transition group-hover:scale-110 duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-4">
                    <i class="fas fa-search-plus text-white text-2xl ml-auto"></i>
                </div>
            </a>
        </div>
        @endif
        @endforeach
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-20 bg-gray-100">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12" data-aos="fade-up">
            <h2 class="text-4xl md:text-5xl font-display font-bold text-gray-800 mb-4">
                Apa Kata <span class="bg-gradient-to-r from-rose-500 to-pink-600 bg-clip-text text-transparent">Mereka</span>
            </h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Pengalaman nyata dari klien kami
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            @foreach($testimonials as $testi)
            <div class="glass-card rounded-2xl p-6" data-aos="fade-up">
                <div class="flex text-yellow-500 mb-3">
                    @for($i=0; $i<$testi['rating']; $i++)
                        <i class="fas fa-star"></i>
                        @endfor
                </div>
                <p class="text-gray-600 mb-4">"{{ $testi['text'] }}"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr($testi['name'], 0, 1) }}
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-800">{{ $testi['name'] }}</h4>
                        <p class="text-xs text-gray-500">Customer</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="{{ $cta_bg_image ?? 'https://images.unsplash.com/photo-1492691527719-9d1e07e534b4?w=1920&fit=crop' }}"
            alt="CTA Background"
            class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/70"></div>
    </div>

    <div class="container mx-auto px-4 relative z-10 text-center" data-aos="fade-up">
        <h2 class="text-4xl md:text-5xl font-display font-bold text-white mb-4">
            {{ $cta_title ?? 'Siap Mengabadikan Momen Anda?' }}
        </h2>
        <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
            {{ $cta_subtitle ?? 'Booking sekarang dan dapatkan pengalaman fotografi terbaik bersama Pixora' }}
        </p>
        <a href="{{ route('calendar') }}"
            class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-500 to-pink-600 text-white px-10 py-4 rounded-full text-lg font-semibold hover:shadow-2xl transition-all hover:scale-105">
            <i class="fas fa-calendar-check"></i> {{ $cta_button_text ?? 'Booking Sekarang' }}
        </a>
    </div>
</section>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    AOS.init({
        duration: 800,
        once: true
    });
</script>
@endpush