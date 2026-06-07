// lib/widgets/slot_chip.dart
// Chip widget untuk slot waktu di kalender

import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../models/calendar_slot.dart';

class SlotChip extends StatelessWidget {
  final String slotKey;
  final TimeSlotData slot;
  final bool isSelected;
  final VoidCallback? onTap;

  const SlotChip({
    super.key,
    required this.slotKey,
    required this.slot,
    this.isSelected = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isAvailable = slot.available;

    IconData icon;
    switch (slotKey) {
      case 'morning':
        icon = Icons.wb_sunny;
        break;
      case 'afternoon':
        icon = Icons.wb_cloudy;
        break;
      case 'evening':
        icon = Icons.nightlight_round;
        break;
      default:
        icon = Icons.access_time;
    }

    return GestureDetector(
      onTap: isAvailable ? onTap : null,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          gradient: isSelected ? PixoraTheme.primaryGradient : null,
          color: isSelected
              ? null
              : isAvailable
                  ? Colors.white
                  : PixoraTheme.lightGray,
          borderRadius: BorderRadius.circular(PixoraTheme.radiusMd),
          border: Border.all(
            color: isSelected
                ? Colors.transparent
                : isAvailable
                    ? PixoraTheme.primaryRose.withValues(alpha: 0.3)
                    : Colors.transparent,
            width: 1.5,
          ),
          boxShadow: isSelected ? PixoraTheme.roseShadow : null,
        ),
        child: Row(
          children: [
            Icon(
              icon,
              size: 20,
              color: isSelected
                  ? Colors.white
                  : isAvailable
                      ? PixoraTheme.primaryRose
                      : PixoraTheme.gray,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    slot.label,
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                      color: isSelected
                          ? Colors.white
                          : isAvailable
                              ? PixoraTheme.dark
                              : PixoraTheme.gray,
                    ),
                  ),
                  Text(
                    slot.timeRange,
                    style: TextStyle(
                      fontSize: 12,
                      color: isSelected
                          ? Colors.white.withValues(alpha: 0.8)
                          : PixoraTheme.gray,
                    ),
                  ),
                ],
              ),
            ),
            if (!isAvailable)
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: slot.isBooked
                      ? PixoraTheme.error.withValues(alpha: 0.1)
                      : PixoraTheme.gray.withValues(alpha: 0.1),
                  borderRadius:
                      BorderRadius.circular(PixoraTheme.radiusFull),
                ),
                child: Text(
                  slot.isBooked ? 'Penuh' : 'Lewat',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w600,
                    color:
                        slot.isBooked ? PixoraTheme.error : PixoraTheme.gray,
                  ),
                ),
              ),
            if (isAvailable && isSelected)
              const Icon(Icons.check_circle, color: Colors.white, size: 20),
          ],
        ),
      ),
    );
  }
}
