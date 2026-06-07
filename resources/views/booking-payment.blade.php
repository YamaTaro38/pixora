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
            <div class="flex flex-col gap-4 max-w-sm mx-auto">
                <button id="pay-button" class="bg-rose-500 text-white px-8 py-3 rounded-xl font-semibold hover:bg-rose-600 transition flex items-center justify-center">
                    <i class="fas fa-credit-card mr-2"></i> Bayar Online (Midtrans)
                </button>
                
                <button id="cod-button" class="bg-gray-800 text-white px-8 py-3 rounded-xl font-semibold hover:bg-gray-900 transition flex items-center justify-center">
                    <i class="fas fa-store mr-2"></i> Bayar di Studio (COD)
                </button>
            </div>
        </div>
        
    </div>
</div>

<!-- Success overlay (hidden by default) -->
<div id="success-overlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden">
    <div class="bg-white rounded-3xl shadow-2xl p-10 max-w-md w-full mx-4 text-center transform scale-95 transition-all duration-500" id="success-card">
        <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-check-circle text-green-500 text-5xl"></i>
        </div>
        <h2 class="text-3xl font-bold text-gray-800 mb-2">🎉 Selamat!</h2>
        <h3 class="text-xl font-semibold text-green-600 mb-4">Booking Anda Berhasil!</h3>
        <p class="text-gray-600 mb-2">Terima kasih telah booking di <strong>Pixora Studio</strong>.</p>
        <p class="text-gray-500 text-sm mb-6" id="success-detail"></p>
        <a id="success-link" href="#" class="inline-block bg-gradient-to-r from-rose-500 to-pink-600 text-white px-8 py-3 rounded-xl font-semibold hover:shadow-lg transition transform hover:scale-105">
            <i class="fas fa-eye mr-2"></i> Lihat Detail Booking
        </a>
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
    
    // Show beautiful success overlay
    function showSuccessOverlay(detail) {
        const overlay = document.getElementById('success-overlay');
        const card = document.getElementById('success-card');
        const detailEl = document.getElementById('success-detail');
        const link = document.getElementById('success-link');
        
        detailEl.textContent = detail || 'Booking Anda sudah aktif dan terkonfirmasi.';
        link.href = showUrl + '?payment=success';
        
        overlay.classList.remove('hidden');
        setTimeout(() => {
            card.classList.remove('scale-95');
            card.classList.add('scale-100');
        }, 100);
    }
    
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
                    startPolling();
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
                    Swal.close();
                    
                    // Show beautiful success overlay
                    const methodText = data.payment_method ? `Metode Pembayaran: ${data.payment_method}` : 'Pembayaran Anda telah berhasil dikonfirmasi.';
                    showSuccessOverlay(methodText);
                    
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

    const codButton = document.getElementById('cod-button');
    const codUrl = '{{ route("booking.cod", $booking->public_token) }}';

    if (codButton) {
        codButton.addEventListener('click', function() {
            if (isProcessing) return;
            
            Swal.fire({
                title: 'Konfirmasi COD',
                text: 'Apakah Anda yakin ingin membayar langsung di studio (Cash on Delivery)?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#4b5563',
                confirmButtonText: 'Ya, Bayar di Studio',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    isProcessing = true;
                    showPaymentProcessing();
                    
                    fetch(codUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.close();
                            showSuccessOverlay('Silakan lakukan pembayaran saat Anda tiba di studio.');
                        } else {
                            throw new Error(data.message || 'Terjadi kesalahan');
                        }
                    })
                    .catch(error => {
                        isProcessing = false;
                        Swal.fire({
                            title: 'Gagal',
                            text: error.message,
                            icon: 'error',
                            confirmButtonColor: '#e11d48',
                        });
                    });
                }
            });
        });
    }
</script>
@endsection