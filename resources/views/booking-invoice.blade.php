{{-- resources/views/booking-invoice.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $booking->booking_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5;
            padding: 40px 20px;
        }
        
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        /* Header */
        .invoice-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 40px;
            color: white;
            position: relative;
        }
        
        .invoice-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.1)" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
            opacity: 0.3;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 20px;
            position: relative;
            z-index: 1;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        .logo p {
            opacity: 0.8;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .invoice-title {
            text-align: right;
        }
        
        .invoice-title h2 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .invoice-title .status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .status-lunas {
            background: #10b981;
            color: white;
        }
        
        .status-dp {
            background: #f59e0b;
            color: white;
        }
        
        /* Body */
        .invoice-body {
            padding: 40px;
        }
        
        /* Info Section */
        .info-section {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .info-box {
            flex: 1;
            min-width: 200px;
        }
        
        .info-box h3 {
            font-size: 14px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        
        .info-box p {
            font-size: 16px;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .info-box .label {
            font-weight: 600;
            color: #4b5563;
        }
        
        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            text-align: left;
            padding: 15px;
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        /* Summary */
        .summary {
            background: #f9fafb;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        
        .summary-row.total {
            border-top: 2px solid #e5e7eb;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 18px;
            font-weight: 700;
            color: #1e3c72;
        }
        
        /* Payment Info */
        .payment-info {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .payment-info h4 {
            color: #065f46;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .payment-info p {
            color: #065f46;
            font-size: 14px;
        }
        
        /* Footer */
        .invoice-footer {
            background: #f9fafb;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        
        .invoice-footer p {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .invoice-footer .signature {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .invoice-header {
                background: #1e3c72 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .status-lunas, .status-dp {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        /* Button */
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1e3c72;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            transition: background 0.3s;
        }
        
        .print-btn:hover {
            background: #2a5298;
        }
        
        .back-btn {
            background: #6b7280;
            margin-left: 10px;
        }
        
        .back-btn:hover {
            background: #4b5563;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Tombol Print dan Back (hanya tampil di layar, tidak saat print) -->
        <div class="no-print" style="padding: 20px 40px 0 40px;">
            <button onclick="window.print()" class="print-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 9V3h12v6M6 21H4a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-2"/>
                    <path d="M6 15h12v6H6z"/>
                    <path d="M18 9V6H6v3"/>
                </svg>
                Cetak Invoice
            </button>
            <button onclick="window.location.href='{{ route('booking.show', $booking->public_token) }}'" class="print-btn back-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Kembali
            </button>
        </div>
        
        <!-- Header Invoice -->
        <div class="invoice-header">
            <div class="header-content">
                <div class="logo">
                    <h1>PIXORA</h1>
                    <p>Studio Photography</p>
                </div>
                <div class="invoice-title">
                    <h2>INVOICE</h2>
                    <span class="status {{ $booking->payment_status == 'lunas' ? 'status-lunas' : 'status-dp' }}">
                        {{ $booking->payment_status == 'lunas' ? 'LUNAS' : 'DP LUNAS' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Body Invoice -->
        <div class="invoice-body">
            <!-- Info Section -->
            <div class="info-section">
                <div class="info-box">
                    <h3>INVOICE TO</h3>
                    <p><span class="label">Nama:</span> {{ $booking->customer_name }}</p>
                    <p><span class="label">WhatsApp:</span> +62 {{ $booking->customer_phone }}</p>
                    @if($booking->customer_email)
                    <p><span class="label">Email:</span> {{ $booking->customer_email }}</p>
                    @endif
                </div>
                <div class="info-box">
                    <h3>INVOICE DETAILS</h3>
                    <p><span class="label">No. Invoice:</span> {{ $booking->booking_code }}</p>
                    <p><span class="label">Tanggal Invoice:</span> {{ Carbon\Carbon::parse($booking->created_at)->translatedFormat('d F Y') }}</p>
                    <p><span class="label">Status:</span> {{ $booking->payment_status == 'lunas' ? 'Lunas' : 'DP Lunas' }}</p>
                </div>
                <div class="info-box">
                    <h3>SESSION DETAILS</h3>
                    <p><span class="label">Tanggal Sesi:</span> {{ Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</p>
                    <p><span class="label">Jam Sesi:</span> {{ $booking->timeSlotLabel }}</p>
                </div>
            </div>
            
            <!-- Items Table -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Deskripsi</th>
                        <th class="text-right">Harga</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $booking->package->name }}</strong><br>
                            <small style="color: #6b7280;">
                                {{ $booking->package->duration_hours }} jam sesi • 
                                {{ $booking->package->edited_photos }} foto edit • 
                                Lokasi: {{ ucfirst($booking->package->location_type) }}
                            </small>
                        </td>
                        <td class="text-right">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    </tr>
                    @if($booking->special_requests)
                    <tr>
                        <td colspan="2">
                            <small style="color: #6b7280;">Catatan: {{ $booking->special_requests }}</small>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            
            <!-- Summary -->
            <div class="summary">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                </div>
                @if($booking->down_payment > 0)
                <div class="summary-row">
                    <span>DP ({{ round(($booking->down_payment / $booking->total_price) * 100) }}%)</span>
                    <span>Rp {{ number_format($booking->down_payment, 0, ',', '.') }}</span>
                </div>
                <div class="summary-row total">
                    <span>SISA PEMBAYARAN</span>
                    <span>Rp {{ number_format($booking->total_price - $booking->down_payment, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="summary-row total">
                    <span>TOTAL {{ $booking->payment_status == 'lunas' ? 'DIBAYAR' : 'DP DIBAYAR' }}</span>
                    <span>Rp {{ number_format($booking->payment_status == 'lunas' ? $booking->total_price : $booking->down_payment, 0, ',', '.') }}</span>
                </div>
            </div>
            
            <!-- Payment Info -->
            <div class="payment-info">
                <h4>Informasi Pembayaran</h4>
                <p><strong>Metode Pembayaran:</strong> {{ $booking->payment_method ?? 'Belum dipilih' }}</p>
                @if($booking->paid_at)
                <p><strong>Tanggal Bayar:</strong> {{ Carbon\Carbon::parse($booking->paid_at)->translatedFormat('d F Y H:i') }} WIB</p>
                @endif
                @if($booking->payment_status == 'dp_paid')
                <p class="mt-4"><strong>Pembayaran Sisanya:</strong> Silakan bayar sisa pembayaran sebelum sesi foto dimulai.</p>
                @endif
            </div>
            
            <!-- Notes -->
            <div style="font-size: 12px; color: #6b7280; margin-top: 20px;">
                <p><strong>Catatan:</strong></p>
                <p>1. Invoice ini adalah bukti pembayaran yang sah.</p>
                <p>2. Harap simpan invoice ini sebagai bukti booking.</p>
                <p>3. Untuk reschedule, harap konfirmasi minimal H-3 sebelum sesi.</p>
                <p>4. DP tidak dapat dikembalikan jika pembatalan dilakukan kurang dari H-7.</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="invoice-footer">
            <p>Pixora Studio Photography</p>
            <p>Jakarta, Indonesia | Telp: +62 812-3456-7890 | Email: hello@pixora.com</p>
            <p>Instagram: @pixora.studio | www.pixora.com</p>
            <div class="signature">
                <p>Terima kasih telah mempercayakan momen berharga Anda kepada Pixora!</p>
                <p>© {{ date('Y') }} Pixora Studio. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>