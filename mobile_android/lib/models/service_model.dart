/// Hizmet veri modeli
class ServiceModel {
  final String id;
  final String name;
  final String description;
  final double price;
  final int durationMinutes;
  final String? imageUrl;
  final bool isActive;

  const ServiceModel({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    required this.durationMinutes,
    this.imageUrl,
    this.isActive = true,
  });

  factory ServiceModel.fromMap(Map<String, dynamic> map, [String id = '']) {
    return ServiceModel(
      id: map['id']?.toString() ?? id,
      name: map['name'] ?? '',
      description: map['description'] ?? '',
      price: double.tryParse(map['price']?.toString() ?? '') ?? 0.0,
      durationMinutes: map['duration_minutes'] ?? map['durationMinutes'] ?? 30,
      imageUrl: map['image'] ?? map['imageUrl'],
      isActive: map['is_active'] ?? map['isActive'] ?? true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'duration_minutes': durationMinutes,
      'durationMinutes': durationMinutes,
      'image': imageUrl,
      'imageUrl': imageUrl,
      'is_active': isActive,
      'isActive': isActive,
    };
  }

  ServiceModel copyWith({
    String? id,
    String? name,
    String? description,
    double? price,
    int? durationMinutes,
    String? imageUrl,
    bool? isActive,
  }) {
    return ServiceModel(
      id: id ?? this.id,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      durationMinutes: durationMinutes ?? this.durationMinutes,
      imageUrl: imageUrl ?? this.imageUrl,
      isActive: isActive ?? this.isActive,
    );
  }
}
