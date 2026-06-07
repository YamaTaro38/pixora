// lib/config/api_config.dart
// Konfigurasi API untuk koneksi ke backend Laravel Pixora

import 'dart:io' show Platform;

class ApiConfig {
  // ===== KONFIGURASI BASE URL =====
  // Ganti sesuai environment Anda:
  //
  // Android Emulator  → http://10.0.2.2:8000
  // iOS Simulator     → http://localhost:8000
  // Device Fisik      → http://<IP_KOMPUTER>:8000
  //                     Contoh: http://192.168.1.100:8000
  //
  // Tip: Jalankan `ipconfig` (Windows) atau `ifconfig` (Mac/Linux)
  //      untuk mengetahui IP komputer Anda.

  static String get baseUrl {
    // Otomatis detect platform
    try {
      if (Platform.isAndroid) {
        return 'http://localhost:8000';
      } else if (Platform.isIOS) {
        return 'http://localhost:8000';
      }
    } catch (_) {}
    // Fallback untuk device fisik — ganti IP ini sesuai jaringan Anda
    return 'http://localhost:8000';
  }

  // API Endpoints
  static const String apiPrefix = '/api';

  // Auth
  static const String login = '$apiPrefix/login';
  static const String register = '$apiPrefix/register';
  static const String logout = '$apiPrefix/logout';
  static const String profile = '$apiPrefix/profile';
  static const String updateProfile = '$apiPrefix/profile';
  static const String changePassword = '$apiPrefix/change-password';

  // Packages
  static const String packages = '$apiPrefix/packages';
  static String packageDetail(String slug) => '$apiPrefix/packages/$slug';

  // Calendar
  static const String calendarData = '$apiPrefix/calendar/data';

  // Booking
  static const String checkAvailability = '$apiPrefix/booking/check-availability';
  static const String createBooking = '$apiPrefix/booking/store';
  static String bookingDetail(String token) => '$apiPrefix/booking/$token';
  static String bookingPayment(String token) => '/booking/$token/payment';
  static String bookingStatus(String token) => '$apiPrefix/booking/$token/status';
  static String bookingCod(String token) => '$apiPrefix/booking/$token/cod';
  static String bookingInvoice(String token) => '/booking/$token/invoice';

  // AI Chat — sekarang via API route (tanpa CSRF)
  static const String aiChat = '$apiPrefix/ai/chat';

  // Health check
  static const String ping = '$apiPrefix/ping';

  // Full URL helpers
  static String fullUrl(String path) => '$baseUrl$path';
  static String paymentUrl(String token) => '$baseUrl/booking/$token/payment';
  static String invoiceUrl(String token) => '$baseUrl/booking/$token/invoice';
}
