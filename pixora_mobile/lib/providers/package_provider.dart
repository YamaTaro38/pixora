// lib/providers/package_provider.dart
// Provider untuk state management paket fotografi

import 'package:flutter/material.dart';
import '../models/package.dart';
import '../services/api_service.dart';
import '../config/api_config.dart';

class PackageProvider extends ChangeNotifier {
  final ApiService _api = ApiService();

  List<Package> _packages = [];
  Package? _selectedPackage;
  bool _isLoading = false;
  String? _error;

  List<Package> get packages => _packages;
  Package? get selectedPackage => _selectedPackage;
  bool get isLoading => _isLoading;
  String? get error => _error;

  // Fetch semua paket
  Future<void> fetchPackages() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get(ApiConfig.packages);

      if (response.success && response.data != null) {
        final List<dynamic> data = response.data is List
            ? response.data
            : (response.data['data'] ?? []);
        _packages = data.map((json) => Package.fromJson(json)).toList();
      } else {
        _error = response.error;
        // Fallback demo data
        _packages = _getDemoPackages();
      }
    } catch (e) {
      _error = e.toString();
      _packages = _getDemoPackages();
    }

    _isLoading = false;
    notifyListeners();
  }

  // Fetch detail paket
  Future<void> fetchPackageDetail(String slug) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _api.get(ApiConfig.packageDetail(slug));

      if (response.success && response.data != null) {
        final data = response.data is Map<String, dynamic>
            ? response.data['data'] ?? response.data
            : response.data;
        _selectedPackage = Package.fromJson(data);
      } else {
        _error = response.error;
      }
    } catch (e) {
      _error = e.toString();
    }

    _isLoading = false;
    notifyListeners();
  }

  void selectPackage(Package package) {
    _selectedPackage = package;
    notifyListeners();
  }

  void clearSelection() {
    _selectedPackage = null;
    notifyListeners();
  }

  // Demo data fallback
  List<Package> _getDemoPackages() {
    return [
      Package(
        id: 1,
        name: 'Paket Wedding',
        slug: 'paket-wedding',
        description:
            'Paket fotografi wedding lengkap dengan makeup artist dan dekorasi indoor.',
        price: 5000000,
        downPayment: 2000000,
        durationHours: 4,
        editedPhotos: 100,
        locationType: 'both',
        inclusions: [
          '1 Fotografer professional',
          '1 Asisten fotografer',
          'Soft file via Google Drive',
          '100 foto hasil edit',
          'Video highlight 3 menit',
          'Free 1 cetak canvas 40x60',
        ],
        imageUrl: 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600',
      ),
      Package(
        id: 2,
        name: 'Paket Prewedding',
        slug: 'paket-prewedding',
        description:
            'Paket foto prewedding outdoor/indoor dengan konsep kreatif dan modern.',
        price: 3500000,
        downPayment: 1500000,
        durationHours: 3,
        editedPhotos: 50,
        locationType: 'outdoor',
        inclusions: [
          '1 Fotografer professional',
          '1 Asisten fotografer',
          'Soft file via Google Drive',
          '50 foto hasil edit',
        ],
        imageUrl: 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600',
      ),
      Package(
        id: 3,
        name: 'Paket Family',
        slug: 'paket-family',
        description:
            'Abadikan momen keluarga bahagia Anda dengan sesi foto keluarga profesional.',
        price: 1500000,
        downPayment: 500000,
        durationHours: 2,
        editedPhotos: 30,
        locationType: 'studio',
        inclusions: [
          '1 Fotografer professional',
          'Soft file via Google Drive',
          '30 foto hasil edit',
        ],
        imageUrl: 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600',
      ),
    ];
  }
}
