{{-- resources/views/calendar.blade.php --}}
@extends('layouts.app')

@section('title', 'Cek Jadwal & Booking - Pixora')

@section('content')

<div class="container mx-auto px-4 py-8">
    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
            <p class="text-red-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
            <div>
                @foreach($errors->all() as $error)
                <p class="text-red-700">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="max-w-7xl mx-auto">
        
        <!-- Calendar Section -->
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8">
                <button id="prevBtn" class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-xl transition flex items-center gap-2">
                    <i class="fas fa-chevron-left"></i> Bulan Lalu
                </button>
                <h2 id="monthTitle" class="text-2xl md:text-3xl font-bold text-gray-800">Memuat...</h2>
                <button id="nextBtn" class="bg-gray-200 hover:bg-gray-300 px-5 py-2 rounded-xl transition flex items-center gap-2">
                    Bulan Depan <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Day Names -->
            <div class="grid grid-cols-7 gap-2 text-center mb-4">
                <div class="font-bold text-red-500 py-2 bg-red-50 rounded-lg">Min</div>
                <div class="font-bold text-gray-700 py-2 bg-gray-50 rounded-lg">Sen</div>
                <div class="font-bold text-gray-700 py-2 bg-gray-50 rounded-lg">Sel</div>
                <div class="font-bold text-gray-700 py-2 bg-gray-50 rounded-lg">Rab</div>
                <div class="font-bold text-gray-700 py-2 bg-gray-50 rounded-lg">Kam</div>
                <div class="font-bold text-gray-700 py-2 bg-gray-50 rounded-lg">Jum</div>
                <div class="font-bold text-gray-700 py-2 bg-gray-50 rounded-lg">Sab</div>
            </div>
            
            <!-- Calendar Grid -->
            <div id="calendarGrid" class="grid grid-cols-7 gap-2 min-h-[500px]">
                <div class="col-span-7 text-center py-10">
                    <div class="inline-block w-8 h-8 border-4 border-rose-500 border-t-transparent rounded-full animate-spin"></div>
                    <p class="mt-2 text-gray-500">Memuat kalender...</p>
                </div>
            </div>
            
            <!-- Legend -->
            <div class="mt-6 pt-4 border-t flex flex-wrap justify-center gap-6 text-sm">
                <div class="flex items-center gap-2"><div class="w-4 h-4 bg-green-500 rounded"></div><span>Tersedia</span></div>
                <div class="flex items-center gap-2"><div class="w-4 h-4 bg-red-500 rounded"></div><span>Penuh/Dibooking</span></div>
                <div class="flex items-center gap-2"><div class="w-4 h-4 bg-gray-300 rounded"></div><span>Lewat</span></div>
                <div class="flex items-center gap-2"><div class="w-4 h-4 bg-rose-500 rounded-full"></div><span>Hari Ini</span></div>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                <i class="fas fa-calendar-check text-rose-500"></i>
                Form Booking
            </h3>
            
            <form id="bookingForm" action="{{ route('booking.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Hidden inputs -->
                <input type="hidden" name="package_id" id="selectedPackageId" required>
                <input type="hidden" name="booking_date" id="selectedDateHidden" required>
                <input type="hidden" name="time_slot" id="selectedSlotHidden" required>
                
                <!-- Pilih Paket -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-box text-rose-500 mr-2"></i>
                        Pilih Paket Fotografi
                    </label>
                    <div id="packagesContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($packages as $package)
                        <div class="package-card border-2 rounded-xl p-4 cursor-pointer transition-all hover:shadow-lg"
                             data-package-id="{{ $package->id }}"
                             data-package-name="{{ $package->name }}"
                             data-package-price="{{ $package->price }}">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-lg text-gray-800">{{ $package->name }}</h4>
                                <span class="bg-rose-100 text-rose-600 text-xs px-2 py-1 rounded-full">Popular</span>
                            </div>
                            <p class="text-gray-500 text-sm mb-3">{{ Str::limit($package->description, 80) }}</p>
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-2xl font-bold text-rose-600">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-400">/sesi</span>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <i class="fas fa-clock mr-1"></i> {{ $package->duration_hours }} jam
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Tanggal yang Dipilih -->
                <div class="bg-gray-50 rounded-xl p-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        <i class="fas fa-calendar-day text-rose-500 mr-2"></i>
                        Tanggal Booking
                    </label>
                    <div id="selectedDateDisplay" class="text-lg font-medium text-gray-800">
                        Belum ada tanggal dipilih
                    </div>
                </div>
                
                <!-- Pilih Jam Sesi -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">
                        <i class="fas fa-clock text-rose-500 mr-2"></i>
                        Pilih Jam Sesi
                    </label>
                    <div id="timeSlotsContainer" class="min-h-[200px]">
                        <div class="text-center text-gray-400 py-8">
                            <i class="fas fa-calendar-day text-4xl mb-2 block"></i>
                            <p>Silakan pilih tanggal terlebih dahulu</p>
                        </div>
                    </div>
                </div>
                
                <!-- Data Diri -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-rose-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">WhatsApp <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <span class="bg-gray-100 border border-r-0 border-gray-300 rounded-l-xl px-4 py-3 text-gray-500">+62</span>
                            <input type="tel" name="customer_phone" placeholder="81234567890" class="flex-1 border border-gray-300 rounded-r-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-rose-500" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Email (Opsional)</label>
                        <input type="email" name="customer_email" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Catatan Khusus</label>
                        <input type="text" name="special_requests" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-rose-500" placeholder="Ada request khusus?">
                    </div>
                </div>
                
                <!-- Metode Pembayaran -->
                <div>
                    <label class="block text-gray-700 font-semibold mb-3">Metode Pembayaran</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer hover:bg-rose-50 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50">
                            <input type="radio" name="payment_type" value="full" checked class="text-rose-500 w-4 h-4">
                            <div>
                                <div class="font-semibold">Bayar Lunas</div>
                                <div class="text-xs text-gray-500">Bayar penuh sekarang</div>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer hover:bg-rose-50 transition has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50">
                            <input type="radio" name="payment_type" value="down_payment" class="text-rose-500 w-4 h-4">
                            <div>
                                <div class="font-semibold">DP</div>
                                <div class="text-xs text-gray-500">Bayar DP 50%</div>
                            </div>
                        </label>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" id="submitBtn" class="w-full bg-gradient-to-r from-rose-500 to-pink-600 text-white py-4 rounded-xl font-semibold text-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                    <i class="fas fa-arrow-right"></i>
                    Lanjut ke Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>

<style>
    .package-card {
        transition: all 0.3s ease;
    }
    .package-card.selected {
        border-color: #e11d48;
        background-color: #fff0f2;
        box-shadow: 0 10px 25px -5px rgba(225, 29, 72, 0.2);
    }
    .time-slot-card {
        transition: all 0.3s ease;
    }
    .time-slot-card.selected {
        border-color: #e11d48;
        background-color: #fff0f2;
        transform: scale(1.02);
    }
</style>

@push('scripts')
<script>
// ========== STATE ==========
let currentYear = {{ $currentYear }};
let currentMonth = {{ $currentMonth }};
let calendarData = {};
let selectedDate = null;
let selectedPackage = null;
let selectedSlot = null;

// ========== DOM Elements ==========
const packagesContainer = document.getElementById('packagesContainer');
const timeSlotsContainer = document.getElementById('timeSlotsContainer');
const selectedDateDisplay = document.getElementById('selectedDateDisplay');
const selectedDateHidden = document.getElementById('selectedDateHidden');
const selectedSlotHidden = document.getElementById('selectedSlotHidden');
const selectedPackageId = document.getElementById('selectedPackageId');
const submitBtn = document.getElementById('submitBtn');

// ========== Helper Functions ==========
function formatDateIndonesia(dateStr) {
    const [year, month, day] = dateStr.split('-');
    const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return `${day} ${monthNames[parseInt(month) - 1]} ${year}`;
}

function getMonthName(month) {
    const names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    return names[month - 1];
}

// ========== Package Selection ==========
function initPackageCards() {
    document.querySelectorAll('.package-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.package-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            
            selectedPackage = {
                id: card.dataset.packageId,
                name: card.dataset.packageName,
                price: card.dataset.packagePrice
            };
            selectedPackageId.value = selectedPackage.id;
            
            checkFormReady();
        });
    });
}

// ========== Render Calendar ==========
function renderCalendar() {
    const today = new Date();
    const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    const currentHour = today.getHours();
    const currentMinute = today.getMinutes();
    
    const firstDayOfMonth = new Date(currentYear, currentMonth - 1, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth, 0).getDate();
    
    let html = '';
    
    // Empty cells
    for (let i = 0; i < firstDayOfMonth; i++) {
        html += '<div class="p-3 bg-gray-50 rounded-xl"></div>';
    }
    
    // Date cells
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = currentYear + '-' + String(currentMonth).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const dayData = calendarData[dateStr];
        const isPast = new Date(dateStr) < new Date(todayStr);
        const isToday = dateStr === todayStr;
        
        let statusText = '';
        let bgColor = '';
        let clickable = true;
        let totalAvailable = 0;
        
        if (isPast) {
            statusText = 'Lewat';
            bgColor = 'bg-gray-200 text-gray-400';
            clickable = false;
        } else if (dayData && dayData.slots) {
            // Hitung slot yang benar-benar available untuk hari ini
            for (const slotKey in dayData.slots) {
                const slot = dayData.slots[slotKey];
                
                let isSlotAvailable = slot.available;
                
                // Untuk hari ini, cek ulang apakah slot sudah lewat
                if (isToday) {
                    if (currentHour > slot.endHour) {
                        isSlotAvailable = false;
                    } else if (currentHour === slot.endHour && currentMinute > 0) {
                        isSlotAvailable = false;
                    }
                }
                
                if (isSlotAvailable && !slot.is_booked) {
                    totalAvailable++;
                }
            }
            
            if (totalAvailable > 0) {
                statusText = totalAvailable + ' slot tersedia';
                bgColor = 'bg-green-100 hover:bg-green-200';
                clickable = true;
            } else {
                statusText = 'Penuh';
                bgColor = 'bg-red-100 text-red-500';
                clickable = false;
            }
        } else if (!isPast && !isToday) {
            // Tanggal future tanpa data
            statusText = '3 slot tersedia';
            bgColor = 'bg-green-100 hover:bg-green-200';
            clickable = true;
        } else if (isToday && !dayData) {
            // Hari ini tanpa data - hitung berdasarkan jam sekarang
            if (currentHour < 11) {
                totalAvailable = 3;
                statusText = '3 slot tersedia';
            } else if (currentHour < 16) {
                totalAvailable = 2;
                statusText = '2 slot tersedia';
            } else if (currentHour < 20) {
                totalAvailable = 1;
                statusText = '1 slot tersedia';
            } else {
                totalAvailable = 0;
                statusText = 'Penuh';
                clickable = false;
            }
            bgColor = totalAvailable > 0 ? 'bg-green-100 hover:bg-green-200' : 'bg-red-100 text-red-500';
        }
        
        const todayClass = isToday ? 'ring-2 ring-rose-500 ring-offset-2' : '';
        const cursorClass = clickable ? 'cursor-pointer' : 'cursor-not-allowed';
        
        html += `
            <div class="date-cell p-3 rounded-xl transition-all ${bgColor} ${todayClass} ${cursorClass} text-center"
                 data-date="${dateStr}" 
                 data-clickable="${clickable}">
                <div class="font-semibold text-lg">${day}</div>
                <div class="text-xs mt-1">${statusText}</div>
                ${isToday ? '<div class="text-[10px] text-rose-500 mt-1">Hari Ini</div>' : ''}
            </div>
        `;
    }
    
    document.getElementById('calendarGrid').innerHTML = html;
    
    // Event klik tanggal
    document.querySelectorAll('.date-cell[data-clickable="true"]').forEach(cell => {
        cell.addEventListener('click', function() {
            document.querySelectorAll('.date-cell').forEach(c => {
                c.classList.remove('ring-2', 'ring-rose-500', 'bg-rose-100');
            });
            this.classList.add('ring-2', 'ring-rose-500', 'bg-rose-100');
            
            selectedDate = this.dataset.date;
            selectedDateDisplay.innerHTML = `<i class="fas fa-calendar-check text-green-600 mr-2"></i> ${formatDateIndonesia(selectedDate)}`;
            selectedDateHidden.value = selectedDate;
            
            renderTimeSlots(selectedDate);
            checkFormReady();
        });
    });
}

// ========== Render Time Slots ==========
function renderTimeSlots(date) {
    const dayData = calendarData[date];
    const today = new Date();
    const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
    const currentHour = today.getHours();
    const currentMinute = today.getMinutes();
    const isToday = date === todayStr;
    
    const slotConfig = {
        morning: { label: 'Pagi', time: '08:00 - 11:00', icon: 'fa-sun', endHour: 11 },
        afternoon: { label: 'Siang', time: '13:00 - 16:00', icon: 'fa-cloud-sun', endHour: 16 },
        evening: { label: 'Sore', time: '17:00 - 20:00', icon: 'fa-moon', endHour: 20 }
    };
    
    let html = '<div class="space-y-3"><div class="grid grid-cols-1 gap-3">';
    
    if (!dayData || !dayData.slots) {
        // Default slots (tidak ada data di database)
        for (const [slotKey, config] of Object.entries(slotConfig)) {
            let isPastSlot = false;
            let isAvailable = true;
            
            if (isToday) {
                if (currentHour > config.endHour || (currentHour === config.endHour && currentMinute > 0)) {
                    isPastSlot = true;
                    isAvailable = false;
                }
            }
            
            html += `
                <div class="time-slot-card rounded-xl border-2 p-4 transition-all duration-200
                            ${isAvailable ? 'bg-green-50 border-green-200 hover:bg-green-100 cursor-pointer' : 'bg-gray-100 border-gray-200 cursor-not-allowed opacity-60'}"
                     data-slot="${slotKey}" 
                     data-available="${isAvailable}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full ${isAvailable ? 'bg-green-100' : 'bg-gray-200'} flex items-center justify-center">
                                <i class="fas ${config.icon} ${isAvailable ? 'text-amber-500' : 'text-gray-400'} text-lg"></i>
                            </div>
                            <div>
                                <div class="font-bold ${isAvailable ? 'text-gray-800' : 'text-gray-500'}">${config.label}</div>
                                <div class="text-sm ${isAvailable ? 'text-gray-600' : 'text-gray-400'}">${config.time}</div>
                            </div>
                        </div>
                        <div>
                            ${isAvailable ? 
                                '<span class="text-xs text-green-600"><i class="fas fa-check-circle"></i> Tersedia</span>' : 
                                (isPastSlot ? '<span class="text-xs text-red-500"><i class="fas fa-clock"></i> Lewat</span>' : '<span class="text-xs text-gray-400"><i class="fas fa-times-circle"></i> Tidak Tersedia</span>')}
                        </div>
                    </div>
                </div>
            `;
        }
    } else {
        // Data dari database
        for (const [slotKey, slot] of Object.entries(dayData.slots)) {
            const config = slotConfig[slotKey];
            if (!config) continue;
            
            let isPastSlot = false;
            if (isToday) {
                if (currentHour > config.endHour || (currentHour === config.endHour && currentMinute > 0)) {
                    isPastSlot = true;
                }
            }
            
            const isAvailable = slot.available && !isPastSlot;
            
            let statusText = '';
            let bgClass = '';
            let iconColor = '';
            
            if (isPastSlot) {
                statusText = 'Lewat';
                bgClass = 'bg-gray-100 border-gray-200 cursor-not-allowed opacity-60';
                iconColor = 'text-gray-400';
            } else if (slot.is_booked) {
                statusText = 'Sudah Dibooking';
                bgClass = 'bg-red-50 border-red-200 cursor-not-allowed opacity-60';
                iconColor = 'text-red-400';
            } else if (isAvailable) {
                statusText = 'Tersedia';
                bgClass = 'bg-green-50 border-green-200 hover:bg-green-100 cursor-pointer';
                iconColor = 'text-amber-500';
            } else {
                statusText = 'Tidak Tersedia';
                bgClass = 'bg-gray-100 border-gray-200 cursor-not-allowed opacity-60';
                iconColor = 'text-gray-400';
            }
            
            html += `
                <div class="time-slot-card rounded-xl border-2 p-4 transition-all duration-200 ${bgClass}"
                     data-slot="${slotKey}" 
                     data-available="${isAvailable}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full ${isAvailable ? 'bg-green-100' : 'bg-gray-200'} flex items-center justify-center">
                                <i class="fas ${config.icon} ${iconColor} text-lg"></i>
                            </div>
                            <div>
                                <div class="font-bold ${isAvailable ? 'text-gray-800' : 'text-gray-500'}">${config.label}</div>
                                <div class="text-sm ${isAvailable ? 'text-gray-600' : 'text-gray-400'}">${slot.start_time} - ${slot.end_time}</div>
                            </div>
                        </div>
                        <div>
                            <span class="text-xs ${isAvailable ? 'text-green-600' : 'text-gray-500'}">
                                ${isAvailable ? '<i class="fas fa-check-circle"></i> ' + statusText : '<i class="fas fa-times-circle"></i> ' + statusText}
                            </span>
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    html += '</div></div>';
    timeSlotsContainer.innerHTML = html;
    
    // Event untuk slot yang available
    document.querySelectorAll('.time-slot-card[data-available="true"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.time-slot-card').forEach(b => {
                b.classList.remove('selected', 'ring-2', 'ring-rose-500', 'bg-rose-100');
            });
            this.classList.add('selected', 'ring-2', 'ring-rose-500', 'bg-rose-100');
            
            selectedSlot = this.dataset.slot;
            selectedSlotHidden.value = selectedSlot;
            
            const slotLabel = this.querySelector('.font-bold').innerText;
            selectedDateDisplay.innerHTML = `
                <i class="fas fa-calendar-check text-green-600 mr-2"></i>
                ${formatDateIndonesia(selectedDate)}
                <span class="text-gray-400 mx-2">•</span>
                <span class="text-rose-600 font-medium">${slotLabel}</span>
            `;
            
            checkFormReady();
        });
    });
    
    // Jika tidak ada slot available
    const availableSlots = document.querySelectorAll('.time-slot-card[data-available="true"]');
    if (availableSlots.length === 0 && dayData && dayData.slots) {
        timeSlotsContainer.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-clock text-4xl mb-2 block"></i>
                <p>Tidak ada jam sesi yang tersedia untuk tanggal ini.</p>
                <p class="text-sm mt-1">Silakan pilih tanggal lain.</p>
            </div>
        `;
    }
}

// ========== Check Form Ready ==========
function checkFormReady() {
    if (selectedPackage && selectedDate && selectedSlot) {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
}

// ========== Load Month ==========
async function loadMonth(year, month) {
    try {
        const response = await fetch(`{{ url('/kalender/data') }}?year=${year}&month=${month}`);
        const result = await response.json();
        
        if (result.success) {
            calendarData = result.calendarData;
            currentYear = result.year;
            currentMonth = result.month;
            
            document.getElementById('monthTitle').innerHTML = `${getMonthName(currentMonth)} ${currentYear}`;
            renderCalendar();
            
            // Reset selections
            selectedDate = null;
            selectedSlot = null;
            selectedDateHidden.value = '';
            selectedSlotHidden.value = '';
            selectedDateDisplay.innerHTML = 'Belum ada tanggal dipilih';
            timeSlotsContainer.innerHTML = `
                <div class="text-center text-gray-400 py-8">
                    <i class="fas fa-calendar-day text-4xl mb-2 block"></i>
                    <p>Silakan pilih tanggal terlebih dahulu</p>
                </div>
            `;
            checkFormReady();
        }
    } catch (error) {
        console.error('Error loading month:', error);
    }
}

// ========== Navigation ==========
document.getElementById('prevBtn').addEventListener('click', () => {
    let newMonth = currentMonth - 1;
    let newYear = currentYear;
    if (newMonth < 1) {
        newMonth = 12;
        newYear--;
    }
    loadMonth(newYear, newMonth);
});

document.getElementById('nextBtn').addEventListener('click', () => {
    let newMonth = currentMonth + 1;
    let newYear = currentYear;
    if (newMonth > 12) {
        newMonth = 1;
        newYear++;
    }
    loadMonth(newYear, newMonth);
});

// ========== Initialize ==========
initPackageCards();
loadMonth(currentYear, currentMonth);
</script>
@endpush
@endsection
