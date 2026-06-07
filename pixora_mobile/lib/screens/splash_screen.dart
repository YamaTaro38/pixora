// lib/screens/splash_screen.dart
// Splash screen dengan logo dan animasi

import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import '../config/theme.dart';
import '../config/routes.dart';
import '../providers/auth_provider.dart';
import 'package:provider/provider.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _initialize();
  }

  Future<void> _initialize() async {
    await Future.delayed(const Duration(seconds: 2));
    if (!mounted) return;

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.initialize();

    if (!mounted) return;

    final onboardingDone = await authProvider.isOnboardingDone();

    if (!mounted) return;

    if (!onboardingDone) {
      Navigator.pushReplacementNamed(context, AppRoutes.onboarding);
    } else {
      Navigator.pushReplacementNamed(context, AppRoutes.main);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFF1F1125), Color(0xFF2D1B35), Color(0xFF1A0D20)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
        ),
        child: Stack(
          children: [
            // Background decorative circles
            Positioned(
              top: -100,
              right: -100,
              child: Container(
                width: 300,
                height: 300,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      PixoraTheme.primaryRose.withValues(alpha: 0.15),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),
            Positioned(
              bottom: -80,
              left: -80,
              child: Container(
                width: 250,
                height: 250,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    colors: [
                      PixoraTheme.primaryPink.withValues(alpha: 0.12),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),

            // Main content
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Logo icon
                  Container(
                    width: 100,
                    height: 100,
                    decoration: BoxDecoration(
                      gradient: PixoraTheme.primaryGradient,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: PixoraTheme.primaryRose.withValues(alpha: 0.4),
                          blurRadius: 30,
                          offset: const Offset(0, 10),
                        ),
                      ],
                    ),
                    child: const Icon(
                      Icons.camera_alt_rounded,
                      size: 48,
                      color: Colors.white,
                    ),
                  )
                      .animate()
                      .scale(
                        begin: const Offset(0.5, 0.5),
                        end: const Offset(1.0, 1.0),
                        duration: 600.ms,
                        curve: Curves.elasticOut,
                      )
                      .fadeIn(duration: 400.ms),

                  const SizedBox(height: 24),

                  // App name
                  Text(
                    'PIXORA',
                    style: GoogleFonts.poppins(
                      fontSize: 36,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                      letterSpacing: 8,
                    ),
                  )
                      .animate(delay: 300.ms)
                      .fadeIn(duration: 500.ms)
                      .slideY(begin: 0.3, end: 0),

                  const SizedBox(height: 8),

                  Text(
                    'Studio Fotografi Modern',
                    style: GoogleFonts.inter(
                      fontSize: 14,
                      color: Colors.white.withValues(alpha: 0.6),
                      letterSpacing: 2,
                    ),
                  )
                      .animate(delay: 500.ms)
                      .fadeIn(duration: 500.ms),

                  const SizedBox(height: 48),

                  // Loading indicator
                  SizedBox(
                    width: 32,
                    height: 32,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(
                        PixoraTheme.primaryRose.withValues(alpha: 0.6),
                      ),
                    ),
                  ).animate(delay: 700.ms).fadeIn(duration: 400.ms),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
