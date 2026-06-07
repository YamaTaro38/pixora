// lib/screens/packages/packages_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/package_provider.dart';
import '../../widgets/package_card.dart';

class PackagesScreen extends StatefulWidget {
  const PackagesScreen({super.key});
  @override
  State<PackagesScreen> createState() => _PackagesScreenState();
}

class _PackagesScreenState extends State<PackagesScreen> {
  @override
  void initState() { super.initState(); final prov = Provider.of<PackageProvider>(context, listen: false); if (prov.packages.isEmpty) prov.fetchPackages(); }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: CustomScrollView(slivers: [
        SliverAppBar(expandedHeight: 180, pinned: true, backgroundColor: PixoraTheme.primaryRose,
          flexibleSpace: FlexibleSpaceBar(title: Text('Paket Fotografi', style: GoogleFonts.poppins(fontWeight: FontWeight.bold, fontSize: 20)),
            background: Container(decoration: const BoxDecoration(gradient: PixoraTheme.primaryGradient),
              child: Center(child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                const SizedBox(height: 40),
                const Icon(Icons.camera_alt, size: 48, color: Colors.white70),
                const SizedBox(height: 8),
                Text('Pilih Paket Terbaik untuk Anda', style: GoogleFonts.inter(fontSize: 14, color: Colors.white70)),
              ]))))),
        Consumer<PackageProvider>(builder: (ctx, prov, _) {
          if (prov.isLoading) return const SliverFillRemaining(child: Center(child: CircularProgressIndicator(color: PixoraTheme.primaryRose)));
          return SliverPadding(padding: const EdgeInsets.all(16),
            sliver: SliverList(delegate: SliverChildBuilderDelegate((ctx, i) => Padding(padding: const EdgeInsets.only(bottom: 16),
              child: PackageCard(package: prov.packages[i], onTap: () { prov.selectPackage(prov.packages[i]); Navigator.pushNamed(context, AppRoutes.packageDetail); })), childCount: prov.packages.length)));
        }),
      ]),
    );
  }
}
