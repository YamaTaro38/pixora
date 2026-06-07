{{-- resources/views/admin/landing-page/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Landing Page')
@section('subtitle', 'Kelola konten halaman utama website')

@section('content')
<style>
    /* Tab Navigation */
    .tab-btn {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .tab-btn.active {
        color: #e11d48;
        border-bottom: 2px solid #e11d48;
    }
    
    /* Icon Picker Grid */
    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 12px;
        max-height: 350px;
        overflow-y: auto;
        padding: 10px;
    }
    .icon-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 12px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid #e2e8f0;
        background: white;
    }
    .icon-option:hover {
        background: #fef2f2;
        border-color: #e11d48;
        transform: scale(1.05);
    }
    .icon-option.selected {
        background: #e11d48;
        color: white;
        border-color: #e11d48;
    }
    .icon-option i {
        font-size: 28px;
        margin-bottom: 6px;
    }
    .icon-option span {
        font-size: 10px;
        font-family: monospace;
    }
    
    /* Dropzone Area */
    .dropzone-area {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #f8fafc;
    }
    .dropzone-area:hover {
        border-color: #e11d48;
        background: #fef2f2;
    }
    
    /* Gallery Preview */
    .gallery-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 12px;
    }
    .gallery-item {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
    }
    .gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .gallery-item .remove-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        cursor: pointer;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .gallery-item:hover .remove-btn {
        opacity: 1;
    }
    
    /* Feature Item */
    .feature-item {
        transition: all 0.2s ease;
    }
    .feature-item:hover {
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .feature-item .sort-handle {
        cursor: move;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background: #f1f5f9;
    }
    
    /* Icon Clickable */
    .icon-clickable {
        cursor: pointer;
        transition: all 0.2s;
    }
    .icon-clickable:hover {
        transform: scale(1.05);
        box-shadow: 0 0 0 2px #e11d48;
    }
    
    /* Stat Card Preview */
    .stat-icon-preview {
        cursor: pointer;
        transition: all 0.2s;
    }
    .stat-icon-preview:hover {
        transform: scale(1.05);
        box-shadow: 0 0 0 2px #e11d48;
    }
</style>

<div class="space-y-6">
    <!-- Tab Navigation -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex flex-wrap -mb-px">
                <button type="button" class="tab-btn px-6 py-3 text-sm font-semibold transition-all duration-200 active text-rose-600 border-b-2 border-rose-600" data-tab="hero">
                    <i class="fas fa-star mr-2"></i> Hero
                </button>
                <button type="button" class="tab-btn px-6 py-3 text-sm font-semibold transition-all duration-200 text-gray-600 border-b-2 border-transparent" data-tab="about">
                    <i class="fas fa-info-circle mr-2"></i> About
                </button>
                <button type="button" class="tab-btn px-6 py-3 text-sm font-semibold transition-all duration-200 text-gray-600 border-b-2 border-transparent" data-tab="features">
                    <i class="fas fa-list-check mr-2"></i> Features
                </button>
                <button type="button" class="tab-btn px-6 py-3 text-sm font-semibold transition-all duration-200 text-gray-600 border-b-2 border-transparent" data-tab="gallery">
                    <i class="fas fa-images mr-2"></i> Gallery
                </button>
                <button type="button" class="tab-btn px-6 py-3 text-sm font-semibold transition-all duration-200 text-gray-600 border-b-2 border-transparent" data-tab="testimonials">
                    <i class="fas fa-comment-dots mr-2"></i> Testimonials
                </button>
            </nav>
        </div>
        
        <div class="p-6">
            <!-- ==================== TAB HERO ==================== -->
            <div id="tab-hero" class="tab-content">
                <form method="POST" action="{{ route('admin.landing-page.hero') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <!-- Hero Text Section -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Hero Text</h4>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Hero</label>
                            <input type="text" name="hero_title" value="{{ $contents['hero_title'] }}" class="w-full rounded-lg px-4 py-2 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200">
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Subtitle Hero</label>
                            <textarea name="hero_subtitle" rows="2" class="w-full rounded-lg px-4 py-2 border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-200">{{ $contents['hero_subtitle'] }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Stats Cards Section -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-chart-simple text-rose-500 mr-1"></i> Stats Cards (Hero)
                        </h4>
                        <p class="text-xs text-gray-500 mb-3">Atur angka dan label untuk kartu statistik di hero section</p>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Stat 1 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Icon</label>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="stat-icon-preview w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center cursor-pointer border-2 border-rose-200"
                                         data-field="stat1_icon" data-preview="stat1Preview" onclick="openIconPickerForStat('stat1_icon', 'stat1Preview')">
                                        <i id="stat1Preview" class="fas {{ $contents['stat1_icon'] ?? 'fa-smile' }} text-rose-500 text-lg"></i>
                                    </div>
                                    <input type="hidden" name="stat1_icon" id="stat1_icon" value="{{ $contents['stat1_icon'] ?? 'fa-smile' }}">
                                    <span class="text-xs text-gray-500">Klik icon untuk mengganti</span>
                                </div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Angka / Value</label>
                                <input type="text" name="stat1_value" value="{{ $contents['stat1_value'] ?? '500+' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mt-2 mb-1">Label</label>
                                <input type="text" name="stat1_label" value="{{ $contents['stat1_label'] ?? 'Klien Puas' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                            </div>
                            
                            <!-- Stat 2 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Icon</label>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="stat-icon-preview w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center cursor-pointer border-2 border-rose-200"
                                         data-field="stat2_icon" data-preview="stat2Preview" onclick="openIconPickerForStat('stat2_icon', 'stat2Preview')">
                                        <i id="stat2Preview" class="fas {{ $contents['stat2_icon'] ?? 'fa-camera-retro' }} text-rose-500 text-lg"></i>
                                    </div>
                                    <input type="hidden" name="stat2_icon" id="stat2_icon" value="{{ $contents['stat2_icon'] ?? 'fa-camera-retro' }}">
                                    <span class="text-xs text-gray-500">Klik icon untuk mengganti</span>
                                </div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Angka / Value</label>
                                <input type="text" name="stat2_value" value="{{ $contents['stat2_value'] ?? '1000+' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mt-2 mb-1">Label</label>
                                <input type="text" name="stat2_label" value="{{ $contents['stat2_label'] ?? 'Sesi Foto' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                            </div>
                            
                            <!-- Stat 3 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Icon</label>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="stat-icon-preview w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center cursor-pointer border-2 border-rose-200"
                                         data-field="stat3_icon" data-preview="stat3Preview" onclick="openIconPickerForStat('stat3_icon', 'stat3Preview')">
                                        <i id="stat3Preview" class="fas {{ $contents['stat3_icon'] ?? 'fa-images' }} text-rose-500 text-lg"></i>
                                    </div>
                                    <input type="hidden" name="stat3_icon" id="stat3_icon" value="{{ $contents['stat3_icon'] ?? 'fa-images' }}">
                                    <span class="text-xs text-gray-500">Klik icon untuk mengganti</span>
                                </div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Angka / Value</label>
                                <input type="text" name="stat3_value" value="{{ $contents['stat3_value'] ?? '50+' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mt-2 mb-1">Label</label>
                                <input type="text" name="stat3_label" value="{{ $contents['stat3_label'] ?? 'Portofolio' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                            </div>
                            
                            <!-- Stat 4 -->
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Icon</label>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="stat-icon-preview w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center cursor-pointer border-2 border-rose-200"
                                         data-field="stat4_icon" data-preview="stat4Preview" onclick="openIconPickerForStat('stat4_icon', 'stat4Preview')">
                                        <i id="stat4Preview" class="fas {{ $contents['stat4_icon'] ?? 'fa-star' }} text-rose-500 text-lg"></i>
                                    </div>
                                    <input type="hidden" name="stat4_icon" id="stat4_icon" value="{{ $contents['stat4_icon'] ?? 'fa-star' }}">
                                    <span class="text-xs text-gray-500">Klik icon untuk mengganti</span>
                                </div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Angka / Value</label>
                                <input type="text" name="stat4_value" value="{{ $contents['stat4_value'] ?? '4.9' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                                <label class="block text-xs font-semibold text-gray-700 mt-2 mb-1">Label</label>
                                <input type="text" name="stat4_label" value="{{ $contents['stat4_label'] ?? 'Rating' }}" class="w-full rounded-lg px-3 py-2 text-sm border border-gray-200">
                            </div>
                        </div>
                    </div>
                    
                    <!-- CTA Button Section -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">
                            <i class="fas fa-hand-pointer text-rose-500 mr-1"></i> Call to Action Button
                        </h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tombol Teks</label>
                                <input type="text" name="hero_button_text" value="{{ $contents['hero_button_text'] }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tombol Link</label>
                                <input type="text" name="hero_button_link" value="{{ $contents['hero_button_link'] }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Icon Tombol</label>
                            <div class="flex items-center gap-4">
                                <div id="ctaIconPreview" class="w-12 h-12 bg-gradient-to-br from-rose-50 to-pink-50 rounded-xl flex items-center justify-center border-2 border-rose-200 cursor-pointer"
                                     onclick="openIconPicker('cta_icon', 'ctaIconPreview')">
                                    <i class="fas {{ $contents['cta_icon'] ?? 'fa-calendar-check' }} text-xl text-rose-500"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-500">Klik icon di samping untuk memilih icon</p>
                                </div>
                                <input type="hidden" name="cta_icon" id="cta_icon" value="{{ $contents['cta_icon'] ?? 'fa-calendar-check' }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hero Background Image -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar Background Hero</label>
                        @if($contents['hero_bg_image'] && file_exists(public_path($contents['hero_bg_image'])))
                            <img src="{{ $contents['hero_bg_image'] }}" class="w-48 h-32 object-cover rounded-lg border mb-2">
                        @endif
                        <input type="file" name="hero_bg_image" accept="image/*" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                        <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah gambar</p>
                    </div>
                    
                    <div class="pt-3">
                        <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition">Simpan Hero</button>
                    </div>
                </form>
            </div>
            
            <!-- ==================== TAB ABOUT ==================== -->
            <div id="tab-about" class="tab-content hidden">
                <form method="POST" action="{{ route('admin.landing-page.about') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Judul About</label>
                        <input type="text" name="about_title" value="{{ $contents['about_title'] }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi About</label>
                        <textarea name="about_description" rows="4" class="w-full rounded-lg px-4 py-2 border border-gray-200">{{ $contents['about_description'] }}</textarea>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-4 mt-2">
                        <h4 class="text-md font-semibold text-gray-800 mb-3">Statistik About</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Tahun Berdiri</label>
                                <input type="text" name="about_year" value="{{ $contents['about_year'] ?? '2018' }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Label Tahun</label>
                                <input type="text" name="about_year_label" value="{{ $contents['about_year_label'] ?? 'Berdiri Sejak' }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                            <div></div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Project</label>
                                <input type="text" name="about_projects" value="{{ $contents['about_projects'] ?? '1000+' }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Label Project</label>
                                <input type="text" name="about_projects_label" value="{{ $contents['about_projects_label'] ?? 'Project Selesai' }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                            <div></div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Jumlah Klien Bahagia</label>
                                <input type="text" name="about_happy" value="{{ $contents['about_happy'] ?? '500+' }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">Label Klien</label>
                                <input type="text" name="about_happy_label" value="{{ $contents['about_happy_label'] ?? 'Klien Bahagia' }}" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar About</label>
                        @if($contents['about_image'] && file_exists(public_path($contents['about_image'])))
                            <img src="{{ $contents['about_image'] }}" class="w-48 h-32 object-cover rounded-lg border mb-2">
                        @endif
                        <input type="file" name="about_image" accept="image/*" class="w-full rounded-lg px-4 py-2 border border-gray-200">
                    </div>
                    
                    <div class="pt-3">
                        <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition">Simpan About</button>
                    </div>
                </form>
            </div>
            
            <!-- ==================== TAB FEATURES ==================== -->
            <div id="tab-features" class="tab-content hidden">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Daftar Fitur</h3>
                            <p class="text-sm text-gray-500">Atur icon, judul, dan deskripsi fitur</p>
                        </div>
                        <button type="button" onclick="addFeature()" class="bg-rose-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-rose-600 transition inline-flex items-center gap-2">
                            <i class="fas fa-plus text-sm"></i> Tambah Fitur
                        </button>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.landing-page.features') }}" id="featuresForm">
                        @csrf
                        <div id="featuresContainer" class="space-y-4">
                            @foreach($contents['features'] as $index => $feature)
                            <div class="feature-item bg-white border border-gray-200 rounded-xl p-4">
                                <div class="flex justify-between items-center mb-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-grip-vertical text-gray-400 cursor-move sort-handle"></i>
                                        <span class="font-medium text-gray-700">Fitur #{{ $index + 1 }}</span>
                                    </div>
                                    <button type="button" onclick="removeFeature(this)" class="text-red-400 hover:text-red-600">Hapus</button>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Icon Fitur</label>
                                    <div class="flex items-center gap-3">
                                        <div class="icon-preview w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center border cursor-pointer"
                                             onclick="openIconPickerForFeature('feature_icon_{{ $index }}', this)">
                                            <i class="fas {{ $feature['icon'] ?? 'fa-camera' }} text-rose-500"></i>
                                        </div>
                                        <input type="hidden" name="feature_icon[]" id="feature_icon_{{ $index }}" value="{{ $feature['icon'] ?? 'fa-camera' }}">
                                        <span class="text-sm text-gray-500">Klik icon untuk mengganti</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Fitur</label>
                                    <input type="text" name="feature_title[]" value="{{ $feature['title'] }}" class="w-full rounded-lg px-3 py-2 border border-gray-200">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Fitur</label>
                                    <textarea name="feature_description[]" rows="2" class="w-full rounded-lg px-3 py-2 border border-gray-200">{{ $feature['description'] }}</textarea>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-3 border-t">
                            <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition">Simpan Features</button>
                        </div>
                    </form>
                </div>
            </div>
            
          <!-- ==================== TAB GALLERY ==================== -->
<div id="tab-gallery" class="tab-content hidden">
    <div class="space-y-4">
        <form method="POST" action="{{ route('admin.landing-page.gallery') }}" enctype="multipart/form-data" id="galleryForm">
            @csrf
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tambah Gambar Baru</label>
                <div id="galleryDropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:border-rose-400 transition-all" style="cursor: pointer;">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                    <p class="text-gray-500">Klik atau drag & drop untuk upload gambar</p>
                    <p class="text-xs text-gray-400 mt-1">Bisa pilih beberapa gambar sekaligus (JPG, PNG, maks 2MB)</p>
                    <input type="file" id="galleryInput" name="gallery_images[]" accept="image/*" multiple class="hidden">
                </div>
                <div id="galleryPreview" class="flex flex-wrap gap-3 mt-4"></div>
            </div>
            
            <div class="border-t border-gray-200 pt-4 mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Gallery Saat Ini</label>
                <div id="existingGallery" class="flex flex-wrap gap-3">
                    @foreach($contents['gallery_images'] as $index => $img)
                    <div class="relative w-24 h-24 rounded-lg overflow-hidden border border-gray-200 group" data-index="{{ $index }}">
                        <img src="{{ $img }}" class="w-full h-full object-cover">
                        <button type="button" class="remove-gallery-btn absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition" onclick="removeGalleryImage({{ $index }})">
                            <i class="fas fa-times"></i>
                        </button>
                        <input type="hidden" name="existing_gallery_images[]" value="{{ $img }}">
                    </div>
                    @endforeach
                </div>
                @if(count($contents['gallery_images']) == 0)
                <p id="noGalleryMessage" class="text-gray-400 text-center py-4">Belum ada gambar gallery</p>
                @endif
            </div>
            
            <div class="mt-4">
                <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition">Simpan Gallery</button>
            </div>
        </form>
    </div>
</div>  
            <!-- ==================== TAB TESTIMONIALS ==================== -->
            <div id="tab-testimonials" class="tab-content hidden">
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Daftar Testimonial</h3>
                            <p class="text-sm text-gray-500">Atur testimonial customer</p>
                        </div>
                        <button type="button" onclick="addTestimonial()" class="bg-rose-500 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-rose-600 transition inline-flex items-center gap-2">
                            <i class="fas fa-plus text-sm"></i> Tambah Testimonial
                        </button>
                    </div>
                    
                    <form method="POST" action="{{ route('admin.landing-page.testimonials') }}" id="testimonialsForm">
                        @csrf
                        <div id="testimonialsContainer" class="space-y-4">
                            @foreach($contents['testimonials'] as $index => $testimonial)
                            <div class="testimonial-item border rounded-xl p-4 bg-gray-50">
                                <input type="hidden" name="testimonial_index[]" value="{{ $index }}">
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-semibold text-gray-700">Testimonial #{{ $index + 1 }}</h4>
                                    <button type="button" onclick="removeTestimonial(this)" class="text-red-500 text-sm hover:text-red-600">Hapus</button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                                        <input type="text" name="testimonial_name[]" value="{{ $testimonial['name'] }}" class="w-full rounded-lg px-3 py-2 border border-gray-200">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Rating</label>
                                        <select name="testimonial_rating[]" class="w-full rounded-lg px-3 py-2 border border-gray-200">
                                            <option value="5" {{ ($testimonial['rating'] ?? 5) == 5 ? 'selected' : '' }}>★★★★★ (5)</option>
                                            <option value="4" {{ ($testimonial['rating'] ?? 5) == 4 ? 'selected' : '' }}>★★★★☆ (4)</option>
                                            <option value="3" {{ ($testimonial['rating'] ?? 5) == 3 ? 'selected' : '' }}>★★★☆☆ (3)</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Testimoni</label>
                                    <textarea name="testimonial_text[]" rows="2" class="w-full rounded-lg px-3 py-2 border border-gray-200">{{ $testimonial['text'] }}</textarea>
                                </div>
                                
                                <div class="mt-3">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Profile</label>
                                    <div class="flex items-center gap-4">
                                        @if(isset($testimonial['photo']) && $testimonial['photo'])
                                        <div class="relative group">
                                            <img src="{{ $testimonial['photo'] }}" class="w-12 h-12 rounded-full object-cover border-2 border-rose-200">
                                            <button type="button" onclick="removeTestimonialPhoto(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">✕</button>
                                        </div>
                                        @endif
                                        <div class="flex-1">
                                            <input type="file" class="testimonial-photo-input hidden" accept="image/*" data-index="{{ $index }}">
                                            <button type="button" onclick="uploadTestimonialPhoto(this, {{ $index }})" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition">Pilih Foto</button>
                                            <input type="hidden" name="testimonial_photo[]" value="{{ $testimonial['photo'] ?? '' }}" class="photo-value">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-3 border-t">
                            <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition">Simpan Testimonials</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
// ============ ICON LIST ============
const iconList = [
    'fa-camera', 'fa-robot', 'fa-clock', 'fa-star', 'fa-heart', 'fa-bolt',
    'fa-shield-alt', 'fa-user', 'fa-users', 'fa-globe', 'fa-music', 'fa-video',
    'fa-image', 'fa-palette', 'fa-magic', 'fa-rocket', 'fa-gem', 'fa-crown',
    'fa-leaf', 'fa-cloud-sun', 'fa-mobile-alt', 'fa-laptop', 'fa-wifi', 'fa-credit-card',
    'fa-calendar-check', 'fa-fire', 'fa-sun', 'fa-moon', 'fa-smile', 'fa-camera-retro',
    'fa-images', 'fa-chart-line', 'fa-award', 'fa-certificate', 'fa-thumbs-up', 'fa-trophy'
];

// ============ TAB NAVIGATION ============
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const tabId = this.getAttribute('data-tab');
        
        document.querySelectorAll('.tab-btn').forEach(t => {
            t.classList.remove('active', 'text-rose-600', 'border-rose-600');
            t.classList.add('text-gray-600', 'border-transparent');
        });
        this.classList.add('active', 'text-rose-600', 'border-rose-600');
        this.classList.remove('text-gray-600', 'border-transparent');
        
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        const activeContent = document.getElementById(`tab-${tabId}`);
        if (activeContent) {
            activeContent.classList.remove('hidden');
        }
        
        localStorage.setItem('activeLandingTab', tabId);
    });
});

// Load saved tab
const savedTab = localStorage.getItem('activeLandingTab') || 'hero';
const savedTabBtn = document.querySelector(`.tab-btn[data-tab="${savedTab}"]`);
if (savedTabBtn) {
    savedTabBtn.click();
} else {
    document.querySelector('.tab-btn[data-tab="hero"]').click();
}

// ============ ICON PICKER FUNCTIONS ============
function openIconPicker(fieldId, previewId) {
    const currentIcon = document.getElementById(fieldId).value;
    
    Swal.fire({
        title: 'Pilih Icon',
        html: `<div class="icon-picker-grid">${iconList.map(icon => `
            <div class="icon-option ${icon === currentIcon ? 'selected' : ''}" data-icon="${icon}">
                <i class="fas ${icon}"></i>
                <span>${icon}</span>
            </div>
        `).join('')}</div>`,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        didOpen: () => {
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    const selectedIcon = opt.getAttribute('data-icon');
                    document.getElementById(fieldId).value = selectedIcon;
                    const previewElement = document.getElementById(previewId);
                    if (previewElement) {
                        previewElement.querySelector('i').className = `fas ${selectedIcon}`;
                    }
                    Swal.close();
                });
            });
        }
    });
}

function openIconPickerForStat(fieldId, previewId) {
    const currentIcon = document.getElementById(fieldId).value;
    
    Swal.fire({
        title: 'Pilih Icon',
        html: `<div class="icon-picker-grid">${iconList.map(icon => `
            <div class="icon-option ${icon === currentIcon ? 'selected' : ''}" data-icon="${icon}">
                <i class="fas ${icon}"></i>
                <span>${icon}</span>
            </div>
        `).join('')}</div>`,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        didOpen: () => {
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    const selectedIcon = opt.getAttribute('data-icon');
                    document.getElementById(fieldId).value = selectedIcon;
                    document.getElementById(previewId).className = `fas ${selectedIcon}`;
                    Swal.close();
                });
            });
        }
    });
}

function openIconPickerForFeature(fieldId, previewElement) {
    const currentIcon = document.getElementById(fieldId).value;
    
    Swal.fire({
        title: 'Pilih Icon',
        html: `<div class="icon-picker-grid">${iconList.map(icon => `
            <div class="icon-option ${icon === currentIcon ? 'selected' : ''}" data-icon="${icon}">
                <i class="fas ${icon}"></i>
                <span>${icon}</span>
            </div>
        `).join('')}</div>`,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Batal',
        didOpen: () => {
            document.querySelectorAll('.icon-option').forEach(opt => {
                opt.addEventListener('click', () => {
                    const selectedIcon = opt.getAttribute('data-icon');
                    document.getElementById(fieldId).value = selectedIcon;
                    if (previewElement) {
                        previewElement.querySelector('i').className = `fas ${selectedIcon}`;
                    }
                    Swal.close();
                });
            });
        }
    });
}

// ============ FEATURES ==========
let featureSortable = null;

function initFeatureSortable() {
    const container = document.getElementById('featuresContainer');
    if (container && !featureSortable) {
        featureSortable = new Sortable(container, {
            animation: 300,
            ghostClass: 'sortable-ghost',
            handle: '.sort-handle'
        });
    }
}

function addFeature() {
    const container = document.getElementById('featuresContainer');
    const index = container.children.length;
    const div = document.createElement('div');
    div.className = 'feature-item bg-white border border-gray-200 rounded-xl p-4';
    div.innerHTML = `
        <div class="flex justify-between items-center mb-3">
            <div class="flex items-center gap-2">
                <i class="fas fa-grip-vertical text-gray-400 cursor-move sort-handle"></i>
                <span class="font-medium text-gray-700">Fitur #${index + 1}</span>
            </div>
            <button type="button" onclick="removeFeature(this)" class="text-red-400 hover:text-red-600">Hapus</button>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Icon Fitur</label>
            <div class="flex items-center gap-3">
                <div class="icon-preview w-10 h-10 bg-rose-50 rounded-lg flex items-center justify-center border cursor-pointer"
                     onclick="openIconPickerForFeature('feature_icon_${index}', this)">
                    <i class="fas fa-camera text-rose-500"></i>
                </div>
                <input type="hidden" name="feature_icon[]" id="feature_icon_${index}" value="fa-camera">
                <span class="text-sm text-gray-500">Klik icon untuk mengganti</span>
            </div>
        </div>
        <div class="mb-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Fitur</label>
            <input type="text" name="feature_title[]" class="w-full rounded-lg px-3 py-2 border border-gray-200" placeholder="Judul fitur">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Deskripsi Fitur</label>
            <textarea name="feature_description[]" rows="2" class="w-full rounded-lg px-3 py-2 border border-gray-200" placeholder="Deskripsi fitur"></textarea>
        </div>
    `;
    container.appendChild(div);
    initFeatureSortable();
}

function removeFeature(btn) {
    btn.closest('.feature-item').remove();
}

// ============ GALLERY UPLOAD - SIMPLE WORKING VERSION ============
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing gallery upload...');
    
    // Get elements
    const dropzone = document.getElementById('galleryDropzone');
    const fileInput = document.getElementById('galleryInput');
    const previewContainer = document.getElementById('galleryPreview');
    
    if (!dropzone) {
        console.error('galleryDropzone not found!');
        return;
    }
    if (!fileInput) {
        console.error('galleryInput not found!');
        return;
    }
    if (!previewContainer) {
        console.error('galleryPreview not found!');
        return;
    }
    
    console.log('All gallery elements found!');
    
    // Remove existing listeners by cloning and replacing
    const newDropzone = dropzone.cloneNode(true);
    dropzone.parentNode.replaceChild(newDropzone, dropzone);
    
    const newFileInput = fileInput.cloneNode(true);
    fileInput.parentNode.replaceChild(newFileInput, fileInput);
    
    // Get fresh references
    const finalDropzone = document.getElementById('galleryDropzone');
    const finalFileInput = document.getElementById('galleryInput');
    const finalPreview = document.getElementById('galleryPreview');
    
    let selectedFiles = [];
    
    // ============ CLICK ON DROPZONE ============
    finalDropzone.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Dropzone clicked, triggering file input...');
        finalFileInput.click();
    });
    
    // ============ FILE INPUT CHANGE ============
    finalFileInput.addEventListener('change', function(e) {
        console.log('File input changed, files:', this.files.length);
        const files = Array.from(this.files);
        handleFiles(files);
    });
    
    // ============ DRAG & DROP ============
    finalDropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '#e11d48';
        this.style.backgroundColor = '#fef2f2';
    });
    
    finalDropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '';
        this.style.backgroundColor = '';
    });
    
    finalDropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.style.borderColor = '';
        this.style.backgroundColor = '';
        
        const files = Array.from(e.dataTransfer.files);
        console.log('Files dropped:', files.length);
        handleFiles(files);
    });
    
    // ============ HANDLE FILES ============
    function handleFiles(files) {
        // Filter hanya gambar
        const imageFiles = files.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length === 0) {
            alert('File yang dipilih bukan gambar!');
            return;
        }
        
        // Cek ukuran
        for (let file of imageFiles) {
            if (file.size > 2 * 1024 * 1024) {
                alert('File ' + file.name + ' terlalu besar (maks 2MB)!');
                return;
            }
        }
        
        // Tambahkan ke array
        imageFiles.forEach(file => {
            selectedFiles.push(file);
            addPreview(file);
        });
        
        // Update file input
        updateFileInput();
    }
    
    // ============ ADD PREVIEW ============
    function addPreview(file) {
        const reader = new FileReader();
        const previewId = 'preview_' + Date.now() + '_' + Math.random().toString(36).substr(2, 8);
        
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'relative w-24 h-24 rounded-lg overflow-hidden border border-gray-200 group';
            div.setAttribute('data-preview-id', previewId);
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover">
                <button type="button" class="remove-preview absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition" onclick="removeGalleryPreview('${previewId}', '${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
            `;
            finalPreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    }
    
    // ============ UPDATE FILE INPUT ============
    function updateFileInput() {
        const dt = new DataTransfer();
        selectedFiles.forEach(file => {
            dt.items.add(file);
        });
        finalFileInput.files = dt.files;
        console.log('Updated file input, total files:', selectedFiles.length);
    }
});

// ============ REMOVE PREVIEW (GLOBAL FUNCTION) ============
function removeGalleryPreview(previewId, fileName) {
    // Get the global selectedFiles array from the closure
    // We need to access it via a global variable
    if (typeof window.selectedGalleryFiles !== 'undefined') {
        const index = window.selectedGalleryFiles.findIndex(f => f.name === fileName);
        if (index !== -1) {
            window.selectedGalleryFiles.splice(index, 1);
        }
    }
    
    // Remove from DOM
    const previewDiv = document.querySelector(`.relative[data-preview-id="${previewId}"]`);
    if (previewDiv) previewDiv.remove();
    
    // Update file input
    const fileInput = document.getElementById('galleryInput');
    if (fileInput && typeof window.selectedGalleryFiles !== 'undefined') {
        const dt = new DataTransfer();
        window.selectedGalleryFiles.forEach(file => {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
    }
}

// ============ REMOVE EXISTING GALLERY IMAGE ============
function removeGalleryImage(index) {
    Swal.fire({
        title: 'Hapus Gambar?',
        text: 'Gambar akan dihapus dari gallery',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("admin.landing-page.gallery-delete") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ index: index })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const galleryItem = document.querySelector(`#existingGallery .relative[data-index="${index}"]`);
                    if (galleryItem) galleryItem.remove();
                    
                    const remainingItems = document.querySelectorAll('#existingGallery .relative').length;
                    if (remainingItems === 0) {
                        const existingGallery = document.getElementById('existingGallery');
                        if (existingGallery) {
                            existingGallery.innerHTML = '<p class="text-gray-400 text-center py-4 col-span-full">Belum ada gambar gallery</p>';
                        }
                    }
                    Swal.fire('Berhasil!', 'Gambar berhasil dihapus', 'success');
                } else {
                    Swal.fire('Gagal!', data.message || 'Terjadi kesalahan', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Gagal menghapus gambar', 'error');
            });
        }
    });
}

// ============ TESTIMONIALS ==========
let testimonialCounter = 0;

// Initialize testimonial counter from existing items
const existingTestimonials = document.querySelectorAll('#testimonialsContainer .testimonial-item');
if (existingTestimonials.length > 0) {
    testimonialCounter = existingTestimonials.length;
}

function addTestimonial() {
    const container = document.getElementById('testimonialsContainer');
    const newIndex = testimonialCounter++;
    const div = document.createElement('div');
    div.className = 'testimonial-item border rounded-xl p-4 bg-gray-50';
    div.innerHTML = `
        <input type="hidden" name="testimonial_index[]" value="new_${newIndex}">
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-semibold text-gray-700">Testimonial #${newIndex + 1}</h4>
            <button type="button" onclick="removeTestimonial(this)" class="text-red-500 text-sm hover:text-red-600">Hapus</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><input type="text" name="testimonial_name[]" class="w-full rounded-lg px-3 py-2 border border-gray-200" placeholder="Nama"></div>
            <div><select name="testimonial_rating[]" class="w-full rounded-lg px-3 py-2 border border-gray-200"><option value="5">★★★★★ (5)</option><option value="4">★★★★☆ (4)</option><option value="3">★★★☆☆ (3)</option></select></div>
        </div>
        <div class="mt-3"><textarea name="testimonial_text[]" rows="2" class="w-full rounded-lg px-3 py-2 border border-gray-200" placeholder="Testimoni"></textarea></div>
        <div class="mt-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Profile</label>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <input type="file" class="testimonial-photo-input hidden" accept="image/*">
                    <button type="button" onclick="uploadTestimonialPhoto(this)" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition">Pilih Foto</button>
                    <input type="hidden" name="testimonial_photo[]" value="" class="photo-value">
                </div>
            </div>
        </div>
    `;
    container.appendChild(div);
}

function removeTestimonial(btn) {
    btn.closest('.testimonial-item').remove();
}

function removeTestimonialPhoto(btn) {
    const testimonialItem = btn.closest('.testimonial-item');
    const photoValue = testimonialItem.querySelector('.photo-value');
    if (photoValue) photoValue.value = '';
    btn.closest('.relative').remove();
}

function uploadTestimonialPhoto(btn) {
    const testimonialItem = btn.closest('.testimonial-item');
    const fileInput = testimonialItem.querySelector('.testimonial-photo-input');
    const photoValue = testimonialItem.querySelector('.photo-value');
    
    fileInput.click();
    fileInput.onchange = function(e) {
        const file = e.target.files[0];
        if (!file || !file.type.startsWith('image/')) { 
            alert('File harus gambar!'); 
            return; 
        }
        if (file.size > 2 * 1024 * 1024) { 
            alert('Ukuran maksimal 2MB!'); 
            return; 
        }
        
        const formData = new FormData();
        formData.append('photo', file);
        
        Swal.fire({ 
            title: 'Uploading...', 
            allowOutsideClick: false, 
            didOpen: () => Swal.showLoading() 
        });
        
        fetch('{{ route("admin.landing-page.upload-testimonial-photo") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                const existingPreview = testimonialItem.querySelector('.relative');
                if (existingPreview) existingPreview.remove();
                const photoPreview = document.createElement('div');
                photoPreview.className = 'relative group';
                photoPreview.innerHTML = `<img src="${data.photo_url}" class="w-12 h-12 rounded-full object-cover border-2 border-rose-200"><button type="button" onclick="removeTestimonialPhoto(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">✕</button>`;
                btn.parentElement.insertBefore(photoPreview, btn);
                photoValue.value = data.photo_url;
                Swal.fire('Berhasil!', 'Foto berhasil diupload', 'success');
            }
        })
        .catch(error => {
            Swal.close();
            Swal.fire('Error!', 'Gagal upload foto', 'error');
        });
    };
}

// Initialize Features Sortable
initFeatureSortable();

// Debug: tampilkan pesan bahwa script sudah siap
console.log('Landing Page Editor script loaded successfully!');
</script>
@endpush
@endsection