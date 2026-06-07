{{-- resources/views/admin/reports/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Laporan')
@section('subtitle', 'Lihat laporan booking dan pendapatan')

@section('content')
<div class="space-y-6">
    <!-- Filter Form -->
    <div class="glass-card rounded-2xl p-6">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-gray-700 text-sm mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">Tanggal Akhir</label>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">&nbsp;</label>
                <button type="submit" class="w-full bg-rose-500 text-white px-4 py-2 rounded-lg hover:bg-rose-600">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
            </div>
            <div>
                <label class="block text-gray-700 text-sm mb-1">&nbsp;</label>
                <a href="{{ route('admin.reports.export', ['start_date' => $startDate->format('Y-m-d'), 'end_date' => $endDate->format('Y-m-d')]) }}" 
                   class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 text-center inline-block">
                    <i class="fas fa-download mr-2"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Total Booking</p>
                    <p class="text-3xl font-bold">{{ array_sum($statusStats) }}</p>
                </div>
                <i class="fas fa-calendar-check text-3xl text-rose-400"></i>
            </div>
        </div>
        
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pendapatan</p>
                    <p class="text-3xl font-bold">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-money-bill-wave text-3xl text-green-400"></i>
            </div>
        </div>
        
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Booking Lunas</p>
                    <p class="text-3xl font-bold">{{ $statusStats['lunas'] }}</p>
                </div>
                <i class="fas fa-check-circle text-3xl text-green-400"></i>
            </div>
        </div>
        
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm">Pending</p>
                    <p class="text-3xl font-bold">{{ $statusStats['pending'] }}</p>
                </div>
                <i class="fas fa-hourglass-half text-3xl text-yellow-400"></i>
            </div>
        </div>
    </div>
    
    <!-- Chart - Booking per Day -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">Grafik Booking Per Hari</h3>
        <canvas id="bookingChart" height="300"></canvas>
    </div>
    
    <!-- Top Packages -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">Paket Terlaris</h3>
        <div class="space-y-4">
            @forelse($topPackages as $item)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span>{{ $item->package->name }}</span>
                    <span>{{ $item->total }} booking</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ ($item->total / max(array_column($topPackages->toArray(), 'total')) * 100) }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-gray-500">Belum ada data</p>
            @endforelse
        </div>
    </div>
    
    <!-- Booking Details Table -->
    <div class="glass-card rounded-2xl p-6">
        <h3 class="text-lg font-semibold mb-4">Detail Booking</h3>
        <div class="overflow-x-auto">
            <table class="w-full data-table">
                <thead>
                    <tr>
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-500">Tanggal</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-500">Jumlah Booking</th>
                        <th class="text-left py-2 px-3 text-xs font-medium text-gray-500">Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookingsData as $data)
                    <tr class="border-t">
                        <td class="py-2 px-3">{{ \Carbon\Carbon::parse($data->date)->format('d/m/Y') }}</td>
                        <td class="py-2 px-3">{{ $data->total }}</td>
                        <td class="py-2 px-3">Rp {{ number_format($data->revenue, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grafik Booking
const ctx = document.getElementById('bookingChart').getContext('2d');
const bookingData = @json($bookingsData);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: bookingData.map(item => item.date),
        datasets: [{
            label: 'Jumlah Booking',
            data: bookingData.map(item => item.total),
            borderColor: '#e11d48',
            backgroundColor: 'rgba(225, 29, 72, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});
</script>
@endpush
@endsection