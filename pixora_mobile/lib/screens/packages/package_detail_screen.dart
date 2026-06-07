// lib/screens/packages/package_detail_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/package_provider.dart';
import '../../widgets/gradient_button.dart';

class PackageDetailScreen extends StatelessWidget {
  const PackageDetailScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<PackageProvider>(builder: (ctx, prov, _) {
      final pkg = prov.selectedPackage;
      if (pkg == null) return const Scaffold(body: Center(child: Text('Paket tidak ditemukan')));

      return Scaffold(body: CustomScrollView(slivers: [
        SliverAppBar(expandedHeight: 300, pinned: true, backgroundColor: PixoraTheme.dark,
          flexibleSpace: FlexibleSpaceBar(background: Stack(children: [
            CachedNetworkImage(imageUrl: pkg.defaultImageUrl, width: double.infinity, height: 360, fit: BoxFit.cover),
            Container(decoration: BoxDecoration(gradient: LinearGradient(colors: [Colors.black.withValues(alpha: 0.6), Colors.transparent], begin: Alignment.bottomCenter, end: Alignment.center))),
          ]))),
        SliverToBoxAdapter(child: Padding(padding: const EdgeInsets.all(20), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(pkg.name, style: GoogleFonts.poppins(fontSize: 24, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
              const SizedBox(height: 4),
              Row(children: [
                ...List.generate(5, (_) => const Icon(Icons.star, color: Colors.amber, size: 16)),
                const SizedBox(width: 4), const Text('(120+ ulasan)', style: TextStyle(fontSize: 12, color: PixoraTheme.gray)),
              ]),
            ])),
            Container(padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8), decoration: BoxDecoration(color: PixoraTheme.lightRose, borderRadius: BorderRadius.circular(12)),
              child: Column(children: [
                const Text('Mulai dari', style: TextStyle(fontSize: 11, color: PixoraTheme.primaryRose, fontWeight: FontWeight.w500)),
                Text(pkg.formattedPrice, style: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.bold, color: PixoraTheme.primaryRose)),
              ])),
          ]),
          const SizedBox(height: 16),
          Text(pkg.description, style: const TextStyle(fontSize: 14, color: PixoraTheme.darkGray, height: 1.6)),
          const SizedBox(height: 20),

          // Specs grid
          Row(children: [
            _specCard(Icons.access_time, 'Durasi', '${pkg.durationHours} Jam'),
            const SizedBox(width: 8),
            _specCard(Icons.photo_library, 'Foto Edit', '${pkg.editedPhotos} Foto'),
            const SizedBox(width: 8),
            _specCard(Icons.location_on, 'Lokasi', pkg.locationLabel),
          ]),
          const SizedBox(height: 24),

          // Inclusions
          Text('Yang Termasuk dalam Paket', style: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
          const SizedBox(height: 12),
          ...pkg.inclusions.map((item) => Padding(padding: const EdgeInsets.only(bottom: 8),
            child: Row(children: [const Icon(Icons.check_circle, color: PixoraTheme.success, size: 20), const SizedBox(width: 8), Expanded(child: Text(item, style: const TextStyle(fontSize: 14, color: PixoraTheme.darkGray)))]))),
          if (pkg.inclusions.isEmpty) ...['1 Fotografer professional', '1 Asisten fotografer', 'Soft file via Google Drive', '${pkg.editedPhotos} foto hasil edit'].map((item) =>
            Padding(padding: const EdgeInsets.only(bottom: 8), child: Row(children: [const Icon(Icons.check_circle, color: PixoraTheme.success, size: 20), const SizedBox(width: 8), Text(item, style: const TextStyle(fontSize: 14, color: PixoraTheme.darkGray))]))),
          const SizedBox(height: 24),

          // Price & booking
          Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(color: PixoraTheme.lightGray, borderRadius: BorderRadius.circular(16)),
            child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                const Text('Total Harga', style: TextStyle(fontSize: 13, color: PixoraTheme.gray)),
                Text(pkg.formattedPrice, style: GoogleFonts.poppins(fontSize: 24, fontWeight: FontWeight.bold, color: PixoraTheme.primaryRose)),
                if (pkg.formattedDownPayment != null) Text('DP: ${pkg.formattedDownPayment}', style: const TextStyle(fontSize: 12, color: PixoraTheme.gray)),
              ]),
              SizedBox(width: 160, child: GradientButton(text: 'Booking', icon: Icons.calendar_today, onPressed: () => Navigator.pushNamed(context, AppRoutes.calendar), borderRadius: 50)),
            ])),
          const SizedBox(height: 24),

          // FAQ
          Text('Pertanyaan Umum', style: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
          const SizedBox(height: 12),
          _faqItem('Apakah bisa request konsep foto tertentu?', 'Tentu! Anda bisa menyampaikan konsep yang diinginkan saat booking atau diskusi dengan tim kreatif kami.'),
          _faqItem('Berapa lama proses edit foto?', 'Foto akan selesai diedit dalam waktu 14-21 hari kerja setelah sesi foto selesai.'),
          _faqItem('Apakah DP bisa refund jika batal?', 'DP tidak dapat dikembalikan jika pembatalan dilakukan kurang dari H-7 sebelum jadwal sesi.'),
          const SizedBox(height: 20),
        ]))),
      ]));
    });
  }

  Widget _specCard(IconData icon, String label, String value) {
    return Expanded(child: Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: PixoraTheme.lightGray, borderRadius: BorderRadius.circular(12)),
      child: Column(children: [Icon(icon, color: PixoraTheme.primaryRose, size: 22), const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 11, color: PixoraTheme.gray)), Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: PixoraTheme.dark))])));
  }

  Widget _faqItem(String q, String a) {
    return Container(margin: const EdgeInsets.only(bottom: 8), padding: const EdgeInsets.all(14), decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8)]),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text(q, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14, color: PixoraTheme.dark)),
        const SizedBox(height: 4), Text(a, style: const TextStyle(fontSize: 13, color: PixoraTheme.gray, height: 1.4)),
      ]));
  }
}
