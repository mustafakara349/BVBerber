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

  factory BarberModel.fromMap(Map<String, dynamic> map, String id) {
    return BarberModel(
      id: id,
      name: map['name'] ?? '',
      surname: map['surname'] ?? '',
      phone: map['phone'] ?? '',
      bio: map['bio'],
      profileImageUrl: map['profileImageUrl'],
      serviceIds: List<String>.from(map['serviceIds'] ?? []),
      workingHours: (map['workingHours'] as Map<String, dynamic>?)?.map(
            (key, value) => MapEntry(
              key,
              Map<String, String>.from(value as Map),
            ),
          ) ??
          {},
      isActive: map['isActive'] ?? true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'name': name,
      'surname': surname,
      'phone': phone,
      'bio': bio,
      'profileImageUrl': profileImageUrl,
      'serviceIds': serviceIds,
      'workingHours': workingHours,
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
