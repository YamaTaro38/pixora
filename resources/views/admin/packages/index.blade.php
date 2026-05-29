{{-- resources/views/admin/packages/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Manajemen Paket')
@section('subtitle', 'Kelola paket fotografi yang tersedia')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Daftar Paket Fotografi</h3>
            <p class="text-sm text-gray-500 mt-1">Kelola semua paket yang tersedia untuk customer</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="bg-rose-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-600 transition-all inline-flex items-center gap-2 shadow-sm">
            <i class="fas fa-plus text-xs"></i>
            <span>Tambah Paket</span>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-rose-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Total Paket</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $packages->total() }}</p>
                </div>
                <div class="w-10 h-10 bg-rose-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-box text-rose-500 text-sm"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Paket Aktif</p>
                    <p class="text-2xl font-bold text-green-600">{{ $packages->where('is_active', true)->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-500 text-sm"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Paket Nonaktif</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $packages->where('is_active', false)->count() }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-ban text-yellow-500 text-sm"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Paling Banyak Dibooking</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $packages->max('bookings_count') ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-blue-500 text-sm"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl shadow-sm p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Paket</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" placeholder="Nama paket..." value="{{ request('search') }}" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-rose-500 focus:ring-1 focus:ring-rose-500">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-rose-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-rose-600 transition">
                    <i class="fas fa-search mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.packages.index') }}" class="inline-block ml-2 bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                    <i class="fas fa-undo-alt mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Packages Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Gambar</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Paket</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Harga</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Durasi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Booking</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($packages as $index => $package)
                    @php
                        $bookingCount = $package->bookings_count ?? $package->bookings()->count();
                        $hasBookings = $bookingCount > 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition-all duration-200">
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $packages->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            @if($package->image)
                            <img src="{{ Storage::url($package->image) }}" class="w-10 h-10 rounded-lg object-cover shadow-sm">
                            @else
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-sm"></i>
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-800">{{ $package->name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5 line-clamp-1 max-w-xs">{{ Str::limit($package->description, 50) }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm font-semibold text-rose-600">Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $package->duration_hours }} jam</td>
                        <td class="px-4 py-3">
                            @if($hasBookings)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <i class="fas fa-calendar-check text-xs"></i> {{ $bookingCount }} booking
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Belum ada booking</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($package->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <i class="fas fa-circle text-[6px]"></i> Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <i class="fas fa-circle text-[6px]"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <!-- Edit Button - Selalu ada -->
                                <a href="{{ route('admin.packages.edit', $package->id) }}" 
                                class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all flex items-center justify-center group"
                                title="Edit paket">
                                    <i class="fas fa-edit text-sm group-hover:scale-110 transition"></i>
                                </a>
                                
                                @php
                                    $bookingCount = $package->bookings_count ?? $package->bookings()->count();
                                    $hasBookings = $bookingCount > 0;
                                    $isActive = $package->is_active;
                                @endphp
                                
                                <!-- Delete/Deactivate Button Logic -->
                                @if(!$isActive)
                                    <!-- Jika sudah nonaktif, tidak tampilkan tombol apapun -->
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-400 flex items-center justify-center cursor-not-allowed"
                                        title="Paket sudah nonaktif">
                                        <i class="fas fa-check-circle text-sm"></i>
                                    </div>
                                @elseif($hasBookings)
                                    <!-- Jika aktif dan punya booking, tampilkan tombol nonaktifkan -->
                                    <button onclick="deactivatePackage({{ $package->id }}, '{{ $package->name }}', {{ $bookingCount }})" 
                                            class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition-all flex items-center justify-center group"
                                            title="Paket memiliki {{ $bookingCount }} booking. Tidak bisa dihapus, hanya bisa dinonaktifkan.">
                                        <i class="fas fa-ban text-sm group-hover:scale-110 transition"></i>
                                    </button>
                                @else
                                    <!-- Jika aktif dan tidak punya booking, tampilkan tombol hapus -->
                                    <button onclick="confirmDelete({{ $package->id }}, '{{ $package->name }}')" 
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all flex items-center justify-center group"
                                            title="Hapus paket">
                                        <i class="fas fa-trash text-sm group-hover:scale-110 transition"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-box-open text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-400 font-medium">Belum ada paket</p>
                                <p class="text-xs text-gray-300 mt-1">Klik tombol "Tambah Paket" untuk mulai</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $packages->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Deactivate Form -->
<form id="deactivate-form" method="POST" style="display: none;">
    @csrf
    @method('PUT')
    <input type="hidden" name="deactivate" value="1">
</form>

<script>
// ============ DELETE PACKAGE (Tanpa Booking) ============
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Paket?',
        html: `Paket <strong class="text-rose-600">${name}</strong> akan dihapus permanen.<br><span class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'px-4 py-2 text-sm rounded-lg',
            cancelButton: 'px-4 py-2 text-sm rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('delete-form');
            form.action = `/admin/packages/${id}`;
            form.submit();
        }
    });
}

// ============ DEACTIVATE PACKAGE (Dengan Booking) ============
function deactivatePackage(id, name, bookingCount) {
    Swal.fire({
        title: 'Nonaktifkan Paket?',
        html: `
            <div class="text-left">
                <p class="mb-2">Paket <strong class="text-yellow-600">${name}</strong> memiliki <strong>${bookingCount} booking</strong>.</p>
                <p class="text-sm text-gray-500 mb-2">Paket tidak bisa dihapus karena sudah ada booking.</p>
                <p class="text-sm text-gray-500">Apakah Anda ingin menonaktifkan paket ini?</p>
                <p class="text-xs text-gray-400 mt-2">Paket yang dinonaktifkan tidak akan tampil di website.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Nonaktifkan!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'px-4 py-2 text-sm rounded-lg',
            cancelButton: 'px-4 py-2 text-sm rounded-lg'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('deactivate-form');
            form.action = `/admin/packages/${id}/deactivate`;
            form.submit();
        }
    });
}
</script>
@endsection