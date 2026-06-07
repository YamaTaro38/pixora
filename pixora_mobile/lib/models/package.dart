// lib/models/package.dart
// Model Package sesuai dengan tabel packages di backend Laravel

class PackageGallery {
  final int id;
  final int packageId;
  final String image;
  final int sortOrder;

  PackageGallery({
    required this.id,
    required this.packageId,
    required this.image,
    this.sortOrder = 0,
  });

  factory PackageGallery.fromJson(Map<String, dynamic> json) {
    return PackageGallery(
      id: json['id'] ?? 0,
      packageId: json['package_id'] ?? 0,
      image: json['image'] ?? '',
      sortOrder: json['sort_order'] ?? 0,
    );
  }

  String get imageUrl {
    if (image.startsWith('http')) return image;
    return image;
  }
}

class Package {
  final int id;
  final String name;
  final String slug;
  final String description;
  final String? image;
  final double price;
  final double? downPayment;
  final int durationHours;
  final int editedPhotos;
  final String locationType;
  final List<String> inclusions;
  final bool isActive;
  final int sortOrder;
  final List<PackageGallery> galleries;
  final String? imageUrl;

  Package({
    required this.id,
    required this.name,
    required this.slug,
    required this.description,
    this.image,
    required this.price,
    this.downPayment,
    required this.durationHours,
    required this.editedPhotos,
    required this.locationType,
    this.inclusions = const [],
    this.isActive = true,
    this.sortOrder = 0,
    this.galleries = const [],
    this.imageUrl,
  });

  factory Package.fromJson(Map<String, dynamic> json) {
    List<String> parseInclusions(dynamic val) {
      if (val == null) return [];
      if (val is List) return val.map((e) => e.toString()).toList();
      if (val is String) {
        try {
          final parsed = val;
          if (parsed.startsWith('[')) {
            return [];
          }
          return [parsed];
        } catch (_) {
          return [val];
        }
      }
      return [];
    }

    return Package(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      slug: json['slug'] ?? '',
      description: json['description'] ?? '',
      image: json['image'],
      price: double.parse(json['price'].toString()),
      downPayment: json['down_payment'] != null
          ? double.parse(json['down_payment'].toString())
          : null,
      durationHours: json['duration_hours'] ?? 0,
      editedPhotos: json['edited_photos'] ?? 0,
      locationType: json['location_type'] ?? 'studio',
      inclusions: parseInclusions(json['inclusions']),
      isActive: json['is_active'] == true || json['is_active'] == 1,
      sortOrder: json['sort_order'] ?? 0,
      galleries: json['galleries'] != null
          ? (json['galleries'] as List)
              .map((g) => PackageGallery.fromJson(g))
              .toList()
          : [],
      imageUrl: json['image_url'],
    );
  }

  String get formattedPrice {
    return 'Rp ${_formatNumber(price.toInt())}';
  }

  String? get formattedDownPayment {
    if (downPayment == null) return null;
    return 'Rp ${_formatNumber(downPayment!.toInt())}';
  }

  String get locationLabel {
    switch (locationType) {
      case 'studio':
        return 'Studio Indoor';
      case 'outdoor':
        return 'Outdoor';
      case 'both':
        return 'Studio & Outdoor';
      default:
        return locationType;
    }
  }

  String get defaultImageUrl {
    if (imageUrl != null && imageUrl!.isNotEmpty) return imageUrl!;
    if (image != null && image!.startsWith('http')) return image!;

    final lowerName = name.toLowerCase();
    if (lowerName.contains('wedding')) {
      return 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600';
    } else if (lowerName.contains('prewedding')) {
      return 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600';
    } else if (lowerName.contains('family')) {
      return 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600';
    }
    return 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600';
  }

  static String _formatNumber(int number) {
    String result = '';
    String numStr = number.toString();
    int count = 0;
    for (int i = numStr.length - 1; i >= 0; i--) {
      count++;
      result = numStr[i] + result;
      if (count % 3 == 0 && i > 0) {
        result = '.$result';
      }
    }
    return result;
  }
}
