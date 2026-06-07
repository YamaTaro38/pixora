// lib/screens/home/home_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/package_provider.dart';
import '../../widgets/gradient_button.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});
  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  @override
  void initState() { super.initState(); Provider.of<PackageProvider>(context, listen: false).fetchPackages(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SingleChildScrollView(child: Column(children: [
        _buildHero(context),
        _buildStats(),
        _buildPackagesSection(context),
        _buildGallery(),
        _buildTestimonials(),
        _buildCta(context),
        const SizedBox(height: 20),
      ])),
    );
  }

  Widget _buildHero(BuildContext context) {
    return Stack(children: [
      CachedNetworkImage(imageUrl: 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800&fit=crop', height: 480, width: double.infinity, fit: BoxFit.cover,
        placeholder: (c, u) => Container(height: 480, color: PixoraTheme.dark)),
      Container(height: 480, decoration: BoxDecoration(gradient: LinearGradient(colors: [Colors.black.withValues(alpha: 0.7), Colors.black.withValues(alpha: 0.3), Colors.transparent], begin: Alignment.bottomCenter, end: Alignment.topCenter))),
      Positioned(bottom: 40, left: 24, right: 24, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Text('Abadikan Momen\nTerbaik Anda', style: GoogleFonts.poppins(fontSize: 32, fontWeight: FontWeight.bold, color: Colors.white, height: 1.2))
            .animate().fadeIn(duration: 600.ms).slideY(begin: 0.2, end: 0),
        const SizedBox(height: 12),
        Text('Studio fotografi modern dengan sentuhan AI untuk hasil yang sempurna', style: GoogleFonts.inter(fontSize: 15, color: Colors.white70, height: 1.5))
            .animate(delay: 200.ms).fadeIn(duration: 500.ms),
        const SizedBox(height: 24),
        Row(children: [
          Expanded(child: GradientButton(text: 'Lihat Paket', icon: Icons.camera_alt, onPressed: () => Navigator.pushNamed(context, AppRoutes.packages), borderRadius: 50)),
          const SizedBox(width: 12),
          Expanded(child: OutlinedButton.icon(onPressed: () => Navigator.pushNamed(context, AppRoutes.calendar), icon: const Icon(Icons.calendar_today, size: 18, color: Colors.white),
            label: const Text('Booking', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
            style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16), side: const BorderSide(color: Colors.white38), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(50))))),
        ]).animate(delay: 400.ms).fadeIn().slideY(begin: 0.2, end: 0),
      ])),
    ]);
  }

  Widget _buildStats() {
    final stats = [
      {'icon': Icons.sentiment_satisfied_alt, 'value': '500+', 'label': 'Klien Puas'},
      {'icon': Icons.camera_alt, 'value': '1000+', 'label': 'Sesi Foto'},
      {'icon': Icons.photo_library, 'value': '50+', 'label': 'Portofolio'},
      {'icon': Icons.star, 'value': '4.9', 'label': 'Rating'},
    ];
    return Container(color: PixoraTheme.lightGray, padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
      child: Row(children: stats.map((s) => Expanded(child: Column(children: [
        Icon(s['icon'] as IconData, color: PixoraTheme.primaryRose, size: 28),
        const SizedBox(height: 4),
        Text(s['value'] as String, style: GoogleFonts.poppins(fontSize: 20, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
        Text(s['label'] as String, style: const TextStyle(fontSize: 11, color: PixoraTheme.gray)),
      ]))).toList()));
  }

  Widget _buildPackagesSection(BuildContext context) {
    return Padding(padding: const EdgeInsets.all(24), child: Column(children: [
      Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
        Text('Paket Populer', style: GoogleFonts.poppins(fontSize: 22, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
        TextButton(onPressed: () => Navigator.pushNamed(context, AppRoutes.packages), child: const Text('Lihat Semua', style: TextStyle(color: PixoraTheme.primaryRose, fontWeight: FontWeight.w600))),
      ]),
      const SizedBox(height: 16),
      Consumer<PackageProvider>(builder: (ctx, prov, _) {
        if (prov.isLoading) return const Center(child: CircularProgressIndicator(color: PixoraTheme.primaryRose));
        return SizedBox(height: 280, child: ListView.builder(scrollDirection: Axis.horizontal, itemCount: prov.packages.length, itemBuilder: (ctx, i) {
          final pkg = prov.packages[i];
          return Container(width: 240, margin: EdgeInsets.only(right: i < prov.packages.length - 1 ? 16 : 0),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: PixoraTheme.softShadow), clipBehavior: Clip.antiAlias,
            child: GestureDetector(onTap: () { prov.selectPackage(pkg); Navigator.pushNamed(context, AppRoutes.packageDetail); },
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                CachedNetworkImage(imageUrl: pkg.defaultImageUrl, height: 140, width: double.infinity, fit: BoxFit.cover),
                Padding(padding: const EdgeInsets.all(12), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(pkg.name, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: PixoraTheme.dark)),
                  const SizedBox(height: 4),
                  Row(children: [const Icon(Icons.access_time, size: 14, color: PixoraTheme.gray), const SizedBox(width: 4), Text('${pkg.durationHours} jam', style: const TextStyle(fontSize: 12, color: PixoraTheme.gray)),
                    const SizedBox(width: 12), const Icon(Icons.photo, size: 14, color: PixoraTheme.gray), const SizedBox(width: 4), Text('${pkg.editedPhotos} foto', style: const TextStyle(fontSize: 12, color: PixoraTheme.gray))]),
                  const SizedBox(height: 8),
                  Text(pkg.formattedPrice, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: PixoraTheme.primaryRose)),
                ])),
              ])));
        }));
      }),
    ]));
  }

  Widget _buildGallery() {
    final images = ['https://images.unsplash.com/photo-1519741497674-611481863552?w=400', 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=400',
      'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=400', 'https://images.unsplash.com/photo-1606216794074-735e91aa2c92?w=400',
      'https://images.unsplash.com/photo-1537633552985-df8429e8048b?w=400', 'https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=400'];
    return Padding(padding: const EdgeInsets.symmetric(horizontal: 24), child: Column(children: [
      Text('Galeri Karya Kami', style: GoogleFonts.poppins(fontSize: 22, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
      const SizedBox(height: 16),
      GridView.builder(shrinkWrap: true, physics: const NeverScrollableScrollPhysics(), gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, crossAxisSpacing: 8, mainAxisSpacing: 8),
        itemCount: images.length, itemBuilder: (ctx, i) => ClipRRect(borderRadius: BorderRadius.circular(12),
          child: CachedNetworkImage(imageUrl: images[i], fit: BoxFit.cover))),
      const SizedBox(height: 24),
    ]));
  }

  Widget _buildTestimonials() {
    final testimonials = [
      {'name': 'Andi & Siska', 'text': 'Hasil fotonya luar biasa! Tim Pixora sangat profesional dan kreatif.', 'rating': 5},
      {'name': 'Budi Santoso', 'text': 'Proses booking sangat mudah, tinggal pilih tanggal dan bayar. Recommended!', 'rating': 5},
      {'name': 'Citra Dewi', 'text': 'Fotonya aesthetic banget! Cocok untuk yang suka style modern dan clean.', 'rating': 5},
    ];
    return Container(color: PixoraTheme.lightGray, padding: const EdgeInsets.all(24), child: Column(children: [
      Text('Apa Kata Mereka', style: GoogleFonts.poppins(fontSize: 22, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
      const SizedBox(height: 16),
      SizedBox(height: 180, child: ListView.builder(scrollDirection: Axis.horizontal, itemCount: testimonials.length, itemBuilder: (ctx, i) {
        final t = testimonials[i];
        return Container(width: 280, margin: EdgeInsets.only(right: i < testimonials.length - 1 ? 12 : 0), padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: PixoraTheme.softShadow),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: List.generate(t['rating'] as int, (_) => const Icon(Icons.star, color: Colors.amber, size: 16))),
            const SizedBox(height: 8),
            Expanded(child: Text('"${t['text']}"', style: const TextStyle(fontSize: 13, color: PixoraTheme.darkGray, height: 1.4))),
            Row(children: [
              CircleAvatar(radius: 16, backgroundColor: PixoraTheme.primaryRose, child: Text((t['name'] as String)[0], style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14))),
              const SizedBox(width: 8),
              Text(t['name'] as String, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13, color: PixoraTheme.dark)),
            ]),
          ]));
      })),
    ]));
  }

  Widget _buildCta(BuildContext context) {
    return Container(margin: const EdgeInsets.all(24), padding: const EdgeInsets.all(24), decoration: BoxDecoration(gradient: PixoraTheme.primaryGradient, borderRadius: BorderRadius.circular(20), boxShadow: PixoraTheme.roseShadow),
      child: Column(children: [
        const Icon(Icons.camera_enhance, size: 40, color: Colors.white),
        const SizedBox(height: 12),
        Text('Siap Mengabadikan\nMomen Anda?', textAlign: TextAlign.center, style: GoogleFonts.poppins(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white, height: 1.2)),
        const SizedBox(height: 8),
        const Text('Booking sekarang dan dapatkan pengalaman fotografi terbaik', textAlign: TextAlign.center, style: TextStyle(color: Colors.white70, fontSize: 14)),
        const SizedBox(height: 20),
        ElevatedButton.icon(onPressed: () => Navigator.pushNamed(context, AppRoutes.calendar), icon: const Icon(Icons.calendar_today),
          label: const Text('Booking Sekarang'), style: ElevatedButton.styleFrom(backgroundColor: Colors.white, foregroundColor: PixoraTheme.primaryRose, padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(50)))),
      ]));
  }
}
