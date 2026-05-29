{{-- resources/views/admin/dashboard.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('subtitle', 'Selamat datang kembali! Berikut ringkasan data studio Anda')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="glass-card rounded-2xl p-6 stats-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Booking Hari Ini</p>
                <p class="text-3xl font-bold text-gray-800">{{ $todayBookings ?? 0 }}</p>
                <p class="text-xs text-green-500 mt-2">
                    <i class="fas fa-arrow-up mr-1"></i> +12% dari kemarin
                </p>
            </div>
            <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center">
                <i class="fas fa-calendar-day text-rose-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-2xl p-6 stats-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Pendapatan Hari Ini</p>
                <p class="text-3xl font-bold text-gray-800">Rp {{ number_format($todayRevenue ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-green-500 mt-2">
                    <i class="fas fa-arrow-up mr-1"></i> +8% dari kemarin
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-2xl flex items-center justify-center">
                <i class="fas fa-money-bill-wave text-green-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-2xl p-6 stats-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Menunggu Pembayaran</p>
                <p class="text-3xl font-bold text-gray-800">{{ $pendingBookings ?? 0 }}</p>
                <p class="text-xs text-yellow-500 mt-2">
                    <i class="fas fa-clock mr-1"></i> Perlu tindakan
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-2xl flex items-center justify-center">
                <i class="fas fa-hourglass-half text-yellow-500 text-xl"></i>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-2xl p-6 stats-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-500 text-sm mb-1">Total Customer</p>
                <p class="text-3xl font-bold text-gray-800">{{ $totalCustomers ?? 0 }}</p>
                <p class="text-xs text-blue-500 mt-2">
                    <i class="fas fa-users mr-1"></i> Customer terdaftar
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center">
                <i class="fas fa-users text-blue-500 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Stats Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="glass-card rounded-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Statistik Bulan Ini</h3>
            <i class="fas fa-chart-line text-gray-400"></i>
        </div>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Total Booking</span>
                    <span class="font-semibold text-gray-800">{{ $monthBookings ?? 0 }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ min(($monthBookings ?? 0) / 100 * 100, 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Total Pendapatan</span>
                    <span class="font-semibold text-gray-800">Rp {{ number_format($monthRevenue ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ min(($monthRevenue ?? 0) / 100000000 * 100, 100) }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Booking Terkonfirmasi</span>
                    <span class="font-semibold text-gray-800">{{ $confirmedBookings ?? 0 }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min(($confirmedBookings ?? 0) / 100 * 100, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="glass-card rounded-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Status Booking</h3>
            <i class="fas fa-chart-pie text-gray-400"></i>
        </div>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Pending</span>
                </div>
                <span class="font-semibold">{{ $statusCounts['pending'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Lunas</span>
                </div>
                <span class="font-semibold">{{ $statusCounts['lunas'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">DP Lunas</span>
                </div>
                <span class="font-semibold">{{ $statusCounts['dp_paid'] ?? 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                    <span class="text-sm text-gray-600">Expired</span>
                </div>
                <span class="font-semibold">{{ $statusCounts['expired'] ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings Table -->
<div class="glass-card rounded-2xl p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Booking Terbaru</h3>
        <a href="{{ route('admin.bookings.index') }}" class="text-sm text-rose-500 hover:text-rose-600 transition">
            Lihat semua <i class="fas fa-arrow-right ml-1"></i>
        </a>
    </div>
    
    @if(isset($recentBookings) && $recentBookings->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full data-table">
            <thead>
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Paket</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="text-left py-3 px-4 text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentBookings as $booking)
                <tr class="border-t border-gray-100 hover:bg-white/30 transition">
                    <td class="py-3 px-4 text-sm font-mono">{{ $booking->booking_code }}</td>
                    <td class="py-3 px-4">
                        <div class="font-medium">{{ $booking->customer_name }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->customer_phone }}</div>
                    </td>
                    <td class="py-3 px-4 text-sm">{{ $booking->package->name }}</td>
                    <td class="py-3 px-4 text-sm">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-sm font-medium">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($booking->payment_status == 'lunas') bg-green-100 text-green-700
                            @elseif($booking->payment_status == 'pending') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-8 text-gray-400">
        <i class="fas fa-calendar-check text-4xl mb-2 block"></i>
        <p>Belum ada booking</p>
    </div>
    @endif
</div>
@endsection