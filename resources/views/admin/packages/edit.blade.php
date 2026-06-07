{{-- resources/views/admin/packages/edit.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Edit Paket')
@section('subtitle', 'Ubah informasi paket fotografi')

@section('content')
<style>
    .image-preview {
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .image-preview:hover {
        border-color: #e11d48;
        background: #fef2f2;
    }
    .preview-img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-top: 10px;
    }
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
    .existing-gallery {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
    }
    .existing-gallery-item {
        position: relative;
        width: 100px;
        height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .existing-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .existing-gallery-item .remove-btn {
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
    .existing-gallery-item:hover .remove-btn {
        opacity: 1;
    }
    .info-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 8px;
    }
    
    /* Toggle Slider */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: 0.3s;
        border-radius: 24px;
    }
    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.3s;
        border-radius: 50%;
    }
    input:checked + .toggle-slider {
        background-color: #e11d48;
    }
    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }
    
    /* Badge Container */
    .badge-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }
    .badge {
        background: #fef2f2;
        color: #e11d48;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge .remove-badge {
        cursor: pointer;
        font-size: 10px;
        opacity: 0.7;
    }
    .badge .remove-badge:hover {
        opacity: 1;
    }
    
    /* Currency Input */
    .currency-input {
        position: relative;
    }
    .currency-input span {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
        font-weight: 500;
    }
    .currency-input input {
        padding-left: 32px;
    }
</style>

<form method="POST" action="{{ route('admin.packages.update', $package->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket</label>
                    <input type="text" name="name" value="{{ $package->name }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-rose-500 focus:ring-1 focus:ring-rose-500" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-rose-500 focus:ring-1 focus:ring-rose-500" required>{{ $package->description }}</textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                        <div class="currency-input">
                            <span>Rp</span>
                            <input type="text" id="price_input" name="price" value="{{ number_format($package->price, 0, ',', '.') }}" class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DP (Opsional)</label>
                        <div class="currency-input">
                            <span>Rp</span>
                            <input type="text" id="down_payment_input" name="down_payment" value="{{ $package->down_payment ? number_format($package->down_payment, 0, ',', '.') : '' }}" class="w-full px-3 py-2 pl-8 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Durasi (Jam)</label>
                        <input type="number" name="duration_hours" value="{{ $package->duration_hours }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Foto Edit</label>
                        <input type="number" name="edited_photos" value="{{ $package->edited_photos }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                        <select name="location_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="studio" {{ $package->location_type == 'studio' ? 'selected' : '' }}>Studio</option>
                            <option value="outdoor" {{ $package->location_type == 'outdoor' ? 'selected' : '' }}>Outdoor</option>
                            <option value="both" {{ $package->location_type == 'both' ? 'selected' : '' }}>Studio & Outdoor</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center gap-3">
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" value="1" {{ $package->is_active ? 'checked' : '' }}>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="text-sm text-gray-600" id="status_label">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Yang Termasuk dengan Badge -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Yang Termasuk</label>
                    <input type="text" id="inclusions_input" class="w-full px-3 py-2 border border-gray-300 rounded-lg" placeholder="Ketik lalu tekan Enter atau koma">
                    <div id="inclusions_badge" class="badge-container"></div>
                    <input type="hidden" name="inclusions" id="inclusions_hidden" value='{{ json_encode($package->inclusions) }}'>
                </div>
                
                <!-- Main Image -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Utama</label>
                    
                    @if($package->image)
                    <div class="mb-3">
                        <div class="existing-gallery-item">
                            <img src="{{ Storage::url($package->image) }}">
                            <button type="button" class="remove-btn" onclick="removeExistingMainImage()">✕</button>
                        </div>
                        <div class="info-text">Gambar saat ini (klik X untuk menghapus)</div>
                        <input type="hidden" id="remove_main_image" name="remove_main_image" value="0">
                    </div>
                    @endif
                    
                    <input type="file" id="main_image" name="image" accept="image/*" class="hidden">
                    <div id="main_image_preview" class="image-preview" onclick="document.getElementById('main_image').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                        <p class="text-sm text-gray-500 mt-1">Klik untuk ganti gambar</p>
                        <p class="text-xs text-gray-400">JPG, PNG, maks 2MB</p>
                    </div>
                </div>
                
                <!-- Existing Gallery -->
                @if($package->galleries->count() > 0)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gallery Saat Ini</label>
                    <div class="existing-gallery" id="existing_gallery">
                        @foreach($package->galleries as $gallery)
                        <div class="existing-gallery-item" data-id="{{ $gallery->id }}">
                            <img src="{{ Storage::url($gallery->image) }}">
                            <button type="button" class="remove-btn" onclick="deleteExistingGalleryImage({{ $gallery->id }}, this)">✕</button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                <!-- New Gallery Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Gallery Baru</label>
                    <input type="file" id="gallery_input" name="gallery_images[]" accept="image/*" multiple class="hidden">
                    <div id="gallery_upload_btn" class="image-preview" onclick="document.getElementById('gallery_input').click()">
                        <i class="fas fa-images text-3xl text-gray-400"></i>
                        <p class="text-sm text-gray-500 mt-1">Klik untuk upload gallery baru</p>
                        <p class="text-xs text-gray-400">Bisa pilih beberapa gambar</p>
                    </div>
                    <div id="gallery_preview" class="gallery-preview"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="flex justify-end gap-3 mt-6">
        <a href="{{ route('admin.packages.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Batal</a>
        <button type="submit" class="px-4 py-2 bg-rose-500 text-white rounded-lg hover:bg-rose-600">Update Paket</button>
    </div>
</form>

<script>
// ============ CURRENCY FORMAT ============
function formatCurrency(value) {
    let number = value.toString().replace(/\D/g, '');
    if (number === '') return '';
    return new Intl.NumberFormat('id-ID').format(parseInt(number));
}

function handleCurrencyInput(input) {
    let rawValue = input.value;
    let formatted = formatCurrency(rawValue);
    input.value = formatted;
}

// Saat form submit, konversi kembali ke angka
document.querySelector('form').addEventListener('submit', function(e) {
    const priceInput = document.getElementById('price_input');
    const dpInput = document.getElementById('down_payment_input');
    
    if (priceInput.value) {
        let numericValue = priceInput.value.replace(/\D/g, '');
        priceInput.value = numericValue;
    }
    if (dpInput.value) {
        let numericValue = dpInput.value.replace(/\D/g, '');
        dpInput.value = numericValue;
    }
});

// Inisialisasi currency untuk nilai awal
const priceInput = document.getElementById('price_input');
const dpInput = document.getElementById('down_payment_input');

if (priceInput.value) {
    priceInput.value = formatCurrency(priceInput.value);
}
if (dpInput.value && dpInput.value !== '0') {
    dpInput.value = formatCurrency(dpInput.value);
}

priceInput.addEventListener('input', function() { handleCurrencyInput(this); });
dpInput.addEventListener('input', function() { handleCurrencyInput(this); });

// ============ TOGGLE SLIDER ============
const toggleCheckbox = document.querySelector('.toggle-switch input');
const statusLabel = document.getElementById('status_label');

if (toggleCheckbox) {
    toggleCheckbox.addEventListener('change', function() {
        statusLabel.textContent = this.checked ? 'Aktif' : 'Nonaktif';
    });
}

// ============ INCLUSIONS BADGE ==========
let inclusionsItems = [];
const inclusionsInput = document.getElementById('inclusions_input');
const inclusionsBadge = document.getElementById('inclusions_badge');
const inclusionsHidden = document.getElementById('inclusions_hidden');

// Load existing inclusions dari hidden input
function loadExistingInclusions() {
    try {
        let existingValue = inclusionsHidden.value;
        console.log('Raw inclusions value:', existingValue);
        
        if (existingValue && existingValue !== 'null' && existingValue !== 'undefined') {
            // Coba parse sebagai JSON
            let parsed = JSON.parse(existingValue);
            if (Array.isArray(parsed)) {
                inclusionsItems = parsed;
            } else if (typeof parsed === 'string') {
                // Jika masih string, coba parse lagi
                let doubleParse = JSON.parse(parsed);
                if (Array.isArray(doubleParse)) {
                    inclusionsItems = doubleParse;
                } else {
                    inclusionsItems = [];
                }
            } else {
                inclusionsItems = [];
            }
        } else {
            inclusionsItems = [];
        }
    } catch(e) {
        console.log('Parse error:', e);
        // Jika gagal parse, cek apakah itu array string biasa
        if (existingValue && existingValue.startsWith('[')) {
            try {
                inclusionsItems = JSON.parse(existingValue);
            } catch(e2) {
                inclusionsItems = [];
            }
        } else {
            inclusionsItems = [];
        }
    }
    
    console.log('Loaded inclusions:', inclusionsItems);
    updateInclusionsBadge();
}

function updateInclusionsBadge() {
    if (!inclusionsBadge) return;
    inclusionsBadge.innerHTML = '';
    
    inclusionsItems.forEach((item, index) => {
        const badge = document.createElement('div');
        badge.className = 'badge';
        badge.innerHTML = `${item} <span class="remove-badge" data-index="${index}" style="cursor:pointer;">✕</span>`;
        inclusionsBadge.appendChild(badge);
    });
    
    // Update hidden input dengan JSON
    inclusionsHidden.value = JSON.stringify(inclusionsItems);
    console.log('Updated inclusions:', inclusionsItems);
}

// Tambah item dari input
function addInclusionItem(value) {
    value = value.trim();
    if (value && !inclusionsItems.includes(value)) {
        inclusionsItems.push(value);
        updateInclusionsBadge();
        return true;
    }
    return false;
}

// Event: Enter atau koma
if (inclusionsInput) {
    inclusionsInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            let value = this.value;
            if (addInclusionItem(value)) {
                this.value = '';
            }
        }
    });
    
    inclusionsInput.addEventListener('blur', function() {
        let value = this.value;
        if (value) {
            if (addInclusionItem(value)) {
                this.value = '';
            }
        }
    });
}

// Hapus badge
document.addEventListener('click', function(e) {
    if (e.target.classList && e.target.classList.contains('remove-badge')) {
        const index = parseInt(e.target.dataset.index);
        if (!isNaN(index)) {
            inclusionsItems.splice(index, 1);
            updateInclusionsBadge();
        }
    }
});

// Load existing inclusions saat halaman load
loadExistingInclusions();

// ============ MAIN IMAGE ==========
const mainImageInput = document.getElementById('main_image');
const mainPreviewDiv = document.getElementById('main_image_preview');

function removeExistingMainImage() {
    document.getElementById('remove_main_image').value = '1';
    const existingDiv = document.querySelector('#mainImageContainer .existing-gallery-item, .existing-gallery-item');
    if (existingDiv) existingDiv.remove();
    Swal.fire('Info', 'Gambar utama akan dihapus saat menyimpan', 'info');
}

if (mainImageInput) {
    mainImageInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const file = e.target.files[0];
            if (!file.type.startsWith('image/')) { alert('File harus gambar!'); return; }
            if (file.size > 2 * 1024 * 1024) { alert('Ukuran maksimal 2MB!'); return; }
            
            const reader = new FileReader();
            reader.onload = function(evt) {
                mainPreviewDiv.innerHTML = `
                    <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                    <p class="text-sm text-gray-500 mt-1">Klik untuk ganti gambar</p>
                    <img src="${evt.target.result}" class="preview-img">
                `;
            };
            reader.readAsDataURL(file);
        }
    });
}

// ============ GALLERY ==========
const galleryInput = document.getElementById('gallery_input');
const galleryPreview = document.getElementById('gallery_preview');
let galleryFiles = [];

function updateGalleryPreview() {
    galleryPreview.innerHTML = '';
    galleryFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement('div');
            div.className = 'gallery-item';
            div.innerHTML = `
                <img src="${e.target.result}">
                <button type="button" class="remove-btn" onclick="removeNewGalleryImage(${index})">✕</button>
            `;
            galleryPreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeNewGalleryImage(index) {
    galleryFiles.splice(index, 1);
    updateGalleryPreview();
    updateGalleryInput();
}

function updateGalleryInput() {
    const dt = new DataTransfer();
    galleryFiles.forEach(file => dt.items.add(file));
    galleryInput.files = dt.files;
}

if (galleryInput) {
    galleryInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            if (file.type.startsWith('image/') && file.size <= 2 * 1024 * 1024) {
                galleryFiles.push(file);
            } else {
                alert('File harus gambar dan maksimal 2MB!');
            }
        });
        updateGalleryPreview();
        updateGalleryInput();
    });
}

// ============ DELETE EXISTING GALLERY ==========
function deleteExistingGalleryImage(id, btn) {
    Swal.fire({
        title: 'Hapus Gambar?',
        text: 'Gambar akan dihapus dari gallery',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/package-gallery/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.closest('.existing-gallery-item').remove();
                    Swal.fire('Terhapus!', 'Gambar berhasil dihapus', 'success');
                }
            });
        }
    });
}
</script>
@endsection