{{-- resources/views/packages.blade.php --}}
@extends('layouts.app')

@section('title', 'Paket Fotografi - Pixora')

@section('content')
<section class="relative py-16 bg-gradient-to-r from-rose-500 to-pink-600">
    <div class="absolute inset-0 opacity-20">
        <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=1600&fit=crop" 
             alt="Background" 
             class="w-full h-full object-cover">
    </div>
    <div class="container mx-auto px-4 text-center relative z-10">
        <h1 class="text-5xl md:text-6xl font-display font-bold text-white mb-4" data-aos="fade-up">
            Pilih Paket <span class="text-yellow-300">Terbaik</span> untuk Anda
        </h1>
        <p class="text-xl text-white/90 max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Dapatkan hasil foto profesional dengan harga yang sesuai budget Anda
        </p>
    </div>
</section>

<section class="py-20 container mx-auto px-4">
    <div class="grid md:grid-cols-3 gap-8">
        @foreach($packages as $index => $package)
        <div class="glass-card rounded-2xl overflow-hidden package-card group" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
            @php
                $packageImages = [
                    'https://images.unsplash.com/photo-1519741497674-611481863552?w=600&h=350&fit=crop',
                    'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600&h=350&fit=crop',
                    'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600&h=350&fit=crop'
                ];
            @endphp
            <div class="relative overflow-hidden">
                <img src="{{ $packageImages[$index % count($packageImages)] }}" 
                     alt="{{ $package->name }}" 
                     class="w-full h-64 object-cover transition group-hover:scale-110 duration-500">
                <div class="absolute top-4 right-4">
                    <span class="bg-rose-500 text-white text-xs px-3 py-1 rounded-full">Best Deal</span>
                </div>
            </div>
            <div class="p-6">
                <h3 class="text-2xl font-bold text-gray-800 mb-2">{{ $package->name }}</h3>
                <p class="text-gray-600 mb-4">{{ $package->description }}</p>
                
                <div class="space-y-3 mb-6">
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="fas fa-clock text-rose-500 w-5"></i>
                        <span>{{ $package->duration_hours }} jam sesi foto</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="fas fa-image text-rose-500 w-5"></i>
                        <span>{{ $package->edited_photos }} foto hasil edit</span>
                    </div>
                    <div class="flex items-center gap-3 text-gray-600">
                        <i class="fas fa-location-dot text-rose-500 w-5"></i>
                        <span>{{ ucfirst($package->location_type) }}</span>
                    </div>
                </div>
                
                <div class="border-t pt-4 mt-2">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <span class="text-gray-400 line-through text-sm">Rp {{ number_format($package->price * 1.2, 0, ',', '.') }}</span>
                            <div class="text-3xl font-bold text-rose-600">{{ $package->formatted_price }}</div>
                            <span class="text-xs text-gray-500">/sesi</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm text-gray-500">DP mulai</span>
                            <div class="font-semibold">{{ $package->formatted_down_payment ?? 'Rp 500.000' }}</div>
                        </div>
                    </div>
                    <a href="{{ route('package.detail', $package->slug) }}" 
                       class="block text-center bg-gradient-to-r from-rose-500 to-pink-600 text-white py-3 rounded-xl font-semibold hover:shadow-xl transition-all">
                        Pilih Paket Ini
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

<!-- FAQ Section -->
<section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-display font-bold text-center mb-8">Pertanyaan Umum</h2>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="glass-card rounded-xl p-5">
                <h3 class="font-semibold text-lg mb-2">Apakah bisa request konsep foto tertentu?</h3>
                <p class="text-gray-600">Tentu! Anda bisa menyampaikan konsep yang diinginkan saat booking atau diskusi dengan tim kreatif kami.</p>
            </div>
            <div class="glass-card rounded-xl p-5">
                <h3 class="font-semibold text-lg mb-2">Berapa lama proses edit foto?</h3>
                <p class="text-gray-600">Foto akan selesai diedit dalam waktu 14-21 hari kerja setelah sesi foto selesai.</p>
            </div>
            <div class="glass-card rounded-xl p-5">
                <h3 class="font-semibold text-lg mb-2">Apakah DP bisa refund jika batal?</h3>
                <p class="text-gray-600">DP tidak dapat dikembalikan jika pembatalan dilakukan kurang dari H-7 sebelum jadwal sesi.</p>
            </div>
        </div>
    </div>
</section>
@endsection