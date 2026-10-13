class CafeProductModel {
  final String id;
  final String name;
  final String? categoryId;
  final String? categoryName;
  final String description;
  final String ingredients;
  final double price;
  final String imageUrl;
  final bool isActive;
  final bool isFeatured;
  final int sortOrder;

  CafeProductModel({
    required this.id,
    required this.name,
    this.categoryId,
    this.categoryName,
    required this.description,
    required this.ingredients,
    required this.price,
    required this.imageUrl,
    this.isActive = true,
    this.isFeatured = false,
    this.sortOrder = 0,
  });

  String get formattedPrice {
    return '${price.toStringAsFixed(2).replaceAll('.', ',')} ₺';
  }

  factory CafeProductModel.fromMap(Map<String, dynamic> map) {
    return CafeProductModel(
      id: map['id']?.toString() ?? '',
      name: map['name'] ?? '',
      categoryId: map['category_id']?.toString() ?? map['categoryId']?.toString(),
      categoryName: map['category_name'] ?? map['categoryName'] ?? 'Diğer',
      description: map['description'] ?? '',
      ingredients: map['ingredients'] ?? '',
      price: (map['price'] is num) ? (map['price'] as num).toDouble() : double.tryParse(map['price']?.toString() ?? '0') ?? 0.0,
      imageUrl: map['imageUrl'] ?? map['image_url'] ?? '',
      isActive: map['isActive'] ?? map['is_active'] ?? true,
      isFeatured: map['isFeatured'] ?? map['is_featured'] ?? false,
      sortOrder: (map['sortOrder'] ?? map['sort_order'] ?? 0) as int,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'category_id': categoryId,
      'category_name': categoryName,
      'description': description,
      'ingredients': ingredients,
      'price': price,
      'imageUrl': imageUrl,
      'isActive': isActive,
      'isFeatured': isFeatured,
      'sortOrder': sortOrder,
    };
  }
}

class DynamicCafeCategory {
  final String id;
  final String label;

  DynamicCafeCategory({required this.id, required this.label});
}
