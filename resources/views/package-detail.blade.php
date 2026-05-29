{{-- resources/views/package-detail.blade.php --}}
@extends('layouts.app')

@section('title', $package->name . ' - Pixora')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-6xl mx-auto">
        
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-rose-500">Beranda</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <a href="{{ route('packages') }}" class="hover:text-rose-500">Paket</a>
            <i class="fas fa-chevron-right mx-2 text-xs"></i>
            <span class="text-gray-800">{{ $package->name }}</span>
        </nav>
        
        <!-- Package Detail -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Gallery Slider -->
            @php
                $allImages = [];
                if ($package->image) {
                    $allImages[] = $package->image_url;
                }
                foreach ($package->galleries as $gallery) {
                    $allImages[] = $gallery->image_url;
                }
            @endphp
            
            @if(count($allImages) > 0)
            <div class="relative">
                <div class="swiper-container package-swiper">
                    <div class="swiper-wrapper">
                        @foreach($allImages as $image)
                        <div class="swiper-slide">
                            <div class="relative h-96 md:h-[500px]">
                                <img src="{{ $image }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <!-- Navigation -->
                    <div class="swiper-button-next bg-white/80 rounded-full w-10 h-10 after:text-rose-500 shadow-md"></div>
                    <div class="swiper-button-prev bg-white/80 rounded-full w-10 h-10 after:text-rose-500 shadow-md"></div>
                    <div class="swiper-pagination !bottom-4"></div>
                </div>
            </div>
            @else
            <div class="h-64 bg-gray-200 flex items-center justify-center">
                <i class="fas fa-image text-4xl text-gray-400"></i>
            </div>
            @endif
            
            <!-- Content -->
            <div class="p-6 md:p-8">
                <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2">{{ $package->name }}</h1>
                        <div class="flex items-center gap-2">
                            <div class="flex text-yellow-400">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <span class="text-sm text-gray-500">(120+ ulasan)</span>
                        </div>
                    </div>
                    <div class="bg-rose-50 rounded-xl px-4 py-2 text-center">
                        <span class="text-xs text-rose-500 font-semibold">Mulai dari</span>
                        <p class="text-2xl font-bold text-rose-600">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                    </div>
                </div>
                
                <p class="text-gray-600 leading-relaxed mb-8">{{ $package->description }}</p>
                
                <!-- Package Specs -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <i class="fas fa-clock text-rose-500 text-xl mb-2"></i>
                        <p class="text-sm text-gray-500">Durasi</p>
                        <p class="font-semibold">{{ $package->duration_hours }} Jam</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <i class="fas fa-image text-rose-500 text-xl mb-2"></i>
                        <p class="text-sm text-gray-500">Foto Edit</p>
                        <p class="font-semibold">{{ $package->edited_photos }} Foto</p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <i class="fas fa-location-dot text-rose-500 text-xl mb-2"></i>
                        <p class="text-sm text-gray-500">Lokasi</p>
                        <p class="font-semibold">
                            @if($package->location_type == 'studio')
                                Studio Indoor
                            @elseif($package->location_type == 'outdoor')
                                Outdoor
                            @else
                                Studio & Outdoor
                            @endif
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <i class="fas fa-camera text-rose-500 text-xl mb-2"></i>
                        <p class="text-sm text-gray-500">Fotografer</p>
                        <p class="font-semibold">Professional</p>
                    </div>
                </div>
                
                <!-- Inclusions -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-check-circle text-rose-500"></i>
                        Yang Termasuk dalam Paket
                    </h3>
                    <div class="grid md:grid-cols-2 gap-3">
                        @php
                            $inclusions = is_array($package->inclusions) ? $package->inclusions : json_decode($package->inclusions, true);
                        @endphp
                        @if($inclusions && count($inclusions) > 0)
                            @foreach($inclusions as $item)
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                <span>{{ $item }}</span>
                            </div>
                            @endforeach
                        @else
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                <span>1 Fotografer professional</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                <span>1 Asisten fotografer</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                <span>Soft file via Google Drive</span>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                <span>{{ $package->edited_photos }} foto hasil edit</span>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Price & Booking -->
                <div class="border-t pt-6">
                    <div class="flex flex-wrap justify-between items-center gap-4">
                        <div>
                            <p class="text-gray-500 text-sm">Total Harga</p>
                            <p class="text-3xl font-bold text-rose-600">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                            @if($package->down_payment)
                            <p class="text-sm text-gray-500 mt-1">DP: Rp {{ number_format($package->down_payment, 0, ',', '.') }}</p>
                            @endif
                        </div>
                        <a href="{{ route('calendar') }}" 
                           class="bg-gradient-to-r from-rose-500 to-pink-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg transition-all inline-flex items-center gap-2">
                            <i class="fas fa-calendar-alt"></i>
                            Booking Sekarang
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="mt-8 bg-white rounded-2xl shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-question-circle text-rose-500"></i>
                Pertanyaan Umum
            </h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-1">Apakah bisa request konsep foto tertentu?</h4>
                    <p class="text-gray-600 text-sm">Tentu! Anda bisa menyampaikan konsep yang diinginkan saat booking atau diskusi dengan tim kreatif kami.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800 mb-1">Berapa lama proses edit foto?</h4>
                    <p class="text-gray-600 text-sm">Foto akan selesai diedit dalam waktu 14-21 hari kerja setelah sesi foto selesai.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800 mb-1">Apakah DP bisa refund jika batal?</h4>
                    <p class="text-gray-600 text-sm">DP tidak dapat dikembalikan jika pembatalan dilakukan kurang dari H-7 sebelum jadwal sesi.</p>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 justify-center mt-8">
            <a href="{{ route('calendar') }}" 
               class="bg-gradient-to-r from-rose-500 to-pink-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg transition-all inline-flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i> Booking Sekarang
            </a>
            <a href="{{ route('packages') }}" 
               class="bg-gray-200 text-gray-700 px-8 py-3 rounded-xl font-semibold hover:bg-gray-300 transition-all inline-flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Lihat Paket Lain
            </a>
        </div>
        
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    const swiper = new Swiper('.package-swiper', {
        loop: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        effect: 'slide',
        speed: 800,
    });
</script>
@endpush
@endsection