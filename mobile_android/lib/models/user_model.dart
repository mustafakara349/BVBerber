import 'package:mobile_android/core/enums.dart';

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

  /// Firestore'dan gelen Map'i modele dönüştür
  factory UserModel.fromMap(Map<String, dynamic> map, String id) {
    return UserModel(
      id: id,
      name: map['name'] ?? '',
      surname: map['surname'] ?? '',
      email: map['email'] ?? '',
      phone: map['phone'] ?? '',
      role: UserRole.values.firstWhere(
        (e) => e.name == map['role'],
        orElse: () => UserRole.customer,
      ),
      profileImageUrl: map['profileImageUrl'],
      createdAt: DateTime.parse(map['createdAt'] ?? DateTime.now().toIso8601String()),
    );
  }

  /// Modeli Firestore'a yazmak için Map'e dönüştür
  Map<String, dynamic> toMap() {
    return {
      'name': name,
      'surname': surname,
      'email': email,
      'phone': phone,
      'role': role.name,
      'profileImageUrl': profileImageUrl,
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
