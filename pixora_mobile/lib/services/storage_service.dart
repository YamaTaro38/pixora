// lib/services/storage_service.dart
// Service untuk penyimpanan lokal menggunakan SharedPreferences

import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  static const String _onboardingKey = 'onboarding_done';

  static Future<SharedPreferences> get _prefs async =>
      SharedPreferences.getInstance();

  // Auth Token
  static Future<void> saveToken(String token) async {
    final prefs = await _prefs;
    await prefs.setString(_tokenKey, token);
  }

  static Future<String?> getToken() async {
    final prefs = await _prefs;
    return prefs.getString(_tokenKey);
  }

  static Future<void> removeToken() async {
    final prefs = await _prefs;
    await prefs.remove(_tokenKey);
  }

  // User Data
  static Future<void> saveUserData(String userData) async {
    final prefs = await _prefs;
    await prefs.setString(_userKey, userData);
  }

  static Future<String?> getUserData() async {
    final prefs = await _prefs;
    return prefs.getString(_userKey);
  }

  static Future<void> removeUserData() async {
    final prefs = await _prefs;
    await prefs.remove(_userKey);
  }

  // Onboarding
  static Future<void> setOnboardingDone() async {
    final prefs = await _prefs;
    await prefs.setBool(_onboardingKey, true);
  }

  static Future<bool> isOnboardingDone() async {
    final prefs = await _prefs;
    return prefs.getBool(_onboardingKey) ?? false;
  }

  // Clear All
  static Future<void> clearAll() async {
    final prefs = await _prefs;
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
  }
}
