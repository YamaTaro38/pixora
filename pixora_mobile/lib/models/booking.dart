// lib/models/booking.dart
// Model Booking sesuai dengan tabel bookings di backend Laravel

class Booking {
  final int id;
  final String bookingCode;
  final String publicToken;
  final String customerName;
  final String customerPhone;
  final String? customerEmail;
  final int packageId;
  final DateTime bookingDate;
  final String timeSlot;
  final double totalPrice;
  final double? downPayment;
  final String paymentStatus;
  final String sessionStatus;
  final String bookingStatus;
  final DateTime? expiresAt;
  final String? specialRequests;
  final String? adminNotes;
  final DateTime? paidAt;
  final String? paymentMethod;
  final String? paymentTransactionId;
  final String? packageName;
  final String? snapToken;

  Booking({
    required this.id,
    required this.bookingCode,
    required this.publicToken,
    required this.customerName,
    required this.customerPhone,
    this.customerEmail,
    required this.packageId,
    required this.bookingDate,
    required this.timeSlot,
    required this.totalPrice,
    this.downPayment,
    this.paymentStatus = 'pending',
    this.sessionStatus = 'upcoming',
    this.bookingStatus = 'draft',
    this.expiresAt,
    this.specialRequests,
    this.adminNotes,
    this.paidAt,
    this.paymentMethod,
    this.paymentTransactionId,
    this.packageName,
    this.snapToken,
  });

  factory Booking.fromJson(Map<String, dynamic> json) {
    return Booking(
      id: json['id'] ?? 0,
      bookingCode: json['booking_code'] ?? '',
      publicToken: json['public_token'] ?? '',
      customerName: json['customer_name'] ?? '',
      customerPhone: json['customer_phone'] ?? '',
      customerEmail: json['customer_email'],
      packageId: json['package_id'] ?? 0,
      bookingDate: DateTime.parse(json['booking_date'] ?? DateTime.now().toIso8601String()),
      timeSlot: json['time_slot'] ?? 'morning',
      totalPrice: double.parse(json['total_price'].toString()),
      downPayment: json['down_payment'] != null ? double.parse(json['down_payment'].toString()) : null,
      paymentStatus: json['payment_status'] ?? 'pending',
      sessionStatus: json['session_status'] ?? 'upcoming',
      bookingStatus: json['booking_status'] ?? 'draft',
      expiresAt: json['expires_at'] != null ? DateTime.tryParse(json['expires_at']) : null,
      specialRequests: json['special_requests'],
      adminNotes: json['admin_notes'],
      paidAt: json['paid_at'] != null ? DateTime.tryParse(json['paid_at']) : null,
      paymentMethod: json['payment_method'],
      paymentTransactionId: json['payment_transaction_id'],
      packageName: json['package'] != null ? json['package']['name'] : null,
      snapToken: json['snap_token'],
    );
  }

  String get timeSlotLabel {
    switch (timeSlot) {
      case 'morning':
        return 'Pagi (08:00-11:00)';
      case 'afternoon':
        return 'Siang (13:00-16:00)';
      case 'evening':
        return 'Sore (17:00-20:00)';
      default:
        return timeSlot;
    }
  }

  String get formattedTotalPrice {
    return 'Rp ${_formatNumber(totalPrice.toInt())}';
  }

  String? get formattedDownPayment {
    if (downPayment == null || downPayment == 0) return null;
    return 'Rp ${_formatNumber(downPayment!.toInt())}';
  }

  String get paymentStatusLabel {
    switch (paymentStatus) {
      case 'pending':
        return 'Menunggu Pembayaran';
      case 'lunas':
        return 'Lunas';
      case 'dp_paid':
        return 'DP Terbayar';
      case 'expired':
        return 'Kadaluarsa';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return paymentStatus;
    }
  }

  String get sessionStatusLabel {
    switch (sessionStatus) {
      case 'upcoming':
        return 'Akan Datang';
      case 'completed':
        return 'Selesai';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return sessionStatus;
    }
  }

  bool get isPaid => paymentStatus == 'lunas' || paymentStatus == 'dp_paid';
  bool get isPending => paymentStatus == 'pending';
  bool get isExpired => paymentStatus == 'expired';
  bool get isCancelled => paymentStatus == 'cancelled' || bookingStatus == 'cancelled';

  bool get isAccessible {
    if (paymentStatus == 'expired') return false;
    if (paymentStatus == 'cancelled') return false;
    if (sessionStatus == 'cancelled') return false;
    return true;
  }

  static String _formatNumber(int number) {
    String result = '';
    String numStr = number.toString();
    int count = 0;
    for (int i = numStr.length - 1; i >= 0; i--) {
      count++;
      result = numStr[i] + result;
      if (count % 3 == 0 && i > 0) {
        result = '.$result';
      }
    }
    return result;
  }
}
