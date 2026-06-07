{{-- resources/views/admin/settings/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Pengaturan Studio')
@section('subtitle', 'Kelola informasi dan kebijakan studio')

@section('content')
<div class="space-y-6">
    <!-- Informasi Studio -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-building text-rose-500 mr-2"></i>
                Informasi Studio
            </h3>
            <p class="text-sm text-gray-500 mt-1">Data kontak dan lokasi studio Pixora</p>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-tag text-rose-500 mr-1"></i>
                        Nama Studio
                    </label>
                    <input type="text" name="studio_name" value="{{ $settings['studio_name'] ?? 'Pixora Studio' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-phone text-rose-500 mr-1"></i>
                        Nomor Telepon
                    </label>
                    <input type="text" name="studio_phone" value="{{ $settings['studio_phone'] ?? '+62 812-3456-7890' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-envelope text-rose-500 mr-1"></i>
                        Email Studio
                    </label>
                    <input type="email" name="studio_email" value="{{ $settings['studio_email'] ?? 'hello@pixora.com' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fas fa-map-marker-alt text-rose-500 mr-1"></i>
                        Alamat Studio
                    </label>
                    <input type="text" name="studio_address" value="{{ $settings['studio_address'] ?? 'Jakarta, Indonesia' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    
    <!-- Jam Operasional -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-clock text-rose-500 mr-2"></i>
                Jam Operasional
            </h3>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Buka</label>
                    <input type="time" name="open_time" value="{{ $settings['open_time'] ?? '08:00' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Tutup</label>
                    <input type="time" name="close_time" value="{{ $settings['close_time'] ?? '20:00' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Zona Waktu</label>
                    <select name="timezone" class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                        <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? 'Asia/Jakarta') == 'Asia/Jakarta' ? 'selected' : '' }}>WIB (Asia/Jakarta)</option>
                        <option value="Asia/Makassar" {{ ($settings['timezone'] ?? '') == 'Asia/Makassar' ? 'selected' : '' }}>WITA (Asia/Makassar)</option>
                        <option value="Asia/Jayapura" {{ ($settings['timezone'] ?? '') == 'Asia/Jayapura' ? 'selected' : '' }}>WIT (Asia/Jayapura)</option>
                    </select>
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    
    <!-- Sosial Media -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-share-alt text-rose-500 mr-2"></i>
                Sosial Media
            </h3>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fab fa-instagram text-pink-500 mr-1"></i>
                        Instagram
                    </label>
                    <input type="text" name="instagram" value="{{ $settings['instagram'] ?? '@pixora.studio' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fab fa-facebook text-blue-600 mr-1"></i>
                        Facebook
                    </label>
                    <input type="text" name="facebook" value="{{ $settings['facebook'] ?? 'pixora.studio' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fab fa-tiktok text-black mr-1"></i>
                        TikTok
                    </label>
                    <input type="text" name="tiktok" value="{{ $settings['tiktok'] ?? '@pixora' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        <i class="fab fa-youtube text-red-600 mr-1"></i>
                        YouTube
                    </label>
                    <input type="text" name="youtube" value="{{ $settings['youtube'] ?? 'pixora' }}" 
                           class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">
                </div>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
    
    <!-- Kebijakan Studio -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <h3 class="text-lg font-bold text-gray-800">
                <i class="fas fa-file-alt text-rose-500 mr-2"></i>
                Kebijakan Studio
            </h3>
        </div>
        
        <form method="POST" action="{{ route('admin.settings.update') }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kebijakan Pembatalan</label>
                <textarea name="cancellation_policy" rows="3" 
                          class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">{{ $settings['cancellation_policy'] ?? 'Pembatalan maksimal H-7 untuk refund DP' }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kebijakan Reschedule</label>
                <textarea name="reschedule_policy" rows="3" 
                          class="w-full rounded-lg px-4 py-2.5 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all">{{ $settings['reschedule_policy'] ?? 'Reschedule maksimal H-3 sebelum sesi' }}</textarea>
            </div>
            
            <div class="border-t border-gray-100 pt-4">
                <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition-all">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection