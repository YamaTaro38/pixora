{{-- resources/views/booking-expired.blade.php --}}
@extends('layouts.app')

@section('title', 'Booking Kadaluarsa - Pixora')

@section('content')
<div class="container mx-auto px-4 py-20">
    <div class="max-w-md mx-auto text-center">
        <div class="bg-red-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-clock text-red-600 text-5xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Booking Kadaluarsa</h1>
        <p class="text-gray-600 mb-6">
            Maaf, waktu pembayaran untuk booking ini sudah habis.<br>
            Silakan lakukan booking ulang.
        </p>
        <a href="{{ route('calendar') }}" class="bg-rose-500 hover:bg-rose-600 text-white px-6 py-3 rounded-xl font-semibold transition-all inline-flex items-center">
            <i class="fas fa-calendar-alt mr-2"></i> Booking Ulang
        </a>
    </div>
</div>
@endsection