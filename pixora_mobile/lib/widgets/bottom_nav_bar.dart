// lib/widgets/bottom_nav_bar.dart
// Bottom navigation bar kustom untuk Pixora

import 'package:flutter/material.dart';
import '../config/theme.dart';

class PixoraBottomNavBar extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const PixoraBottomNavBar({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _navItem(0, Icons.home_rounded, 'Beranda'),
              _navItem(1, Icons.camera_alt_rounded, 'Paket'),
              _centerButton(context),
              _navItem(3, Icons.calendar_month_rounded, 'Kalender'),
              _navItem(4, Icons.person_rounded, 'Profil'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _navItem(int index, IconData icon, String label) {
    final isActive = currentIndex == index;
    return GestureDetector(
      onTap: () => onTap(index),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isActive
              ? PixoraTheme.primaryRose.withValues(alpha: 0.1)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              color: isActive ? PixoraTheme.primaryRose : PixoraTheme.gray,
              size: 24,
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
                color: isActive ? PixoraTheme.primaryRose : PixoraTheme.gray,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _centerButton(BuildContext context) {
    return GestureDetector(
      onTap: () => onTap(2),
      child: Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          gradient: PixoraTheme.primaryGradient,
          shape: BoxShape.circle,
          boxShadow: PixoraTheme.roseShadow,
        ),
        child: const Icon(
          Icons.smart_toy_rounded,
          color: Colors.white,
          size: 28,
        ),
      ),
    );
  }
}
