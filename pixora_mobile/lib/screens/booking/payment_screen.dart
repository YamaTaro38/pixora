// lib/screens/booking/payment_screen.dart
import 'dart:async';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../config/api_config.dart';
import '../../providers/booking_provider.dart';

class PaymentScreen extends StatefulWidget {
  const PaymentScreen({super.key});

  @override
  State<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends State<PaymentScreen> {
  late final WebViewController _webViewController;
  bool _isLoading = true;
  bool _paymentSuccess = false;
  Timer? _statusTimer;

  @override
  void initState() {
    super.initState();
    _initWebView();
    _startStatusPolling();
  }

  @override
  void dispose() {
    _statusTimer?.cancel();
    super.dispose();
  }

  void _initWebView() {
    final booking = Provider.of<BookingProvider>(context, listen: false).currentBooking;
    if (booking == null) return;

    final paymentUrl = ApiConfig.paymentUrl(booking.publicToken);

    _webViewController = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (_) {
          if (mounted) setState(() => _isLoading = true);
        },
        onPageFinished: (url) {
          if (mounted) setState(() => _isLoading = false);
          // Check if redirected to booking show page with success
          if (url.contains('payment=success') || url.contains('payment=cod')) {
            _showSuccessDialog();
          }
        },
        onWebResourceError: (error) {
          if (mounted) setState(() => _isLoading = false);
        },
      ))
      ..loadRequest(Uri.parse(paymentUrl));
  }

  void _startStatusPolling() {
    final booking = Provider.of<BookingProvider>(context, listen: false).currentBooking;
    if (booking == null) return;

    _statusTimer = Timer.periodic(const Duration(seconds: 3), (timer) async {
      if (_paymentSuccess) {
        timer.cancel();
        return;
      }

      final bookingProvider = Provider.of<BookingProvider>(context, listen: false);
      final status = await bookingProvider.checkPaymentStatus(booking.publicToken);
      
      if (status != null && status['is_paid'] == true) {
        timer.cancel();
        _showSuccessDialog();
      }
    });
  }

  void _showSuccessDialog() {
    if (_paymentSuccess) return;
    setState(() => _paymentSuccess = true);
    _statusTimer?.cancel();

    showDialog(
      context: context,
      barrierDismissible: false,
      barrierColor: Colors.black.withValues(alpha: 0.7),
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 80, height: 80,
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(40),
                ),
                child: const Icon(Icons.check_circle, color: Color(0xFF22C55E), size: 48),
              ),
              const SizedBox(height: 20),
              Text('🎉 Selamat!',
                style: GoogleFonts.poppins(fontSize: 24, fontWeight: FontWeight.bold, color: PixoraTheme.dark)),
              const SizedBox(height: 8),
              Text('Booking Anda Berhasil!',
                style: GoogleFonts.poppins(fontSize: 18, fontWeight: FontWeight.w600, color: const Color(0xFF16A34A))),
              const SizedBox(height: 12),
              Text('Terima kasih telah booking di Pixora Studio.\nBooking Anda sudah aktif dan terkonfirmasi.',
                textAlign: TextAlign.center,
                style: GoogleFonts.inter(fontSize: 14, color: PixoraTheme.gray, height: 1.5)),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.of(ctx).pop(); // close dialog
                    Navigator.pushNamedAndRemoveUntil(context, AppRoutes.bookingDetail, (route) => route.isFirst);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: PixoraTheme.primaryRose,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                  child: Text('Lihat Detail Booking',
                    style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
                ),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: () {
                  Navigator.of(ctx).pop(); // close dialog
                  Navigator.pushNamedAndRemoveUntil(context, AppRoutes.main, (_) => false);
                },
                child: Text('Kembali ke Beranda',
                  style: GoogleFonts.inter(color: PixoraTheme.gray)),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final booking = Provider.of<BookingProvider>(context).currentBooking;
    
    if (booking == null) {
      return Scaffold(
        appBar: AppBar(
          title: Text('Pembayaran', style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
        ),
        body: const Center(child: Text('Booking tidak ditemukan')),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: Text('Pembayaran', style: GoogleFonts.poppins(fontWeight: FontWeight.bold)),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            if (_paymentSuccess) {
              Navigator.pushNamedAndRemoveUntil(context, AppRoutes.main, (_) => false);
            } else {
              Navigator.pop(context);
            }
          },
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _webViewController),
          if (_isLoading)
            const Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  CircularProgressIndicator(color: PixoraTheme.primaryRose),
                  SizedBox(height: 16),
                  Text('Memuat halaman pembayaran...', style: TextStyle(color: PixoraTheme.gray)),
                ],
              ),
            ),
        ],
      ),
    );
  }
}