import 'package:mobile_android/services/api_service.dart';

/// Hizmet veri modeli
class ServiceModel {
  final String id;
  final String name;
  final String description;
  final double price;
  final double? discountedPrice;
  final int durationMinutes;
  final String? imageUrl;
  final bool isActive;
  final bool isFeatured;
  final bool isPopular;
  final String categoryName;

  const ServiceModel({
    required this.id,
    required this.name,
    required this.description,
    required this.price,
    this.discountedPrice,
    required this.durationMinutes,
    this.imageUrl,
    this.isActive = true,
    this.isFeatured = false,
    this.isPopular = false,
    this.categoryName = 'Diğer',
  });

  factory ServiceModel.fromMap(Map<String, dynamic> map, [String id = '']) {
    final rawPrice = map['price'] != null ? (double.tryParse(map['price'].toString()) ?? 0.0) : 0.0;
    final rawDiscountedPrice = map['discounted_price'] ?? map['discountedPrice'];
    final parsedDiscountedPrice = rawDiscountedPrice != null ? double.tryParse(rawDiscountedPrice.toString()) : null;

    return ServiceModel(
      id: map['id']?.toString() ?? id,
      name: map['name'] ?? '',
      description: map['description'] ?? '',
      price: rawPrice,
      discountedPrice: parsedDiscountedPrice,
      durationMinutes: map['duration_minutes'] ?? map['durationMinutes'] ?? 30,
      imageUrl: ApiService.normalizeImageUrl(map['image_url'] ?? map['imageUrl'] ?? map['image']),
      isActive: map['is_active'] ?? map['isActive'] ?? true,
      isFeatured: map['is_featured'] ?? map['isFeatured'] ?? false,
      isPopular: map['is_popular'] ?? map['isPopular'] ?? false,
      categoryName: map['category_name'] ?? map['categoryName'] ?? 'Diğer',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'discounted_price': discountedPrice,
      'discountedPrice': discountedPrice,
      'duration_minutes': durationMinutes,
      'durationMinutes': durationMinutes,
      'image': imageUrl,
      'imageUrl': imageUrl,
      'is_active': isActive,
      'isActive': isActive,
      'is_featured': isFeatured,
      'isFeatured': isFeatured,
      'is_popular': isPopular,
      'isPopular': isPopular,
      'category_name': categoryName,
      'categoryName': categoryName,
    };
  }

  ServiceModel copyWith({
    String? id,
    String? name,
    String? description,
    double? price,
    double? discountedPrice,
    int? durationMinutes,
    String? imageUrl,
    bool? isActive,
    bool? isFeatured,
    bool? isPopular,
    String? categoryName,
  }) {
    return ServiceModel(
      id: id ?? this.id,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      discountedPrice: discountedPrice ?? this.discountedPrice,
      durationMinutes: durationMinutes ?? this.durationMinutes,
      imageUrl: imageUrl ?? this.imageUrl,
      isActive: isActive ?? this.isActive,
      isFeatured: isFeatured ?? this.isFeatured,
      isPopular: isPopular ?? this.isPopular,
      categoryName: categoryName ?? this.categoryName,
    );
  }
}
