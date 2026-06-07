// lib/config/theme.dart
// Theme konfigurasi Pixora - Rose/Pink gradient design system

import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class PixoraTheme {
  // Brand Colors
  static const Color primaryRose = Color(0xFFF43F5E);
  static const Color primaryPink = Color(0xFFEC4899);
  static const Color darkRose = Color(0xFFBE123C);
  static const Color lightRose = Color(0xFFFFF1F2);
  static const Color roseAccent = Color(0xFFFDA4AF);

  // Neutral Colors
  static const Color dark = Color(0xFF1F2937);
  static const Color darkGray = Color(0xFF374151);
  static const Color gray = Color(0xFF6B7280);
  static const Color lightGray = Color(0xFFF3F4F6);
  static const Color white = Color(0xFFFFFFFF);
  static const Color background = Color(0xFFFAFAFA);

  // Status Colors
  static const Color success = Color(0xFF22C55E);
  static const Color warning = Color(0xFFF59E0B);
  static const Color error = Color(0xFFEF4444);
  static const Color info = Color(0xFF3B82F6);

  // Gradients
  static const LinearGradient primaryGradient = LinearGradient(
    colors: [primaryRose, primaryPink],
    begin: Alignment.centerLeft,
    end: Alignment.centerRight,
  );

  static const LinearGradient heroGradient = LinearGradient(
    colors: [Color(0xCC000000), Color(0x66000000), Color(0x00000000)],
    begin: Alignment.bottomCenter,
    end: Alignment.topCenter,
  );

  static const LinearGradient darkGradient = LinearGradient(
    colors: [Color(0xFF1F2937), Color(0xFF111827)],
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
  );

  // Border Radius
  static const double radiusSm = 8.0;
  static const double radiusMd = 12.0;
  static const double radiusLg = 16.0;
  static const double radiusXl = 24.0;
  static const double radiusFull = 100.0;

  // Shadows
  static List<BoxShadow> get softShadow => [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.05),
          blurRadius: 10,
          offset: const Offset(0, 4),
        ),
      ];

  static List<BoxShadow> get mediumShadow => [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.1),
          blurRadius: 20,
          offset: const Offset(0, 8),
        ),
      ];

  static List<BoxShadow> get roseShadow => [
        BoxShadow(
          color: primaryRose.withValues(alpha: 0.3),
          blurRadius: 20,
          offset: const Offset(0, 8),
        ),
      ];

  // Theme Data
  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.light,
      primaryColor: primaryRose,
      scaffoldBackgroundColor: background,
      colorScheme: ColorScheme.fromSeed(
        seedColor: primaryRose,
        primary: primaryRose,
        secondary: primaryPink,
        surface: white,
        error: error,
      ),
      textTheme: GoogleFonts.interTextTheme().copyWith(
        displayLarge: GoogleFonts.poppins(
          fontSize: 32,
          fontWeight: FontWeight.bold,
          color: dark,
        ),
        displayMedium: GoogleFonts.poppins(
          fontSize: 28,
          fontWeight: FontWeight.bold,
          color: dark,
        ),
        headlineLarge: GoogleFonts.poppins(
          fontSize: 24,
          fontWeight: FontWeight.bold,
          color: dark,
        ),
        headlineMedium: GoogleFonts.poppins(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: dark,
        ),
        titleLarge: GoogleFonts.inter(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: dark,
        ),
        titleMedium: GoogleFonts.inter(
          fontSize: 16,
          fontWeight: FontWeight.w500,
          color: dark,
        ),
        bodyLarge: GoogleFonts.inter(
          fontSize: 16,
          color: darkGray,
        ),
        bodyMedium: GoogleFonts.inter(
          fontSize: 14,
          color: gray,
        ),
        bodySmall: GoogleFonts.inter(
          fontSize: 12,
          color: gray,
        ),
      ),
      appBarTheme: AppBarTheme(
        backgroundColor: white,
        foregroundColor: dark,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: GoogleFonts.poppins(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: dark,
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryRose,
          foregroundColor: white,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(radiusMd),
          ),
          textStyle: GoogleFonts.inter(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: lightGray,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: primaryRose, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(radiusMd),
          borderSide: const BorderSide(color: error, width: 1),
        ),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        hintStyle: GoogleFonts.inter(color: gray, fontSize: 14),
      ),
      cardTheme: CardThemeData(
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(radiusLg),
        ),
        color: white,
      ),
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: white,
        selectedItemColor: primaryRose,
        unselectedItemColor: gray,
        type: BottomNavigationBarType.fixed,
        elevation: 8,
      ),
    );
  }
}
