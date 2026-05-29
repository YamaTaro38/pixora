{{-- resources/views/admin/customers/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Manajemen Customer')
@section('subtitle', 'Kelola data customer dan blokir akses')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Customer</p>
                <p class="text-3xl font-bold text-gray-800">{{ $stats['total'] ?? 0 }}</p>
                <p class="text-xs text-green-500 mt-1">+12% bulan ini</p>
            </div>
            <div class="w-12 h-12 bg-rose-100 rounded-full flex items-center justify-center">
                <i class="fas fa-users text-rose-500 text-xl"></i>
            </div>
        </div>
        
        <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Customer Aktif</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['active'] ?? 0 }}</p>
                <p class="text-xs text-green-500 mt-1">Dapat login & booking</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-check text-green-500 text-xl"></i>
            </div>
        </div>
        
        <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Customer Diblokir</p>
                <p class="text-3xl font-bold text-red-600">{{ $stats['blocked'] ?? 0 }}</p>
                <p class="text-xs text-red-500 mt-1">Tidak bisa login</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <i class="fas fa-user-slash text-red-500 text-xl"></i>
            </div>
        </div>
        
        <div class="glass-card rounded-2xl p-5 flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm font-medium">Booking Terbanyak</p>
                <p class="text-3xl font-bold text-rose-600">{{ $stats['top_bookings'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Customer aktif</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <i class="fas fa-trophy text-purple-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="bg-white rounded-2xl shadow-sm p-5">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Cari Customer</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="search" placeholder="Nama, email, atau no HP..." 
                           value="{{ request('search') }}" 
                           class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 transition-all text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-200">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Semua Customer</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="blocked" {{ request('status') == 'blocked' ? 'selected' : '' }}>Diblokir</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sortir</label>
                <select name="sort" class="border border-gray-200 rounded-xl px-4 py-2 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-200">
                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nama A-Z</option>
                    <option value="most_booking" {{ request('sort') == 'most_booking' ? 'selected' : '' }}>Booking Terbanyak</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-rose-500 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-rose-600 transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.customers.index') }}" class="inline-block ml-2 bg-gray-100 text-gray-600 px-5 py-2 rounded-xl text-sm font-semibold hover:bg-gray-200 transition-all">
                    <i class="fas fa-undo mr-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Customer Table -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Bergabung</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total Booking</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $index => $customer)
                    <tr class="hover:bg-rose-50/30 transition-all duration-200">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-500">{{ $index + 1 }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold shadow-sm">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $customer->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $customer->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <i class="fab fa-whatsapp text-green-500 text-sm"></i>
                                <span class="text-sm text-gray-600">{{ $customer->phone ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $customer->created_at->format('d/m/Y') }}
                            <div class="text-xs text-gray-400">{{ $customer->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1">
                                <span class="text-sm font-semibold text-rose-600">{{ $customer->bookings_count ?? 0 }}</span>
                                <span class="text-xs text-gray-400">booking</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($customer->is_blocked)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                    <i class="fas fa-ban text-xs"></i> Diblokir
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                    <i class="fas fa-check-circle text-xs"></i> Aktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" 
                                   class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-all flex items-center justify-center group">
                                    <i class="fas fa-eye text-sm group-hover:scale-110 transition"></i>
                                </a>
                                <button onclick="toggleBlock({{ $customer->id }}, '{{ $customer->name }}', {{ $customer->is_blocked ? 'true' : 'false' }})" 
                                        class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 transition-all flex items-center justify-center group">
                                    <i class="fas {{ $customer->is_blocked ? 'fa-user-check' : 'fa-user-slash' }} text-sm group-hover:scale-110 transition"></i>
                                </button>
                                <button onclick="confirmDelete({{ $customer->id }}, '{{ $customer->name }}')" 
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-all flex items-center justify-center group">
                                    <i class="fas fa-trash text-sm group-hover:scale-110 transition"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-users-slash text-5xl text-gray-300 mb-3"></i>
                                <p class="text-gray-400 font-medium">Tidak ada data customer</p>
                                <p class="text-xs text-gray-300 mt-1">Belum ada customer yang terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $customers->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleBlock(id, name, isBlocked) {
    let title = isBlocked ? 'Unblock Customer?' : 'Blokir Customer?';
    let text = isBlocked ? `Yakin ingin mengaktifkan kembali <strong>${name}</strong>?` : `Yakin ingin memblokir <strong>${name}</strong>? Customer tidak bisa login dan booking.`;
    let confirmText = isBlocked ? 'Ya, Unblock!' : 'Ya, Blokir!';
    
    Swal.fire({
        title: title,
        html: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg px-4 py-2',
            cancelButton: 'rounded-lg px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/customers/${id}/toggle-block`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#e11d48',
                    timer: 2000,
                    showConfirmButton: true,
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                }).then(() => {
                    location.reload();
                });
            });
        }
    });
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Customer?',
        html: `Customer <strong class="text-red-600">${name}</strong> akan dihapus permanen beserta semua bookingnya.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg px-4 py-2',
            cancelButton: 'rounded-lg px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/customers/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: 'Terhapus!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#e11d48',
                    timer: 2000,
                    showConfirmButton: true,
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                }).then(() => {
                    location.reload();
                });
            });
        }
    });
}
</script>
@endpush
@endsection