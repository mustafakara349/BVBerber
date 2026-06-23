import 'package:mobile_android/core/enums.dart';
import 'package:mobile_android/services/api_service.dart';

/// Kullanıcı veri modeli
class UserModel {
  final String id;
  final String name;
  final String surname;
  final String email;
  final String phone;
  final UserRole role;
  final String? profileImageUrl;
  final DateTime createdAt;

  const UserModel({
    required this.id,
    required this.name,
    required this.surname,
    required this.email,
    required this.phone,
    required this.role,
    this.profileImageUrl,
    required this.createdAt,
  });

  /// Firestore veya Laravel API'den gelen Map'i modele dönüştür
  factory UserModel.fromMap(Map<String, dynamic> map, [String id = '']) {
    final rawId = map['id']?.toString() ?? id;
    final roleSlug = map['role'] is Map ? map['role']['slug'] : map['role'];
    
    UserRole userRole = UserRole.customer;
    if (roleSlug == 'barber') {
      userRole = UserRole.barber;
    } else if (roleSlug == 'super_admin' || roleSlug == 'owner' || roleSlug == 'manager' || roleSlug == 'admin') {
      userRole = UserRole.admin;
    }

    return UserModel(
      id: rawId,
      name: map['first_name'] ?? map['name'] ?? '',
      surname: map['last_name'] ?? map['surname'] ?? '',
      email: map['email'] ?? '',
      phone: map['phone'] ?? '',
      role: userRole,
      profileImageUrl: ApiService.normalizeImageUrl(map['profile_photo'] ?? map['profileImageUrl'] ?? map['profile_image_url']),
      createdAt: DateTime.parse(map['created_at'] ?? map['createdAt'] ?? DateTime.now().toIso8601String()),
    );
  }

  /// Modeli Map'e dönüştür
  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'first_name': name,
      'last_name': surname,
      'name': name,
      'surname': surname,
      'email': email,
      'phone': phone,
      'role': role.name,
      'profile_photo': profileImageUrl,
      'profileImageUrl': profileImageUrl,
      'created_at': createdAt.toIso8601String(),
      'createdAt': createdAt.toIso8601String(),
    };
  }

  /// Kopyalama metodu
  UserModel copyWith({
    String? id,
    String? name,
    String? surname,
    String? email,
    String? phone,
    UserRole? role,
    String? profileImageUrl,
    DateTime? createdAt,
  }) {
    return UserModel(
      id: id ?? this.id,
      name: name ?? this.name,
      surname: surname ?? this.surname,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      role: role ?? this.role,
      profileImageUrl: profileImageUrl ?? this.profileImageUrl,
      createdAt: createdAt ?? this.createdAt,
    );
  }
}
