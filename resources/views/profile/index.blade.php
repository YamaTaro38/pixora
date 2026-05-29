{{-- resources/views/profile/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Profil Saya - Pixora')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Profil Saya</h1>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <div class="w-24 h-24 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-4xl text-rose-500"></i>
                    </div>
                    <h3 class="font-bold text-xl">{{ Auth::user()->name }}</h3>
                    <p class="text-gray-500 text-sm">{{ Auth::user()->email }}</p>
                    <div class="mt-4 pt-4 border-t">
                        <span class="inline-block px-3 py-1 bg-rose-100 text-rose-600 rounded-full text-xs">
                            {{ Auth::user()->role == 'admin' ? 'Administrator' : 'Customer' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="md:col-span-2 space-y-6">
                <!-- Edit Profile Form -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Informasi Profil</h2>
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="name" value="{{ Auth::user()->name }}" class="w-full border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">Email</label>
                                <input type="email" value="{{ Auth::user()->email }}" class="w-full border rounded-xl px-4 py-2 bg-gray-100" readonly disabled>
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">No WhatsApp</label>
                                <input type="tel" name="phone" value="{{ Auth::user()->phone }}" class="w-full border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                        </div>
                        <button type="submit" class="mt-4 bg-rose-500 text-white px-6 py-2 rounded-xl hover:bg-rose-600 transition">
                            Update Profil
                        </button>
                    </form>
                </div>
                
                <!-- Change Password Form (hanya untuk user yang punya password) -->
                @if(Auth::user()->password)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Ubah Password</h2>
                    <form method="POST" action="{{ route('profile.password') }}">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 mb-1">Password Saat Ini</label>
                                <input type="password" name="current_password" class="w-full border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">Password Baru</label>
                                <input type="password" name="password" class="w-full border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="w-full border rounded-xl px-4 py-2 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            </div>
                        </div>
                        <button type="submit" class="mt-4 bg-rose-500 text-white px-6 py-2 rounded-xl hover:bg-rose-600 transition">
                            Update Password
                        </button>
                    </form>
                </div>
                @endif
                
                <!-- Booking History -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold mb-4">Riwayat Booking</h2>
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
                    <p class="text-gray-500 text-center py-4">Belum ada booking</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection