{{-- resources/views/admin/landing-page/partials/testimonials.blade.php --}}
<div class="space-y-4">
    <form method="POST" action="{{ route('admin.landing-page.testimonials') }}" id="testimonialsForm">
        @csrf
        <div id="testimonialsContainer" class="space-y-4">
            @foreach($contents['testimonials'] as $index => $testimonial)
            <div class="testimonial-item border rounded-xl p-4 bg-gray-50">
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
        
        <button type="button" onclick="addTestimonial()" class="text-rose-500 text-sm hover:text-rose-600 transition mt-3">
            <i class="fas fa-plus mr-1"></i> Tambah Testimonial
        </button>
        
        <div class="pt-3">
            <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-rose-600 transition">Simpan Testimonials</button>
        </div>
    </form>
</div>

<script>
let testimonialCounter = {{ count($contents['testimonials']) }};

function addTestimonial() {
    const container = document.getElementById('testimonialsContainer');
    const newIndex = testimonialCounter++;
    
    const div = document.createElement('div');
    div.className = 'testimonial-item border rounded-xl p-4 bg-gray-50';
    div.innerHTML = `
        <div class="flex justify-between items-start mb-3">
            <h4 class="font-semibold text-gray-700">Testimonial #${newIndex + 1}</h4>
            <button type="button" onclick="removeTestimonial(this)" class="text-red-500 text-sm hover:text-red-600">Hapus</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" name="testimonial_name[]" class="w-full rounded-lg px-3 py-2 border border-gray-200">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Rating</label>
                <select name="testimonial_rating[]" class="w-full rounded-lg px-3 py-2 border border-gray-200">
                    <option value="5">★★★★★ (5)</option>
                    <option value="4">★★★★☆ (4)</option>
                    <option value="3">★★★☆☆ (3)</option>
                </select>
            </div>
        </div>
        <div class="mt-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Testimoni</label>
            <textarea name="testimonial_text[]" rows="2" class="w-full rounded-lg px-3 py-2 border border-gray-200"></textarea>
        </div>
        <div class="mt-3">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Foto Profile</label>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <input type="file" class="testimonial-photo-input hidden" accept="image/*" data-index="new_${newIndex}">
                    <button type="button" onclick="uploadTestimonialPhoto(this, 'new_${newIndex}')" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm transition">Pilih Foto</button>
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
    btn.closest('.relative').remove();
    const photoValue = btn.closest('.testimonial-item').querySelector('.photo-value');
    if (photoValue) photoValue.value = '';
}

function uploadTestimonialPhoto(btn, index) {
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
            text: 'Mohon tunggu',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch('{{ route("admin.landing-page.upload-testimonial-photo") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                // Remove existing photo preview
                const existingPreview = testimonialItem.querySelector('.relative');
                if (existingPreview) existingPreview.remove();
                
                // Add new photo preview
                const photoPreview = document.createElement('div');
                photoPreview.className = 'relative group';
                photoPreview.innerHTML = `
                    <img src="${data.photo_url}" class="w-12 h-12 rounded-full object-cover border-2 border-rose-200">
                    <button type="button" onclick="removeTestimonialPhoto(this)" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">✕</button>
                `;
                btn.parentElement.insertBefore(photoPreview, btn);
                
                // Update hidden input
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
</script>