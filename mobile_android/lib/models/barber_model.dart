/// Berber veri modeli
class BarberModel {
  final String id;
  final String name;
  final String surname;
  final String phone;
  final String? bio;
  final String? profileImageUrl;
  final List<String> serviceIds;
  final Map<String, Map<String, String>> workingHours;
  final bool isActive;

  const BarberModel({
    required this.id,
    required this.name,
    required this.surname,
    required this.phone,
    this.bio,
    this.profileImageUrl,
    this.serviceIds = const [],
    this.workingHours = const {},
    this.isActive = true,
  });

  factory BarberModel.fromMap(Map<String, dynamic> map, [String id = '']) {
    final rawId = map['id']?.toString() ?? id;
    
    final userMap = map['user'] as Map<String, dynamic>?;
    final fullName = map['full_name'] ?? '';
    String name = map['name'] ?? '';
    String surname = map['surname'] ?? '';
    if (userMap != null) {
      name = userMap['first_name'] ?? '';
      surname = userMap['last_name'] ?? '';
    } else if (fullName.isNotEmpty) {
      final parts = fullName.split(' ');
      if (parts.length > 1) {
        name = parts.sublist(0, parts.length - 1).join(' ');
        surname = parts.last;
      } else {
        name = fullName;
      }
    }

    final phone = userMap?['phone'] ?? map['phone'] ?? '';
    final bio = map['biography'] ?? map['bio'];
    final profileImageUrl = userMap?['profile_photo'] ?? map['profileImageUrl'];
    
    final servicesList = map['services'] as List?;
    final serviceIds = servicesList != null
        ? servicesList.map((s) => (s is Map ? s['id']?.toString() : s.toString())).whereType<String>().toList()
        : List<String>.from(map['serviceIds'] ?? []);

    return BarberModel(
      id: rawId,
      name: name,
      surname: surname,
      phone: phone,
      bio: bio,
      profileImageUrl: profileImageUrl,
      serviceIds: serviceIds,
      workingHours: (map['workingHours'] as Map<String, dynamic>?)?.map(
            (key, value) => MapEntry(
              key,
              Map<String, String>.from(value as Map),
            ),
          ) ??
          {},
      isActive: map['is_active'] ?? map['isActive'] ?? true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'surname': surname,
      'phone': phone,
      'bio': bio,
      'profileImageUrl': profileImageUrl,
      'serviceIds': serviceIds,
      'workingHours': workingHours,
      'is_active': isActive,
      'isActive': isActive,
    };
  }

  BarberModel copyWith({
    String? id,
    String? name,
    String? surname,
    String? phone,
    String? bio,
    String? profileImageUrl,
    List<String>? serviceIds,
    Map<String, Map<String, String>>? workingHours,
    bool? isActive,
  }) {
    return BarberModel(
      id: id ?? this.id,
      name: name ?? this.name,
      surname: surname ?? this.surname,
      phone: phone ?? this.phone,
      bio: bio ?? this.bio,
      profileImageUrl: profileImageUrl ?? this.profileImageUrl,
      serviceIds: serviceIds ?? this.serviceIds,
      workingHours: workingHours ?? this.workingHours,
      isActive: isActive ?? this.isActive,
    );
  }
}
