// lib/screens/booking/booking_form_screen.dart
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../config/routes.dart';
import '../../providers/calendar_provider.dart';
import '../../providers/package_provider.dart';
import '../../providers/booking_provider.dart';
import '../../utils/validators.dart';
import '../../utils/formatters.dart';
import '../../widgets/gradient_button.dart';

class BookingFormScreen extends StatefulWidget {
  const BookingFormScreen({super.key});
  @override
  State<BookingFormScreen> createState() => _BookingFormScreenState();
}

class _BookingFormScreenState extends State<BookingFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();
  String _paymentType = 'full';
  int? _selectedPackageId;

  @override
  void dispose() { _nameCtrl.dispose(); _phoneCtrl.dispose(); _emailCtrl.dispose(); _notesCtrl.dispose(); super.dispose(); }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedPackageId == null) { ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Pilih paket terlebih dahulu'))); return; }
    final cal = Provider.of<CalendarProvider>(context, listen: false);
    final booking = Provider.of<BookingProvider>(context, listen: false);
    final result = await booking.createBooking(
      packageId: _selectedPackageId!, customerName: _nameCtrl.text.trim(), customerPhone: _phoneCtrl.text.trim(),
      customerEmail: _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
      bookingDate: '${cal.selectedDate.year}-${cal.selectedDate.month.toString().padLeft(2, '0')}-${cal.selectedDate.day.toString().padLeft(2, '0')}',
      timeSlot: cal.selectedSlot!, specialRequests: _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(), paymentType: _paymentType);
    if (!mounted) return;
    if (result != null) {
      Navigator.pushNamed(context, AppRoutes.payment);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(booking.error ?? 'Booking gagal'), backgroundColor: PixoraTheme.error, behavior: SnackBarBehavior.floating));
    }
  }

  @override
  Widget build(BuildContext context) {
    final cal = Provider.of<CalendarProvider>(context);
    final pkgProv = Provider.of<PackageProvider>(context);
    return Scaffold(
      appBar: AppBar(title: Text('Form Booking', style: GoogleFonts.poppins(fontWeight: FontWeight.bold))),
      body: SingleChildScrollView(padding: const EdgeInsets.all(20), child: Form(key: _formKey, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Booking summary
        Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(gradient: PixoraTheme.primaryGradient, borderRadius: BorderRadius.circular(16)),
          child: Row(children: [
            const Icon(Icons.calendar_today, color: Colors.white, size: 28),
            const SizedBox(width: 12),
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(Formatters.date(cal.selectedDate), style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
              Text(cal.selectedDayData?.slots[cal.selectedSlot]?.timeRange ?? '', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13)),
            ]),
          ])),
        const SizedBox(height: 24),

        // Package selection
        Text('Pilih Paket', style: GoogleFonts.poppins(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        ...pkgProv.packages.map((pkg) => GestureDetector(onTap: () => setState(() => _selectedPackageId = pkg.id),
          child: Container(margin: const EdgeInsets.only(bottom: 8), padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(color: _selectedPackageId == pkg.id ? PixoraTheme.lightRose : Colors.white, borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _selectedPackageId == pkg.id ? PixoraTheme.primaryRose : const Color(0xFFE5E7EB), width: _selectedPackageId == pkg.id ? 2 : 1)),
            child: Row(children: [
              Icon(_selectedPackageId == pkg.id ? Icons.radio_button_checked : Icons.radio_button_off, color: _selectedPackageId == pkg.id ? PixoraTheme.primaryRose : PixoraTheme.gray),
              const SizedBox(width: 12),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(pkg.name, style: const TextStyle(fontWeight: FontWeight.w600, color: PixoraTheme.dark)),
                Text(pkg.formattedPrice, style: const TextStyle(fontSize: 13, color: PixoraTheme.primaryRose, fontWeight: FontWeight.w500)),
              ])),
            ])))),
        const SizedBox(height: 20),

        // Customer info
        Text('Data Pemesan', style: GoogleFonts.poppins(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        TextFormField(controller: _nameCtrl, validator: Validators.name, textCapitalization: TextCapitalization.words,
          decoration: const InputDecoration(hintText: 'Nama Lengkap', prefixIcon: Icon(Icons.person_outlined, color: PixoraTheme.gray))),
        const SizedBox(height: 12),
        TextFormField(controller: _phoneCtrl, keyboardType: TextInputType.phone, validator: (v) => Validators.required(v, field: 'No. Telepon'),
          decoration: const InputDecoration(hintText: 'No. Telepon', prefixIcon: Icon(Icons.phone_outlined, color: PixoraTheme.gray))),
        const SizedBox(height: 12),
        TextFormField(controller: _emailCtrl, keyboardType: TextInputType.emailAddress,
          decoration: const InputDecoration(hintText: 'Email (opsional)', prefixIcon: Icon(Icons.email_outlined, color: PixoraTheme.gray))),
        const SizedBox(height: 12),
        TextFormField(controller: _notesCtrl, maxLines: 3,
          decoration: const InputDecoration(hintText: 'Catatan khusus (opsional)', prefixIcon: Icon(Icons.note_outlined, color: PixoraTheme.gray), alignLabelWithHint: true)),
        const SizedBox(height: 20),

        // Payment type
        Text('Tipe Pembayaran', style: GoogleFonts.poppins(fontSize: 16, fontWeight: FontWeight.w600)),
        const SizedBox(height: 8),
        Row(children: [
          Expanded(child: GestureDetector(onTap: () => setState(() => _paymentType = 'full'),
            child: Container(padding: const EdgeInsets.all(14), decoration: BoxDecoration(color: _paymentType == 'full' ? PixoraTheme.lightRose : Colors.white, borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _paymentType == 'full' ? PixoraTheme.primaryRose : const Color(0xFFE5E7EB), width: _paymentType == 'full' ? 2 : 1)),
              child: Column(children: [Icon(Icons.payment, color: _paymentType == 'full' ? PixoraTheme.primaryRose : PixoraTheme.gray), const SizedBox(height: 4),
                Text('Full Payment', style: TextStyle(fontWeight: FontWeight.w600, color: _paymentType == 'full' ? PixoraTheme.primaryRose : PixoraTheme.dark, fontSize: 13))])))),
          const SizedBox(width: 12),
          Expanded(child: GestureDetector(onTap: () => setState(() => _paymentType = 'down_payment'),
            child: Container(padding: const EdgeInsets.all(14), decoration: BoxDecoration(color: _paymentType == 'down_payment' ? PixoraTheme.lightRose : Colors.white, borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _paymentType == 'down_payment' ? PixoraTheme.primaryRose : const Color(0xFFE5E7EB), width: _paymentType == 'down_payment' ? 2 : 1)),
              child: Column(children: [Icon(Icons.account_balance_wallet, color: _paymentType == 'down_payment' ? PixoraTheme.primaryRose : PixoraTheme.gray), const SizedBox(height: 4),
                Text('Down Payment', style: TextStyle(fontWeight: FontWeight.w600, color: _paymentType == 'down_payment' ? PixoraTheme.primaryRose : PixoraTheme.dark, fontSize: 13))])))),
        ]),
        const SizedBox(height: 32),

        Consumer<BookingProvider>(builder: (ctx, b, _) => GradientButton(text: 'Proses Booking', icon: Icons.check_circle, onPressed: _submit, isLoading: b.isLoading)),
        const SizedBox(height: 20),
      ]))),
    );
  }
}
