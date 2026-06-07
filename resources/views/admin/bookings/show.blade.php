{{-- resources/views/admin/bookings/show.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Detail Booking')

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold">Detail Booking</h3>
    </div>
    
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Informasi Customer</h4>
                <div class="space-y-2">
                    <p><span class="text-gray-500 w-32 inline-block">Nama:</span> {{ $booking->customer_name }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">WhatsApp:</span> +62 {{ $booking->customer_phone }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">Email:</span> {{ $booking->customer_email ?? '-' }}</p>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Informasi Booking</h4>
                <div class="space-y-2">
                    <p><span class="text-gray-500 w-32 inline-block">Kode Booking:</span> {{ $booking->booking_code }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">Paket:</span> {{ $booking->package->name }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">Tanggal Sesi:</span> {{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">Jam Sesi:</span> {{ $booking->timeSlotLabel }}</p>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Informasi Pembayaran</h4>
                <div class="space-y-2">
                    <p><span class="text-gray-500 w-32 inline-block">Total Harga:</span> Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">DP Dibayar:</span> Rp {{ number_format($booking->down_payment, 0, ',', '.') }}</p>
                    <p><span class="text-gray-500 w-32 inline-block">Status:</span> 
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($booking->payment_status == 'lunas') bg-green-100 text-green-700
                            @elseif($booking->payment_status == 'pending') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </p>
                    <p><span class="text-gray-500 w-32 inline-block">Metode:</span> {{ $booking->payment_method ?? 'Belum dipilih' }}</p>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Update Status</h4>
                <form method="POST" action="{{ route('admin.bookings.update-status', $booking->id) }}">
                    @csrf
                    <div class="space-y-3">
                        <select name="payment_status" class="w-full border rounded-lg px-3 py-2">
                            <option value="pending" {{ $booking->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="lunas" {{ $booking->payment_status == 'lunas' ? 'selected' : '' }}>Lunas</option>
                            <option value="dp_paid" {{ $booking->payment_status == 'dp_paid' ? 'selected' : '' }}>DP Lunas</option>
                            <option value="expired" {{ $booking->payment_status == 'expired' ? 'selected' : '' }}>Expired</option>
                        </select>
                        <select name="session_status" class="w-full border rounded-lg px-3 py-2">
                            <option value="upcoming" {{ $booking->session_status == 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
                            <option value="ongoing" {{ $booking->session_status == 'ongoing' ? 'selected' : '' }}>Sedang Berlangsung</option>
                            <option value="completed" {{ $booking->session_status == 'completed' ? 'selected' : '' }}>Selesai</option>
                            <option value="cancelled" {{ $booking->session_status == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        <button type="submit" class="w-full bg-rose-500 text-white py-2 rounded-lg">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
        
        @if($booking->special_requests)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-semibold text-gray-700 mb-2">Catatan Khusus</h4>
            <p class="text-gray-600">{{ $booking->special_requests }}</p>
        </div>
        @endif
        
        <div class="mt-6 flex gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600">
                Kembali
            </a>
            @if($booking->snap_token)
            <a href="https://app.sandbox.midtrans.com/snap/v2/transactions/{{ $booking->snap_token }}" target="_blank" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                Lihat Transaksi Midtrans
            </a>
            @endif
        </div>
    </div>
</div>
@endsection