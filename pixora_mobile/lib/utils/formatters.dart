// lib/utils/formatters.dart
// Utility untuk formatting data

import 'package:intl/intl.dart';

class Formatters {
  // Currency formatter (Rupiah)
  static String currency(double amount) {
    final formatter = NumberFormat('#,###', 'id_ID');
    return 'Rp ${formatter.format(amount.toInt())}';
  }

  // Date formatter
  static String date(DateTime date) {
    return DateFormat('d MMMM yyyy', 'id_ID').format(date);
  }

  // Short date
  static String shortDate(DateTime date) {
    return DateFormat('dd/MM/yyyy').format(date);
  }

  // Date with day name
  static String dateWithDay(DateTime date) {
    return DateFormat('EEEE, d MMMM yyyy', 'id_ID').format(date);
  }

  // Time
  static String time(DateTime date) {
    return DateFormat('HH:mm').format(date);
  }

  // DateTime
  static String dateTime(DateTime date) {
    return DateFormat('d MMM yyyy HH:mm', 'id_ID').format(date);
  }

  // Relative time
  static String relativeTime(DateTime date) {
    final diff = DateTime.now().difference(date);
    if (diff.inDays > 30) return DateFormat('d MMM yyyy').format(date);
    if (diff.inDays > 0) return '${diff.inDays} hari lalu';
    if (diff.inHours > 0) return '${diff.inHours} jam lalu';
    if (diff.inMinutes > 0) return '${diff.inMinutes} menit lalu';
    return 'Baru saja';
  }

  // Month year
  static String monthYear(int year, int month) {
    final date = DateTime(year, month);
    return DateFormat('MMMM yyyy', 'id_ID').format(date);
  }

  // Phone number formatter
  static String phone(String phone) {
    if (phone.startsWith('08')) {
      phone = '+62${phone.substring(1)}';
    }
    return phone;
  }
}
