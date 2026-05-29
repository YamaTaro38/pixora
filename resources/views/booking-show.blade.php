{{-- resources/views/booking-show.blade.php --}}

@extends('layouts.app')

@section('title', 'Detail Booking - Pixora')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Detail Booking</h1>
            <p class="text-gray-500">Simpan halaman ini sebagai bukti booking Anda</p>
        </div>
        
        <!-- Status Banner -->
        @php
            $statusColor = 'yellow';
            $statusIcon = 'fa-hourglass-half';
            $statusText = 'Menunggu Pembayaran';
            
            if ($booking->payment_status == 'lunas') {
                $statusColor = 'green';
                $statusIcon = 'fa-check-circle';
                $statusText = 'Lunas';
            } elseif ($booking->payment_status == 'dp_paid') {
                $statusColor = 'blue';
                $statusIcon = 'fa-money-bill-wave';
                $statusText = 'DP Lunas';
            } elseif ($booking->payment_status == 'expired') {
                $statusColor = 'red';
                $statusIcon = 'fa-times-circle';
                $statusText = 'Kadaluarsa';
            }
        @endphp
        
        <div class="bg-{{ $statusColor }}-50 border-l-4 border-{{ $statusColor }}-500 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas {{ $statusIcon }} text-{{ $statusColor }}-500 text-xl mr-3"></i>
                <div>
                    <p class="font-semibold text-{{ $statusColor }}-700">Status: {{ $statusText }}</p>
                    @if($booking->payment_status == 'pending')
                    <p class="text-sm text-gray-600">Batas bayar: {{ Carbon\Carbon::parse($booking->expires_at)->format('H:i') }} WIB</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Booking Details -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-rose-500 to-pink-600 px-6 py-4">
                <h2 class="text-white font-bold text-xl">Informasi Booking</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-500 text-sm">Kode Booking</p>
                        <p class="font-bold text-lg text-rose-600">{{ $booking->booking_code }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Paket</p>
                        <p class="font-semibold">{{ $booking->package->name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Tanggal Sesi</p>
                        <p class="font-semibold">{{ Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Jam Sesi</p>
                        <p class="font-semibold">{{ $booking->timeSlotLabel }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Harga</p>
                        <p class="font-bold text-xl text-rose-600">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Metode Pembayaran</p>
                        <p class="font-semibold">
                            @if($booking->payment_method && $booking->payment_method != 'Belum dipilih')
                                <span class="inline-flex items-center gap-1">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                    {{ $booking->payment_method }}
                                </span>
                            @else
                                <span class="text-gray-400">Belum dipilih</span>
                            @endif
                        </p>
                    </div>
                </div>
                
                @if($booking->payment_status == 'pending')
                <div class="mt-6 pt-4 border-t">
                    <a href="{{ route('booking.payment', $booking->public_token) }}" 
                       class="inline-block bg-rose-500 text-white px-6 py-3 rounded-xl font-semibold hover:bg-rose-600 transition">
                        <i class="fas fa-credit-card mr-2"></i> Lanjutkan Pembayaran
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 justify-center">
            @if(in_array($booking->payment_status, ['lunas', 'dp_paid']))
            <a href="{{ route('booking.download-invoice', $booking->public_token) }}" 
            class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-xl font-semibold transition inline-flex items-center gap-2">
                <i class="fas fa-download mr-2"></i>
                Download Invoice (PDF)
            </a>
            @endif
            
            <button onclick="copyToClipboard()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-xl font-semibold transition inline-flex items-center gap-2">
                <i class="fas fa-link mr-2"></i>
                Salin Link
            </button>
            
            <a href="{{ route('calendar') }}" class="bg-rose-500 hover:bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold transition inline-flex items-center gap-2">
                <i class="fas fa-calendar-alt mr-2"></i>
                Booking Lagi
            </a>
        </div>
        
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href);
    Swal.fire({
        title: 'Berhasil!',
        text: 'Link booking telah disalin ke clipboard.',
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });
}

// Cek parameter URL untuk notifikasi
const urlParams = new URLSearchParams(window.location.search);
const paymentStatus = urlParams.get('payment');

if (paymentStatus === 'success') {
    Swal.fire({
        title: 'Pembayaran Berhasil!',
        html: `
            <div class="text-center">
                <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                <p class="text-gray-600">Terima kasih telah booking di Pixora Studio.</p>
                <p class="text-gray-600 mt-2">Booking Anda sudah aktif.</p>
            </div>
        `,
        icon: 'success',
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'OK'
    }).then(() => {
        // Hapus parameter dari URL
        window.history.replaceState({}, document.title, window.location.pathname);
    });
} else if (paymentStatus === 'pending') {
    Swal.fire({
        title: 'Menunggu Pembayaran',
        text: 'Pembayaran Anda sedang diproses. Silakan cek secara berkala.',
        icon: 'info',
        confirmButtonColor: '#e11d48',
        confirmButtonText: 'OK'
    }).then(() => {
        window.history.replaceState({}, document.title, window.location.pathname);
    });
}
</script>
@endsection