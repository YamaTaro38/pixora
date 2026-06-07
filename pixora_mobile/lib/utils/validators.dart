// lib/utils/validators.dart
// Utility untuk validasi form

class Validators {
  static String? email(String? value) {
    if (value == null || value.isEmpty) {
      return 'Email tidak boleh kosong';
    }
    final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    if (!emailRegex.hasMatch(value)) {
      return 'Format email tidak valid';
    }
    return null;
  }

  static String? password(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password tidak boleh kosong';
    }
    if (value.length < 6) {
      return 'Password minimal 6 karakter';
    }
    return null;
  }

  static String? confirmPassword(String? value, String password) {
    if (value == null || value.isEmpty) {
      return 'Konfirmasi password tidak boleh kosong';
    }
    if (value != password) {
      return 'Password tidak cocok';
    }
    return null;
  }

  static String? name(String? value) {
    if (value == null || value.isEmpty) {
      return 'Nama tidak boleh kosong';
    }
    if (value.length < 2) {
      return 'Nama minimal 2 karakter';
    }
    if (value.length > 100) {
      return 'Nama maksimal 100 karakter';
    }
    return null;
  }

  static String? phone(String? value) {
    if (value == null || value.isEmpty) {
      return null; // Optional
    }
    final phoneRegex = RegExp(r'^(\+62|62|0)[0-9]{8,13}$');
    if (!phoneRegex.hasMatch(value.replaceAll(' ', '').replaceAll('-', ''))) {
      return 'Format nomor telepon tidak valid';
    }
    return null;
  }

  static String? required(String? value, {String field = 'Field'}) {
    if (value == null || value.trim().isEmpty) {
      return '$field tidak boleh kosong';
    }
    return null;
  }
}
