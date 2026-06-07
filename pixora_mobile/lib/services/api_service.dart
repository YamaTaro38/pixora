// lib/services/api_service.dart
// HTTP Client service untuk komunikasi dengan backend Laravel

import 'dart:convert';
import 'dart:async';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import 'storage_service.dart';

class ApiResponse {
  final int statusCode;
  final dynamic data;
  final String? error;
  final bool success;

  ApiResponse({
    required this.statusCode,
    this.data,
    this.error,
    required this.success,
  });
}

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  String get baseUrl => ApiConfig.baseUrl;

  // Timeout 15 detik untuk semua request
  static const Duration _timeout = Duration(seconds: 15);

  Future<Map<String, String>> _headers() async {
    final token = await StorageService.getToken();
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  // GET request
  Future<ApiResponse> get(String endpoint,
      {Map<String, String>? queryParams}) async {
    try {
      var uri = Uri.parse('$baseUrl$endpoint');
      if (queryParams != null && queryParams.isNotEmpty) {
        uri = uri.replace(queryParameters: queryParams);
      }

      final response = await http
          .get(uri, headers: await _headers())
          .timeout(_timeout);
      return _handleResponse(response);
    } on TimeoutException {
      return ApiResponse(
        statusCode: 0,
        error: 'Request timeout. Pastikan server berjalan dan koneksi stabil.',
        success: false,
      );
    } catch (e) {
      return _handleError(e);
    }
  }

  // POST request
  Future<ApiResponse> post(String endpoint,
      {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final response = await http
          .post(
            uri,
            headers: await _headers(),
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(_timeout);
      return _handleResponse(response);
    } on TimeoutException {
      return ApiResponse(
        statusCode: 0,
        error: 'Request timeout. Pastikan server berjalan dan koneksi stabil.',
        success: false,
      );
    } catch (e) {
      return _handleError(e);
    }
  }

  // PUT request
  Future<ApiResponse> put(String endpoint,
      {Map<String, dynamic>? body}) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final response = await http
          .put(
            uri,
            headers: await _headers(),
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(_timeout);
      return _handleResponse(response);
    } on TimeoutException {
      return ApiResponse(
        statusCode: 0,
        error: 'Request timeout. Pastikan server berjalan dan koneksi stabil.',
        success: false,
      );
    } catch (e) {
      return _handleError(e);
    }
  }

  // MULTIPART POST request
  Future<ApiResponse> multipartPost(String endpoint,
      {Map<String, String>? fields, String? fileField, String? filePath}) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final request = http.MultipartRequest('POST', uri);
      
      final headers = await _headers();
      headers.remove('Content-Type'); // Let http client set this for multipart
      request.headers.addAll(headers);
      
      if (fields != null) {
        request.fields.addAll(fields);
      }
      
      if (fileField != null && filePath != null) {
        request.files.add(await http.MultipartFile.fromPath(fileField, filePath));
      }
      
      final streamedResponse = await request.send().timeout(_timeout);
      final response = await http.Response.fromStream(streamedResponse);
      return _handleResponse(response);
    } on TimeoutException {
      return ApiResponse(
        statusCode: 0,
        error: 'Request timeout. Pastikan server berjalan dan koneksi stabil.',
        success: false,
      );
    } catch (e) {
      return _handleError(e);
    }
  }

  // DELETE request
  Future<ApiResponse> delete(String endpoint) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final response = await http
          .delete(uri, headers: await _headers())
          .timeout(_timeout);
      return _handleResponse(response);
    } on TimeoutException {
      return ApiResponse(
        statusCode: 0,
        error: 'Request timeout. Pastikan server berjalan dan koneksi stabil.',
        success: false,
      );
    } catch (e) {
      return _handleError(e);
    }
  }

  // Health check — test koneksi ke server
  Future<bool> healthCheck() async {
    try {
      final uri = Uri.parse('$baseUrl${ApiConfig.ping}');
      final response = await http
          .get(uri, headers: {'Accept': 'application/json'})
          .timeout(const Duration(seconds: 5));
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  ApiResponse _handleResponse(http.Response response) {
    dynamic data;
    try {
      data = jsonDecode(response.body);
    } catch (_) {
      data = response.body;
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return ApiResponse(
        statusCode: response.statusCode,
        data: data,
        success: true,
      );
    } else {
      String errorMsg = 'Terjadi kesalahan';

      if (response.statusCode == 401) {
        errorMsg = 'Sesi habis. Silakan login kembali.';
      } else if (response.statusCode == 403) {
        errorMsg = 'Akses ditolak.';
      } else if (response.statusCode == 404) {
        errorMsg = 'Data tidak ditemukan.';
      } else if (response.statusCode == 419) {
        errorMsg = 'Token CSRF expired. Coba lagi.';
      } else if (response.statusCode == 422) {
        // Validation errors
        if (data is Map) {
          if (data['errors'] != null && data['errors'] is Map) {
            final errors = data['errors'] as Map;
            final firstError = errors.values.first;
            if (firstError is List && firstError.isNotEmpty) {
              errorMsg = firstError.first.toString();
            }
          } else {
            errorMsg = data['message'] ?? 'Validasi gagal';
          }
        }
      } else if (response.statusCode >= 500) {
        errorMsg = 'Server error. Coba lagi nanti.';
      } else if (data is Map) {
        errorMsg = data['message'] ??
            data['error'] ??
            'Terjadi kesalahan (${response.statusCode})';
      }

      return ApiResponse(
        statusCode: response.statusCode,
        data: data,
        error: errorMsg,
        success: false,
      );
    }
  }

  ApiResponse _handleError(dynamic e) {
    String errorMsg;

    if (e.toString().contains('SocketException') ||
        e.toString().contains('Connection refused')) {
      errorMsg =
          'Tidak bisa terhubung ke server. Pastikan:\n'
          '1. Server Laravel berjalan (php artisan serve)\n'
          '2. URL API benar di api_config.dart\n'
          '3. Perangkat terhubung ke jaringan yang sama';
    } else if (e.toString().contains('HandshakeException')) {
      errorMsg = 'SSL error. Gunakan http:// bukan https:// untuk development.';
    } else {
      errorMsg = 'Koneksi gagal: ${e.toString()}';
    }

    return ApiResponse(
      statusCode: 0,
      error: errorMsg,
      success: false,
    );
  }
}
