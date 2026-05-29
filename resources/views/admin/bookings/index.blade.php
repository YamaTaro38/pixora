{{-- resources/views/admin/bookings/index.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Manajemen Booking')

@section('content')
<div class="bg-white rounded-xl shadow-sm">
    <div class="p-6 border-b">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h3 class="text-lg font-semibold">Daftar Booking</h3>
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="text" name="search" placeholder="Cari nama/kode..." value="{{ request('search') }}" class="border rounded-lg px-3 py-2 text-sm w-64">
                <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                    <option value="all">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="dp_paid" {{ request('status') == 'dp_paid' ? 'selected' : '' }}>DP Lunas</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
                <button type="submit" class="bg-rose-500 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
            </form>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Sesi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($bookings as $booking)
                <tr>
                    <td class="px-6 py-4 text-sm font-mono">{{ $booking->booking_code }}</td>
                    <td class="px-6 py-4">
                        <div class="font-medium">{{ $booking->customer_name }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->customer_phone }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $booking->package->name }}</td>
                    <td class="px-6 py-4 text-sm">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 text-sm">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($booking->payment_status == 'lunas') bg-green-100 text-green-700
                            @elseif($booking->payment_status == 'dp_paid') bg-blue-100 text-blue-700
                            @elseif($booking->payment_status == 'pending') bg-yellow-100 text-yellow-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ ucfirst($booking->payment_status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="text-rose-500 hover:text-rose-700">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                        Tidak ada data booking
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6 border-t">
        {{ $bookings->links() }}
    </div>
</div>
@endsection