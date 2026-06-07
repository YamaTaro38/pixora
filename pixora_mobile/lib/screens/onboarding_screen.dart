// lib/screens/onboarding_screen.dart
// Onboarding screen dengan 3 slides

import 'package:flutter/material.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import '../config/theme.dart';
import '../config/routes.dart';
import '../providers/auth_provider.dart';
import 'package:provider/provider.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _controller = PageController();
  int _currentPage = 0;

  final List<_OnboardingData> _pages = [
    _OnboardingData(
      icon: Icons.camera_alt_rounded,
      title: 'Abadikan Momen\nTerbaik Anda',
      subtitle:
          'Studio fotografi profesional dengan sentuhan modern untuk hasil yang sempurna dan tak terlupakan.',
      gradient: const [Color(0xFFF43F5E), Color(0xFFEC4899)],
    ),
    _OnboardingData(
      icon: Icons.calendar_month_rounded,
      title: 'Booking Mudah\n& Cepat',
      subtitle:
          'Pilih paket, tentukan jadwal, dan lakukan pembayaran langsung dari genggaman tangan Anda.',
      gradient: const [Color(0xFF8B5CF6), Color(0xFFA855F7)],
    ),
    _OnboardingData(
      icon: Icons.smart_toy_rounded,
      title: 'AI-Powered\nAssistant',
      subtitle:
          'Asisten AI pintar siap membantu Anda memilih paket terbaik dan menjawab semua pertanyaan.',
      gradient: const [Color(0xFF3B82F6), Color(0xFF06B6D4)],
    ),
  ];

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onNext() {
    if (_currentPage < _pages.length - 1) {
      _controller.nextPage(
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOut,
      );
    } else {
      _finishOnboarding();
    }
  }

  void _finishOnboarding() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.setOnboardingDone();
    if (!mounted) return;
    Navigator.pushReplacementNamed(context, AppRoutes.main);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // Page View
          PageView.builder(
            controller: _controller,
            onPageChanged: (index) {
              setState(() => _currentPage = index);
            },
            itemCount: _pages.length,
            itemBuilder: (context, index) {
              return _buildPage(_pages[index]);
            },
          ),

          // Bottom controls
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    // Page indicator
                    SmoothPageIndicator(
                      controller: _controller,
                      count: _pages.length,
                      effect: ExpandingDotsEffect(
                        activeDotColor: PixoraTheme.primaryRose,
                        dotColor: PixoraTheme.primaryRose.withValues(alpha: 0.2),
                        dotHeight: 8,
                        dotWidth: 8,
                        expansionFactor: 4,
                      ),
                    ),
                    const SizedBox(height: 32),

                    // Buttons
                    Row(
                      children: [
                        if (_currentPage > 0)
                          Expanded(
                            child: TextButton(
                              onPressed: () => _controller.previousPage(
                                duration: const Duration(milliseconds: 400),
                                curve: Curves.easeInOut,
                              ),
                              child: Text(
                                'Kembali',
                                style: TextStyle(
                                  color: PixoraTheme.gray,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          )
                        else
                          Expanded(
                            child: TextButton(
                              onPressed: _finishOnboarding,
                              child: Text(
                                'Lewati',
                                style: TextStyle(
                                  color: PixoraTheme.gray,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),
                            ),
                          ),
                        const SizedBox(width: 16),
                        Expanded(
                          flex: 2,
                          child: Container(
                            decoration: BoxDecoration(
                              gradient: PixoraTheme.primaryGradient,
                              borderRadius: BorderRadius.circular(16),
                              boxShadow: PixoraTheme.roseShadow,
                            ),
                            child: ElevatedButton(
                              onPressed: _onNext,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                padding:
                                    const EdgeInsets.symmetric(vertical: 16),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(16),
                                ),
                              ),
                              child: Text(
                                _currentPage == _pages.length - 1
                                    ? 'Mulai Sekarang'
                                    : 'Lanjut',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPage(_OnboardingData data) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            data.gradient[0].withValues(alpha: 0.05),
            Colors.white,
          ],
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
        ),
      ),
      child: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Spacer(),
              // Icon
              Container(
                width: 140,
                height: 140,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: data.gradient,
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(36),
                  boxShadow: [
                    BoxShadow(
                      color: data.gradient[0].withValues(alpha: 0.3),
                      blurRadius: 40,
                      offset: const Offset(0, 16),
                    ),
                  ],
                ),
                child: Icon(
                  data.icon,
                  size: 64,
                  color: Colors.white,
                ),
              ).animate().scale(
                    begin: const Offset(0.8, 0.8),
                    end: const Offset(1.0, 1.0),
                    duration: 500.ms,
                    curve: Curves.elasticOut,
                  ),
              const SizedBox(height: 48),

              // Title
              Text(
                data.title,
                textAlign: TextAlign.center,
                style: GoogleFonts.poppins(
                  fontSize: 30,
                  fontWeight: FontWeight.bold,
                  color: PixoraTheme.dark,
                  height: 1.2,
                ),
              ).animate(delay: 200.ms).fadeIn(duration: 400.ms).slideY(
                    begin: 0.2,
                    end: 0,
                  ),

              const SizedBox(height: 16),

              // Subtitle
              Text(
                data.subtitle,
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(
                  fontSize: 16,
                  color: PixoraTheme.gray,
                  height: 1.6,
                ),
              ).animate(delay: 400.ms).fadeIn(duration: 400.ms),

              const Spacer(flex: 2),
            ],
          ),
        ),
      ),
    );
  }
}

class _OnboardingData {
  final IconData icon;
  final String title;
  final String subtitle;
  final List<Color> gradient;

  _OnboardingData({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.gradient,
  });
}
