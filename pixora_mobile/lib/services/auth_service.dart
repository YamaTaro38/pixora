// lib/services/auth_service.dart
// Service untuk autentikasi pengguna

import 'dart:convert';
import '../config/api_config.dart';
import '../models/user.dart';
import 'api_service.dart';
import 'storage_service.dart';

class AuthService {
  final ApiService _api = ApiService();

  // Login
  Future<User?> login(String email, String password) async {
    final response = await _api.post(
      ApiConfig.login,
      body: {
        'email': email,
        'password': password,
      },
    );

    if (response.success && response.data != null) {
      final token = response.data['token'] ?? response.data['access_token'];
      final userData = response.data['user'] ?? response.data;

      if (token != null) {
        await StorageService.saveToken(token);
      }

      final user = User.fromJson(userData is Map<String, dynamic>
          ? {...userData, 'token': token}
          : {'token': token});
      await StorageService.saveUserData(jsonEncode(user.toJson()));

      return user;
    }

    throw Exception(response.error ?? 'Login gagal');
  }

  // Register
  Future<User?> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? phone,
  }) async {
    final response = await _api.post(
      ApiConfig.register,
      body: {
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        'phone': phone,
      },
    );

    if (response.success && response.data != null) {
      final token = response.data['token'] ?? response.data['access_token'];
      final userData = response.data['user'] ?? response.data;

      if (token != null) {
        await StorageService.saveToken(token);
      }

      final user = User.fromJson(userData is Map<String, dynamic>
          ? {...userData, 'token': token}
          : {'token': token});
      await StorageService.saveUserData(jsonEncode(user.toJson()));

      return user;
    }

    throw Exception(response.error ?? 'Registrasi gagal');
  }

  // Logout
  Future<void> logout() async {
    try {
      await _api.post(ApiConfig.logout);
    } catch (_) {
      // Ignore logout API errors
    }
    await StorageService.clearAll();
  }

  // Get Profile
  Future<User?> getProfile() async {
    final response = await _api.get(ApiConfig.profile);
    if (response.success && response.data != null) {
      final userData = response.data['user'] ?? response.data;
      return User.fromJson(userData);
    }
    return null;
  }

  // Update Profile
  Future<User?> updateProfile({
    required String name,
    String? phone,
    String? avatarPath,
  }) async {
    final fields = <String, String>{
      'name': name,
    };
    if (phone != null) {
      fields['phone'] = phone;
    }

    final response = await _api.multipartPost(
      ApiConfig.updateProfile,
      fields: fields,
      fileField: avatarPath != null ? 'avatar' : null,
      filePath: avatarPath,
    );

    if (response.success && response.data != null) {
      final userData = response.data['user'] ?? response.data;
      final user = User.fromJson(userData);
      await StorageService.saveUserData(jsonEncode(user.toJson()));
      return user;
    }

    throw Exception(response.error ?? 'Update profil gagal');
  }

  // Change Password
  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    final response = await _api.put(
      ApiConfig.changePassword,
      body: {
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPasswordConfirmation,
      },
    );

    if (response.success) return true;
    throw Exception(response.error ?? 'Gagal mengubah password');
  }

  // Check if logged in
  Future<User?> getCurrentUser() async {
    final token = await StorageService.getToken();
    if (token == null) return null;

    final userData = await StorageService.getUserData();
    if (userData != null) {
      try {
        return User.fromJson(jsonDecode(userData));
      } catch (_) {}
    }

    // Try to fetch from API
    return getProfile();
  }
}
