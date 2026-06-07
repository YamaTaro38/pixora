{{-- resources/views/booking-invoice-pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $booking->booking_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background: white;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            padding: 20px;
        }
        
        .invoice {
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* Header - Memisahkan dengan jelas */
        .header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #e11d48;
        }
        
        .header-left {
            float: left;
            width: 60%;
        }
        
        .header-right {
            float: right;
            width: 35%;
            text-align: right;
        }
        
        .clearfix {
            overflow: auto;
        }
        
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        .logo h1 {
            font-size: 32px;
            font-weight: bold;
            color: #e11d48;
            margin-bottom: 5px;
            letter-spacing: 1px;
        }
        
        .logo p {
            color: #666;
            font-size: 11px;
            margin: 2px 0;
        }
        
        .invoice-title h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .status {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-lunas {
            background: #10b981;
            color: white;
        }
        
        .status-dp {
            background: #f59e0b;
            color: white;
        }
        
        /* Info Section */
        .info-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .info-box {
            float: left;
            width: 33%;
        }
        
        .info-box h3 {
            font-size: 11px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        
        .info-box p {
            margin-bottom: 6px;
            font-size: 11px;
        }
        
        .info-box .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 85px;
        }
        
        /* Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        
        .items-table th {
            text-align: left;
            padding: 10px;
            background: #f5f5f5;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 2px solid #ddd;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        /* Summary - Diletakkan di kanan */
        .summary-wrapper {
            text-align: right;
            margin-bottom: 30px;
        }
        
        .summary {
            display: inline-block;
            width: 300px;
            text-align: left;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 11px;
        }
        
        .summary-row.total {
            border-top: 2px solid #333;
            margin-top: 8px;
            padding-top: 10px;
            font-weight: bold;
            font-size: 14px;
        }
        
        /* Payment Info */
        .payment-info {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #10b981;
        }
        
        .payment-info h4 {
            font-size: 12px;
            font-weight: bold;
            color: #065f46;
            margin-bottom: 10px;
        }
        
        .payment-info p {
            font-size: 11px;
            color: #065f46;
            margin-bottom: 5px;
        }
        
        /* Notes */
        .notes {
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
        
        .notes p {
            margin-bottom: 5px;
        }
        
        .notes strong {
            color: #333;
        }
        
        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
        }
        
        .signature {
            margin-top: 15px;
            padding-top: 15px;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #eee;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-4 {
            margin-top: 15px;
        }
        
        .mb-2 {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="invoice">
        <!-- Header -->
        <div class="header clearfix">
            <div class="header-left">
                <div class="logo">
                    <h1>PIXORA</h1>
                    <p>Studio Photography</p>
                    <p>Jakarta, Indonesia</p>
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">
                    <h2>INVOICE</h2>
                    <span class="status {{ $booking->payment_status == 'lunas' ? 'status-lunas' : 'status-dp' }}">
                        {{ $booking->payment_status == 'lunas' ? 'LUNAS' : 'DP LUNAS' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Info Section -->
        <div class="info-section clearfix">
            <div class="info-box">
                <h3>KEPADA</h3>
                <p><span class="label">Nama:</span> {{ $booking->customer_name }}</p>
                <p><span class="label">WhatsApp:</span> +62 {{ $booking->customer_phone }}</p>
                @if($booking->customer_email)
                <p><span class="label">Email:</span> {{ $booking->customer_email }}</p>
                @endif
            </div>
            <div class="info-box">
                <h3>DETAIL INVOICE</h3>
                <p><span class="label">No. Invoice:</span> {{ $booking->booking_code }}</p>
                <p><span class="label">Tanggal:</span> {{ date('d/m/Y', strtotime($booking->created_at)) }}</p>
                <p><span class="label">Status:</span> {{ $booking->payment_status == 'lunas' ? 'Lunas' : 'DP Lunas' }}</p>
            </div>
            <div class="info-box">
                <h3>DETAIL SESI</h3>
                <p><span class="label">Tanggal Sesi:</span> {{ date('d F Y', strtotime($booking->booking_date)) }}</p>
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
                        <small style="color: #666;">
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
                        <small style="color: #666;">Catatan: {{ $booking->special_requests }}</small>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Summary -->
        <div class="summary-wrapper">
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
        </div>
        
        <div class="clearfix"></div>
        
        <!-- Payment Info -->
        <div class="payment-info">
            <h4>Informasi Pembayaran</h4>
            <p><strong>Metode Pembayaran:</strong> {{ $booking->payment_method ?? 'Transfer Bank' }}</p>
            @if($booking->paid_at)
            <p><strong>Tanggal Bayar:</strong> {{ date('d F Y H:i', strtotime($booking->paid_at)) }} WIB</p>
            @endif
            @if($booking->payment_status == 'dp_paid')
            <p class="mt-4"><strong>Sisa pembayaran:</strong> Harap dilunasi sebelum sesi foto dimulai.</p>
            @endif
        </div>
        
        <!-- Notes -->
        <div class="notes">
            <p><strong>Catatan Penting:</strong></p>
            <p>1. Invoice ini adalah bukti pembayaran yang sah.</p>
            <p>2. Harap simpan invoice ini sebagai bukti booking.</p>
            <p>3. Reschedule dapat dilakukan maksimal H-3 sebelum sesi foto.</p>
            <p>4. DP tidak dapat dikembalikan jika pembatalan dilakukan kurang dari H-7.</p>
            <p>5. Harap datang 15 menit sebelum sesi dimulai.</p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>Pixora Studio Photography</p>
            <p>Telp: +62 812-3456-7890 | Email: hello@pixora.com | IG: @pixora.studio</p>
            <div class="signature">
                <p>Terima kasih telah mempercayakan momen berharga Anda kepada Pixora!</p>
                <p>© {{ date('Y') }} Pixora Studio. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>