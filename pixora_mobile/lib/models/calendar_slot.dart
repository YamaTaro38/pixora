// lib/models/calendar_slot.dart
// Model untuk data kalender dan slot waktu

class TimeSlotData {
  final bool available;
  final String label;
  final String startTime;
  final String endTime;
  final String icon;
  final bool isBooked;
  final bool isPastSlot;
  final bool isDatePast;

  TimeSlotData({
    required this.available,
    required this.label,
    required this.startTime,
    required this.endTime,
    required this.icon,
    this.isBooked = false,
    this.isPastSlot = false,
    this.isDatePast = false,
  });

  factory TimeSlotData.fromJson(Map<String, dynamic> json) {
    return TimeSlotData(
      available: json['available'] ?? false,
      label: json['label'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      icon: json['icon'] ?? 'fa-clock',
      isBooked: json['is_booked'] ?? false,
      isPastSlot: json['is_past_slot'] ?? false,
      isDatePast: json['is_date_past'] ?? false,
    );
  }

  String get timeRange => '$startTime - $endTime';

  String get statusLabel {
    if (isBooked) return 'Sudah Dibooking';
    if (isPastSlot) return 'Sudah Lewat';
    if (isDatePast) return 'Tanggal Lewat';
    if (available) return 'Tersedia';
    return 'Tidak Tersedia';
  }
}

class CalendarDayData {
  final String date;
  final int day;
  final bool isToday;
  final bool isPast;
  final int totalAvailableSlots;
  final Map<String, TimeSlotData> slots;

  CalendarDayData({
    required this.date,
    required this.day,
    this.isToday = false,
    this.isPast = false,
    this.totalAvailableSlots = 0,
    this.slots = const {},
  });

  factory CalendarDayData.fromJson(String dateKey, Map<String, dynamic> json) {
    final slotsJson = json['slots'] as Map<String, dynamic>? ?? {};
    final slots = slotsJson.map(
      (key, value) => MapEntry(key, TimeSlotData.fromJson(value)),
    );

    return CalendarDayData(
      date: dateKey,
      day: json['day'] ?? 1,
      isToday: json['is_today'] ?? false,
      isPast: json['is_past'] ?? false,
      totalAvailableSlots: json['total_available_slots'] ?? 0,
      slots: slots,
    );
  }

  bool get isFullyBooked => totalAvailableSlots == 0 && !isPast;
  bool get hasAvailableSlots => totalAvailableSlots > 0;
  bool get isPartiallyAvailable =>
      totalAvailableSlots > 0 && totalAvailableSlots < 3;
}
