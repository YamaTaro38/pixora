// lib/providers/booking_provider.dart
// Provider untuk state management booking

import 'package:flutter/material.dart';
import '../models/booking.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class BookingProvider extends ChangeNotifier {
  final ApiService _api = ApiService();

  final List<Booking> _bookings = [];
  Booking? _currentBooking;
  bool _isLoading = false;
  String? _error;

  List<Booking> get bookings => _bookings;
  Booking? get currentBooking => _currentBooking;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Check slot availability
  Future<bool> checkAvailability(String date, String timeSlot) async {
    try {
      final response = await _api.post(
        ApiConfig.checkAvailability,
        body: {
          'date': date,
          'time_slot': timeSlot,
        },
      );

      if (response.success && response.data != null) {
        return response.data['available'] == true;
      }
      return false;
    } catch (_) {
      return false;
    }
  }

  // Create booking
  Future<Booking?> createBooking({
    required int packageId,
    required String customerName,
    required String customerPhone,
    String? customerEmail,
    required String bookingDate,
    required String timeSlot,
    String? specialRequests,
    required String paymentType,
  }) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.post(
        ApiConfig.createBooking,
        body: {
          'package_id': packageId,
          'customer_name': customerName,
          'customer_phone': customerPhone,
          'customer_email': customerEmail,
          'booking_date': bookingDate,
          'time_slot': timeSlot,
          'special_requests': specialRequests,
          'payment_type': paymentType,
        },
      );

      if (response.success && response.data != null) {
        _currentBooking = Booking.fromJson(response.data['booking'] ?? response.data);
        _isLoading = false;
        notifyListeners();
        return _currentBooking;
      } else {
        _error = response.error;
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
    return null;
  }

  // Get booking detail
  Future<Booking?> getBookingDetail(String token) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get(ApiConfig.bookingDetail(token));

      if (response.success && response.data != null) {
        _currentBooking = Booking.fromJson(response.data['booking'] ?? response.data);
        _isLoading = false;
        notifyListeners();
        return _currentBooking;
      } else {
        _error = response.error;
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
    return null;
  }

  // Check payment status
  Future<Map<String, dynamic>?> checkPaymentStatus(String token) async {
    try {
      final response = await _api.get(ApiConfig.bookingStatus(token));
      if (response.success && response.data != null) {
        return Map<String, dynamic>.from(response.data);
      }
    } catch (_) {}
    return null;
  }

  void setCurrentBooking(Booking booking) {
    _currentBooking = booking;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
