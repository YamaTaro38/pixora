// lib/screens/booking/booking_detail_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../providers/booking_provider.dart';
import '../../utils/formatters.dart';

class BookingDetailScreen extends StatelessWidget {
  const BookingDetailScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<BookingProvider>(builder: (ctx, prov, _) {
      final b = prov.currentBooking;
      if (b == null) return Scaffold(appBar: AppBar(), body: const Center(child: Text('Booking tidak ditemukan')));

      return Scaffold(
        appBar: AppBar(title: Text('Detail Booking', style: GoogleFonts.poppins(fontWeight: FontWeight.bold))),
        body: SingleChildScrollView(padding: const EdgeInsets.all(20), child: Column(children: [
          // Status card
          Container(width: double.infinity, padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(gradient: b.isPaid ? const LinearGradient(colors: [Color(0xFF22C55E), Color(0xFF16A34A)]) : b.isPending ? const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFD97706)]) : const LinearGradient(colors: [Color(0xFFEF4444), Color(0xFFDC2626)]),
              borderRadius: BorderRadius.circular(16)),
            child: Column(children: [
              Icon(b.isPaid ? Icons.check_circle : b.isPending ? Icons.access_time : Icons.cancel, size: 48, color: Colors.white),
              const SizedBox(height: 8),
              Text(b.paymentStatusLabel, style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text(b.bookingCode, style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 14)),
            ])),
          const SizedBox(height: 20),

          // Details
          _infoCard([
            _row('Paket', b.packageName ?? '-'),
            _row('Tanggal', Formatters.date(b.bookingDate)),
            _row('Waktu', b.timeSlotLabel),
            if (b.specialRequests != null) _row('Catatan', b.specialRequests!),
          ]),
          const SizedBox(height: 12),

          _infoCard([
            _row('Nama', b.customerName),
            _row('Telepon', b.customerPhone),
            if (b.customerEmail != null) _row('Email', b.customerEmail!),
          ]),
          const SizedBox(height: 12),

          _infoCard([
            _row('Total Harga', b.formattedTotalPrice),
            if (b.formattedDownPayment != null) _row('Down Payment', b.formattedDownPayment!),
            if (b.paymentMethod != null) _row('Metode', b.paymentMethod!),
            _row('Status', b.paymentStatusLabel),
          ]),
          const SizedBox(height: 20),

          // Actions
          if (b.isPaid) SizedBox(width: double.infinity, child: ElevatedButton.icon(
            onPressed: () async {
              final url = Uri.parse(ApiConfig.invoiceUrl(b.publicToken));
              if (await canLaunchUrl(url)) await launchUrl(url, mode: LaunchMode.externalApplication);
            },
            icon: const Icon(Icons.receipt_long), label: const Text('Lihat Invoice'),
            style: ElevatedButton.styleFrom(backgroundColor: PixoraTheme.primaryRose, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))))),
        ])),
      );
    });
  }

  Widget _infoCard(List<Widget> children) {
    return Container(width: double.infinity, padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), boxShadow: PixoraTheme.softShadow),
      child: Column(children: children));
  }

  Widget _row(String label, String value) {
    return Padding(padding: const EdgeInsets.symmetric(vertical: 6),
      child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Text(label, style: const TextStyle(color: PixoraTheme.gray, fontSize: 14)),
        Flexible(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600, color: PixoraTheme.dark, fontSize: 14), textAlign: TextAlign.right)),
      ]));
  }
}
