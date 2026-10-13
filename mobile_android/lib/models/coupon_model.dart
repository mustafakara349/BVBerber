class CouponModel {
  final String id;
  final String title;
  final String description;
  final String code;
  final String discountType; // "percentage" or "fixed"
  final double discountValue;
  final String? expiresAt;
  final int usageLimit;
  final int usedCount;
  final int perCustomerLimit;
  final int remainingUsage;
  final bool isValid;

  CouponModel({
    required this.id,
    required this.title,
    required this.description,
    required this.code,
    required this.discountType,
    required this.discountValue,
    this.expiresAt,
    this.usageLimit = 0,
    this.usedCount = 0,
    this.perCustomerLimit = 1,
    this.remainingUsage = 0,
    this.isValid = true,
  });

  bool get isPercentage => discountType == 'percentage';

  factory CouponModel.fromMap(Map<String, dynamic> map) {
    return CouponModel(
      id: map['id']?.toString() ?? '',
      title: map['title'] ?? 'İndirim Kuponu',
      description: map['description'] ?? '',
      code: map['code'] ?? '',
      discountType: map['discountType'] ?? map['discount_type'] ?? 'percentage',
      discountValue: (map['discountValue'] is num)
          ? (map['discountValue'] as num).toDouble()
          : (map['discount_value'] is num)
              ? (map['discount_value'] as num).toDouble()
              : double.tryParse(map['discountValue']?.toString() ?? '0') ?? 0.0,
      expiresAt: map['expiresAt'] ?? map['expires_at'],
      usageLimit: (map['usageLimit'] ?? map['usage_limit'] ?? 0) as int,
      usedCount: (map['usedCount'] ?? map['used_count'] ?? 0) as int,
      perCustomerLimit: (map['perCustomerLimit'] ?? map['per_customer_limit'] ?? 1) as int,
      remainingUsage: (map['remainingUsage'] ?? map['remaining_usage'] ?? 0) as int,
      isValid: map['isValid'] ?? map['is_valid'] ?? true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'code': code,
      'discountType': discountType,
      'discountValue': discountValue,
      'expiresAt': expiresAt,
      'usageLimit': usageLimit,
      'usedCount': usedCount,
      'perCustomerLimit': perCustomerLimit,
      'remainingUsage': remainingUsage,
      'isValid': isValid,
    };
  }
}
