{{-- resources/views/admin/customers/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Detail Customer')
@section('subtitle', 'Informasi lengkap customer')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden sticky top-24">
            <div class="bg-gradient-to-r from-rose-500 to-pink-600 px-6 py-8 text-center">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-user text-4xl text-white"></i>
                </div>
                <h3 class="text-xl font-bold text-white">{{ $customer->name }}</h3>
                <p class="text-white/80 text-sm">{{ $customer->email }}</p>
            </div>
            <div class="p-6 space-y-3">
                <div class="flex items-center gap-3">
                    <i class="fas fa-phone w-5 text-gray-400"></i>
                    <span>{{ $customer->phone ?? '-' }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-calendar-alt w-5 text-gray-400"></i>
                    <span>Bergabung: {{ $customer->created_at->format('d F Y') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-google w-5 text-gray-400"></i>
                    <span>{{ $customer->google_id ? 'Terhubung dengan Google' : 'Registrasi manual' }}</span>
                </div>
                <div class="pt-4 border-t">
                    <div class="flex gap-2">
                        <button onclick="toggleBlock({{ $customer->id }}, '{{ $customer->name }}', {{ $customer->is_blocked ? 'true' : 'false' }})" 
                                class="flex-1 py-2 rounded-lg {{ $customer->is_blocked ? 'bg-green-500 hover:bg-green-600' : 'bg-yellow-500 hover:bg-yellow-600' }} text-white">
                            <i class="fas {{ $customer->is_blocked ? 'fa-user-check' : 'fa-user-slash' }} mr-1"></i>
                            {{ $customer->is_blocked ? 'Unblock' : 'Blokir' }}
                        </button>
                        <button onclick="confirmDelete({{ $customer->id }}, '{{ $customer->name }}')" 
                                class="flex-1 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white">
                            <i class="fas fa-trash mr-1"></i> Hapus
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking History -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-calendar-check text-rose-500 mr-2"></i>
                    Riwayat Booking
                </h3>
            </div>
            
            <div class="p-6">
                @if($bookings->count() > 0)
                <div class="space-y-3">
                    @foreach($bookings as $booking)
                    <div class="border rounded-xl p-4 flex justify-between items-center">
                        <div>
                            <p class="font-semibold">{{ $booking->package->name }}</p>
                            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }} • {{ $booking->timeSlotLabel }}</p>
                            <p class="text-xs text-gray-400">Kode: {{ $booking->booking_code }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-rose-600">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                            <span class="text-xs px-2 py-1 rounded-full 
                                @if($booking->payment_status == 'lunas') bg-green-100 text-green-700
                                @elseif($booking->payment_status == 'pending') bg-yellow-100 text-yellow-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    {{ $bookings->links() }}
                </div>
                @else
                <p class="text-center text-gray-500 py-8">Belum ada booking</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleBlock(id, name, isBlocked) {
    let title = isBlocked ? 'Unblock Customer?' : 'Blokir Customer?';
    let text = isBlocked ? `Yakin ingin mengaktifkan kembali ${name}?` : `Yakin ingin memblokir ${name}?`;
    let confirmText = isBlocked ? 'Ya, Unblock!' : 'Ya, Blokir!';
    
    Swal.fire({
        title: title,
        html: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Batal'
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
                Swal.fire('Berhasil!', data.message, 'success').then(() => {
                    location.reload();
                });
            });
        }
    });
}

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Hapus Customer?',
        html: `Customer <strong>${name}</strong> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
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
                Swal.fire('Terhapus!', data.message, 'success').then(() => {
                    window.location.href = '{{ route("admin.customers.index") }}';
                });
            });
        }
    });
}
</script>
@endpush
@endsection