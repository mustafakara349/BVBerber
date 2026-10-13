class CampaignModel {
  final String id;
  final String title;
  final String description;
  final String? terms;
  final String type;
  final String triggerType;
  final String rewardType;
  final String? rewardProductId;
  final String? rewardCafeProductId;
  final double minOrderAmount;
  final double? maxDiscountAmount;
  final String targetAudience;
  final String? imagePath;
  final int priority;
  final String discountType;
  final double discountValue;
  final String? startDate;
  final String? endDate;
  final bool isActive;
  final int? perCustomerLimit;
  final List<String> categories;

  CampaignModel({
    required this.id,
    required this.title,
    required this.description,
    this.terms,
    this.type = 'auto_apply',
    this.triggerType = 'all',
    this.rewardType = 'discount',
    this.rewardProductId,
    this.rewardCafeProductId,
    this.minOrderAmount = 0.0,
    this.maxDiscountAmount,
    this.targetAudience = 'all',
    this.imagePath,
    this.priority = 0,
    this.discountType = 'percentage',
    this.discountValue = 0.0,
    this.startDate,
    this.endDate,
    this.isActive = true,
    this.perCustomerLimit,
    this.categories = const [],
  });

  bool get isPercentage => discountType == 'percentage';

  factory CampaignModel.fromMap(Map<String, dynamic> map) {
    return CampaignModel(
      id: map['id']?.toString() ?? '',
      title: map['title'] ?? '',
      description: map['description'] ?? '',
      terms: map['terms'],
      type: map['type'] ?? 'auto_apply',
      triggerType: map['triggerType'] ?? map['trigger_type'] ?? 'all',
      rewardType: map['rewardType'] ?? map['reward_type'] ?? 'discount',
      rewardProductId: map['rewardProductId']?.toString() ?? map['reward_product_id']?.toString(),
      rewardCafeProductId: map['rewardCafeProductId']?.toString() ?? map['reward_cafe_product_id']?.toString(),
      minOrderAmount: (map['minOrderAmount'] is num)
          ? (map['minOrderAmount'] as num).toDouble()
          : (map['min_order_amount'] is num)
              ? (map['min_order_amount'] as num).toDouble()
              : double.tryParse(map['minOrderAmount']?.toString() ?? '0') ?? 0.0,
      maxDiscountAmount: (map['maxDiscountAmount'] is num)
          ? (map['maxDiscountAmount'] as num).toDouble()
          : (map['max_discount_amount'] is num)
              ? (map['max_discount_amount'] as num).toDouble()
              : double.tryParse(map['maxDiscountAmount']?.toString() ?? ''),
      targetAudience: map['targetAudience'] ?? map['target_audience'] ?? 'all',
      imagePath: map['imagePath'] ?? map['image_path'],
      priority: (map['priority'] ?? 0) as int,
      discountType: map['discountType'] ?? map['discount_type'] ?? 'percentage',
      discountValue: (map['discountValue'] is num)
          ? (map['discountValue'] as num).toDouble()
          : (map['discount_value'] is num)
              ? (map['discount_value'] as num).toDouble()
              : double.tryParse(map['discountValue']?.toString() ?? '0') ?? 0.0,
      startDate: map['startDate'] ?? map['start_date'],
      endDate: map['endDate'] ?? map['end_date'],
      isActive: map['isActive'] ?? map['is_active'] ?? true,
      perCustomerLimit: (map['perCustomerLimit'] ?? map['per_customer_limit']) as int?,
      categories: (map['categories'] is List)
          ? List<String>.from(map['categories'].map((e) => e.toString()))
          : const [],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'title': title,
      'description': description,
      'terms': terms,
      'type': type,
      'triggerType': triggerType,
      'rewardType': rewardType,
      'rewardProductId': rewardProductId,
      'rewardCafeProductId': rewardCafeProductId,
      'minOrderAmount': minOrderAmount,
      'maxDiscountAmount': maxDiscountAmount,
      'targetAudience': targetAudience,
      'imagePath': imagePath,
      'priority': priority,
      'discountType': discountType,
      'discountValue': discountValue,
      'startDate': startDate,
      'endDate': endDate,
      'isActive': isActive,
      'perCustomerLimit': perCustomerLimit,
      'categories': categories,
    };
  }
}
