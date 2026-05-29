{{-- resources/views/booking-payment.blade.php --}}

@extends('layouts.app')

@section('title', 'Pembayaran Booking - Pixora')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">
        
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Pembayaran Booking</h1>
            <p class="text-gray-500">Silakan selesaikan pembayaran Anda</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Ringkasan Booking</h2>
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-gray-500">Kode Booking</p><p class="font-bold">{{ $booking->booking_code }}</p></div>
                <div><p class="text-gray-500">Total</p><p class="font-bold text-rose-600">Rp {{ number_format($amountToPay, 0, ',', '.') }}</p></div>
                <div><p class="text-gray-500">Batas Bayar</p><p class="font-bold text-red-600">{{ Carbon\Carbon::parse($booking->expires_at)->format('H:i') }} WIB</p></div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
            <button id="pay-button" class="bg-rose-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-rose-600 transition">
                <i class="fas fa-credit-card mr-2"></i> Pilih Metode Pembayaran
            </button>
        </div>
        
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ $clientKey }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const payButton = document.getElementById('pay-button');
    const snapToken = '{{ $snapToken }}';
    const showUrl = '{{ route("booking.show", $booking->public_token) }}';
    const statusUrl = '{{ route("booking.check-status", $booking->public_token) }}';
    
    let statusCheckInterval = null;
    let isProcessing = false;
    
    payButton.addEventListener('click', function() {
        if (isProcessing) return;
        isProcessing = true;
        
        snap.pay(snapToken, {
            onSuccess: function(result) {
                console.log('Payment Success:', result);
                showPaymentProcessing();
                startPolling();
            },
            onPending: function(result) {
                console.log('Payment Pending:', result);
                Swal.fire({
                    title: 'Menunggu Pembayaran',
                    text: 'Pembayaran Anda sedang diproses. Silakan selesaikan pembayaran Anda.',
                    icon: 'info',
                    confirmButtonColor: '#e11d48',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = showUrl;
                });
            },
            onError: function(result) {
                console.log('Payment Error:', result);
                Swal.fire({
                    title: 'Pembayaran Gagal',
                    text: result.status_message || 'Terjadi kesalahan. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonColor: '#e11d48',
                    confirmButtonText: 'Coba Lagi'
                });
                isProcessing = false;
            },
            onClose: function() {
                console.log('Payment popup closed');
                isProcessing = false;
            }
        });
    });
    
    function showPaymentProcessing() {
        Swal.fire({
            title: 'Memproses Pembayaran',
            text: 'Mohon tunggu, kami sedang memverifikasi pembayaran Anda...',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    function startPolling() {
        let attempts = 0;
        const maxAttempts = 30; // 30 x 2 detik = 60 detik
        
        statusCheckInterval = setInterval(async function() {
            attempts++;
            
            try {
                const response = await fetch(statusUrl);
                const data = await response.json();
                
                console.log('Status check attempt', attempts, ':', data);
                
                if (data.is_paid) {
                    clearInterval(statusCheckInterval);
                    
                    Swal.fire({
                        title: 'Pembayaran Berhasil!',
                        html: `
                            <div class="text-center">
                                <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
                                <p class="text-gray-600">Pembayaran Anda telah berhasil dikonfirmasi.</p>
                                <p class="text-gray-600 mt-2">Metode Pembayaran: <strong>${data.payment_method || 'Tidak diketahui'}</strong></p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Lihat Detail Booking',
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = showUrl + '?payment=success';
                    });
                    
                } else if (data.is_expired) {
                    clearInterval(statusCheckInterval);
                    
                    Swal.fire({
                        title: 'Pembayaran Kadaluarsa',
                        text: 'Waktu pembayaran telah habis. Silakan booking ulang.',
                        icon: 'error',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = showUrl;
                    });
                    
                } else if (attempts >= maxAttempts) {
                    clearInterval(statusCheckInterval);
                    
                    Swal.fire({
                        title: 'Waktu Habis',
                        text: 'Pembayaran belum terkonfirmasi. Silakan cek status booking Anda nanti.',
                        icon: 'warning',
                        confirmButtonColor: '#e11d48',
                        confirmButtonText: 'Lihat Booking'
                    }).then(() => {
                        window.location.href = showUrl;
                    });
                }
            } catch (error) {
                console.error('Status check error:', error);
            }
        }, 2000);
    }
</script>
@endsection