// lib/widgets/booking_card.dart
// Card widget untuk menampilkan riwayat booking

import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../models/booking.dart';
import '../utils/formatters.dart';

class BookingCard extends StatelessWidget {
  final Booking booking;
  final VoidCallback? onTap;

  const BookingCard({
    super.key,
    required this.booking,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(PixoraTheme.radiusLg),
          boxShadow: PixoraTheme.softShadow,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    booking.bookingCode,
                    style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                      color: PixoraTheme.dark,
                    ),
                  ),
                ),
                _statusBadge(),
              ],
            ),
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 12),

            // Details
            if (booking.packageName != null)
              _detailRow(Icons.camera_alt, booking.packageName!),
            _detailRow(
                Icons.calendar_today, Formatters.date(booking.bookingDate)),
            _detailRow(Icons.access_time, booking.timeSlotLabel),
            const SizedBox(height: 8),

            // Price
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  booking.formattedTotalPrice,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: PixoraTheme.primaryRose,
                  ),
                ),
                const Icon(
                  Icons.chevron_right,
                  color: PixoraTheme.gray,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _statusBadge() {
    Color bgColor;
    Color textColor;

    switch (booking.paymentStatus) {
      case 'lunas':
        bgColor = PixoraTheme.success.withValues(alpha: 0.1);
        textColor = PixoraTheme.success;
        break;
      case 'dp_paid':
        bgColor = PixoraTheme.info.withValues(alpha: 0.1);
        textColor = PixoraTheme.info;
        break;
      case 'pending':
        bgColor = PixoraTheme.warning.withValues(alpha: 0.1);
        textColor = PixoraTheme.warning;
        break;
      case 'expired':
      case 'cancelled':
        bgColor = PixoraTheme.error.withValues(alpha: 0.1);
        textColor = PixoraTheme.error;
        break;
      default:
        bgColor = PixoraTheme.gray.withValues(alpha: 0.1);
        textColor = PixoraTheme.gray;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(PixoraTheme.radiusFull),
      ),
      child: Text(
        booking.paymentStatusLabel,
        style: TextStyle(
          color: textColor,
          fontSize: 11,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }

  Widget _detailRow(IconData icon, String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        children: [
          Icon(icon, size: 16, color: PixoraTheme.primaryRose),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                fontSize: 13,
                color: PixoraTheme.darkGray,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
