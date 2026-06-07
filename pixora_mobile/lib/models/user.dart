// lib/models/user.dart
// Model User sesuai dengan tabel users di backend Laravel

class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String? avatar;
  final String role;
  final String? googleId;
  final bool isActive;
  final String? token; // Sanctum token

  User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    this.avatar,
    this.role = 'customer',
    this.googleId,
    this.isActive = true,
    this.token,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      phone: json['phone'],
      avatar: json['avatar'],
      role: json['role'] ?? 'customer',
      googleId: json['google_id'],
      isActive: json['is_active'] == true || json['is_active'] == 1,
      token: json['token'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'avatar': avatar,
      'role': role,
      'google_id': googleId,
      'is_active': isActive,
    };
  }

  User copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? avatar,
    String? role,
    String? googleId,
    bool? isActive,
    String? token,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      avatar: avatar ?? this.avatar,
      role: role ?? this.role,
      googleId: googleId ?? this.googleId,
      isActive: isActive ?? this.isActive,
      token: token ?? this.token,
    );
  }

  String get initials {
    final parts = name.split(' ');
    if (parts.length >= 2) {
      return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
    }
    return name.isNotEmpty ? name[0].toUpperCase() : '?';
  }

  bool get isAdmin => role == 'admin';
  bool get isCustomer => role == 'customer';
}
