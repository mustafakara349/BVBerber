enum StockStatus {
  ok,
  low,
  empty;

  String get label {
    switch (this) {
      case StockStatus.ok:
        return 'Stokta';
      case StockStatus.low:
        return 'Az Kaldı';
      case StockStatus.empty:
        return 'Tükendi';
    }
  }
}

class SaleProductModel {
  final String id;
  final String name;
  final String? categoryId;
  final String? categoryName;
  final String description;
  final String sku;
  final String barcode;
  final double sellPrice;
  final double purchasePrice;
  final int stockQuantity;
  final int criticalStock;
  final String imageUrl;
  final bool isActive;
  final bool isInStock;

  SaleProductModel({
    required this.id,
    required this.name,
    this.categoryId,
    this.categoryName,
    required this.description,
    this.sku = '',
    this.barcode = '',
    required this.sellPrice,
    this.purchasePrice = 0.0,
    required this.stockQuantity,
    this.criticalStock = 5,
    required this.imageUrl,
    this.isActive = true,
    required this.isInStock,
  });

  String get formattedPrice {
    return '${sellPrice.toStringAsFixed(2).replaceAll('.', ',')} ₺';
  }

  StockStatus get stockStatus {
    if (stockQuantity <= 0) {
      return StockStatus.empty;
    } else if (stockQuantity <= criticalStock) {
      return StockStatus.low;
    } else {
      return StockStatus.ok;
    }
  }

  factory SaleProductModel.fromMap(Map<String, dynamic> map) {
    final stock = (map['stockQuantity'] ?? map['stock_quantity'] ?? 0) as int;
    return SaleProductModel(
      id: map['id']?.toString() ?? '',
      name: map['name'] ?? '',
      categoryId: map['category_id']?.toString() ?? map['categoryId']?.toString(),
      categoryName: map['category_name'] ?? map['categoryName'] ?? 'Diğer',
      description: map['description'] ?? '',
      sku: map['sku'] ?? '',
      barcode: map['barcode'] ?? '',
      sellPrice: (map['sellPrice'] is num)
          ? (map['sellPrice'] as num).toDouble()
          : (map['sell_price'] is num)
              ? (map['sell_price'] as num).toDouble()
              : double.tryParse(map['sellPrice']?.toString() ?? map['sell_price']?.toString() ?? '0') ?? 0.0,
      purchasePrice: (map['purchasePrice'] is num)
          ? (map['purchasePrice'] as num).toDouble()
          : (map['purchase_price'] is num)
              ? (map['purchase_price'] as num).toDouble()
              : double.tryParse(map['purchasePrice']?.toString() ?? map['purchase_price']?.toString() ?? '0') ?? 0.0,
      stockQuantity: stock,
      criticalStock: (map['criticalStock'] ?? map['critical_stock'] ?? 5) as int,
      imageUrl: map['imageUrl'] ?? map['image_url'] ?? '',
      isActive: map['isActive'] ?? map['is_active'] ?? true,
      isInStock: map['isInStock'] ?? (stock > 0),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'category_id': categoryId,
      'category_name': categoryName,
      'description': description,
      'sku': sku,
      'barcode': barcode,
      'sellPrice': sellPrice,
      'purchasePrice': purchasePrice,
      'stockQuantity': stockQuantity,
      'criticalStock': criticalStock,
      'imageUrl': imageUrl,
      'isActive': isActive,
      'isInStock': isInStock,
    };
  }
}

class DynamicSaleCategory {
  final String id;
  final String label;

  DynamicSaleCategory({required this.id, required this.label});
}
