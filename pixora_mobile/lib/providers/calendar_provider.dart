// lib/providers/calendar_provider.dart
// Provider untuk state management kalender

import 'package:flutter/material.dart';
import '../models/calendar_slot.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class CalendarProvider extends ChangeNotifier {
  final ApiService _api = ApiService();

  Map<String, CalendarDayData> _calendarData = {};
  DateTime _selectedDate = DateTime.now();
  String? _selectedSlot;
  int _currentYear = DateTime.now().year;
  int _currentMonth = DateTime.now().month;
  bool _isLoading = false;
  String? _error;

  Map<String, CalendarDayData> get calendarData => _calendarData;
  DateTime get selectedDate => _selectedDate;
  String? get selectedSlot => _selectedSlot;
  int get currentYear => _currentYear;
  int get currentMonth => _currentMonth;
  bool get isLoading => _isLoading;
  String? get error => _error;

  CalendarDayData? get selectedDayData {
    final key =
        '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';
    return _calendarData[key];
  }

  // Fetch calendar data untuk bulan tertentu
  Future<void> fetchCalendarData({int? year, int? month}) async {
    _currentYear = year ?? _currentYear;
    _currentMonth = month ?? _currentMonth;
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get(
        ApiConfig.calendarData,
        queryParams: {
          'year': _currentYear.toString(),
          'month': _currentMonth.toString(),
        },
      );

      if (response.success && response.data != null) {
        final Map<String, dynamic> calendarJson =
            response.data['calendarData'] ?? response.data;
        _calendarData = calendarJson.map(
          (key, value) =>
              MapEntry(key, CalendarDayData.fromJson(key, value)),
        );
      } else {
        _error = response.error;
        // Generate demo data
        _calendarData = _generateDemoCalendarData();
      }
    } catch (e) {
      _error = e.toString();
      _calendarData = _generateDemoCalendarData();
    }

    _isLoading = false;
    notifyListeners();
  }

  void selectDate(DateTime date) {
    _selectedDate = date;
    _selectedSlot = null;
    notifyListeners();
  }

  void selectSlot(String? slot) {
    _selectedSlot = slot;
    notifyListeners();
  }

  void changeMonth(int year, int month) {
    _currentYear = year;
    _currentMonth = month;
    fetchCalendarData(year: year, month: month);
  }

  // Demo calendar data
  Map<String, CalendarDayData> _generateDemoCalendarData() {
    final Map<String, CalendarDayData> data = {};
    final now = DateTime.now();
    final daysInMonth =
        DateTime(_currentYear, _currentMonth + 1, 0).day;

    for (int day = 1; day <= daysInMonth; day++) {
      final date = DateTime(_currentYear, _currentMonth, day);
      final dateStr =
          '$_currentYear-${_currentMonth.toString().padLeft(2, '0')}-${day.toString().padLeft(2, '0')}';
      final isPast = date.isBefore(DateTime(now.year, now.month, now.day));
      final isToday = date.year == now.year &&
          date.month == now.month &&
          date.day == now.day;

      int available = isPast ? 0 : 3;
      // Simulated booked slots
      if (day % 7 == 0) available = 0;
      if (day % 5 == 0) available = 1;

      data[dateStr] = CalendarDayData(
        date: dateStr,
        day: day,
        isToday: isToday,
        isPast: isPast,
        totalAvailableSlots: available,
        slots: {
          'morning': TimeSlotData(
            available: !isPast && available > 0,
            label: 'Pagi',
            startTime: '08:00',
            endTime: '11:00',
            icon: 'fa-sun',
            isBooked: day % 7 == 0,
            isPastSlot: isPast,
            isDatePast: isPast,
          ),
          'afternoon': TimeSlotData(
            available: !isPast && available > 1,
            label: 'Siang',
            startTime: '13:00',
            endTime: '16:00',
            icon: 'fa-cloud-sun',
            isBooked: day % 5 == 0,
            isPastSlot: isPast,
            isDatePast: isPast,
          ),
          'evening': TimeSlotData(
            available: !isPast && available > 2,
            label: 'Sore',
            startTime: '17:00',
            endTime: '20:00',
            icon: 'fa-moon',
            isBooked: false,
            isPastSlot: isPast,
            isDatePast: isPast,
          ),
        },
      );
    }
    return data;
  }
}
