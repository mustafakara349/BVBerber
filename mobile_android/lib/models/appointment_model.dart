import 'package:mobile_android/core/enums.dart';

/// Randevu veri modeli
class AppointmentModel {
  final String id;
  final String customerId;
  final String barberId;
  final String serviceId;
  final DateTime dateTime;
  final AppointmentStatus status;
  final String? note;
  final DateTime createdAt;
  final String? serviceName;
  final String? barberName;
  final double? price;
  final bool isReviewed;
  final String? icalUrl;
  final int? rating;

  const AppointmentModel({
    required this.id,
    required this.customerId,
    required this.barberId,
    required this.serviceId,
    required this.dateTime,
    required this.status,
    this.note,
    required this.createdAt,
    this.serviceName,
    this.barberName,
    this.price,
    this.isReviewed = false,
    this.icalUrl,
    this.rating,
  });

  factory AppointmentModel.fromMap(Map<String, dynamic> map, [String id = '']) {
    final rawId = map['id']?.toString() ?? id;
    
    // Get barberId/employeeId
    final employeeMap = map['employee'] as Map<String, dynamic>?;
    final rawBarberId = employeeMap?['id']?.toString() ?? map['barberId'] ?? map['employee_id']?.toString() ?? '';
    
    // Get customerId
    final customerMap = map['customer'] as Map<String, dynamic>?;
    final rawCustomerId = customerMap?['id']?.toString() ?? map['customerId'] ?? map['customer_id']?.toString() ?? '';

    // Get serviceId from appointmentServices / services relationship
    String rawServiceId = map['serviceId'] ?? '';
    final servicesList = map['services'] as List?;
    if (servicesList != null && servicesList.isNotEmpty) {
      final firstServiceItem = servicesList.first;
      if (firstServiceItem is Map) {
        if (firstServiceItem['service'] is Map) {
          rawServiceId = firstServiceItem['service']['id']?.toString() ?? '';
        } else {
          rawServiceId = firstServiceItem['service_id']?.toString() ?? '';
        }
      }
    }

    // Parse status
    AppointmentStatus statusVal = AppointmentStatus.pending;
    final rawStatus = map['status']?.toString();
    if (rawStatus != null) {
      switch (rawStatus) {
        case 'pending':
          statusVal = AppointmentStatus.pending;
          break;
        case 'confirmed':
          statusVal = AppointmentStatus.confirmed;
          break;
        case 'completed':
          statusVal = AppointmentStatus.completed;
          break;
        case 'cancelled':
          statusVal = AppointmentStatus.cancelled;
          break;
        case 'rejected':
          statusVal = AppointmentStatus.rejected;
          break;
        case 'no_show':
        case 'noShow':
          statusVal = AppointmentStatus.noShow;
          break;
        case 'in_progress':
        case 'inProgress':
          statusVal = AppointmentStatus.inProgress;
          break;
        default:
          statusVal = AppointmentStatus.pending;
      }
    }

    // Get serviceName and price
    String? serviceNameVal = map['serviceName'];
    if (servicesList != null && servicesList.isNotEmpty) {
      final List<String> names = [];
      for (final item in servicesList) {
        if (item is Map) {
          final serviceMap = item['service'] as Map?;
          if (serviceMap != null) {
            names.add(serviceMap['name']?.toString() ?? '');
          } else if (item['service_name'] != null) {
            names.add(item['service_name'].toString());
          }
        }
      }
      serviceNameVal = names.where((n) => n.isNotEmpty).join(', ');
    }

    double? priceVal = map['total_price'] != null
        ? double.tryParse(map['total_price'].toString())
        : (map['price'] != null ? double.tryParse(map['price'].toString()) : null);
    if (priceVal == null && servicesList != null && servicesList.isNotEmpty) {
      double total = 0.0;
      for (final item in servicesList) {
        if (item is Map) {
          final serviceMap = item['service'] as Map?;
          double itemPrice = 0.0;
          if (serviceMap != null) {
            itemPrice = double.tryParse(serviceMap['price']?.toString() ?? '') ?? 0.0;
          } else {
            itemPrice = double.tryParse(item['unit_price']?.toString() ?? '') ?? 0.0;
          }
          total += itemPrice;
        }
      }
      priceVal = total;
    }

    // Get barberName
    String? barberNameVal = map['barberName'];
    if (employeeMap != null) {
      final userMap = employeeMap['user'] as Map?;
      if (userMap != null) {
        barberNameVal = '${userMap['first_name'] ?? ''} ${userMap['last_name'] ?? ''}'.trim();
      } else {
        barberNameVal = employeeMap['full_name']?.toString();
      }
    }

    return AppointmentModel(
      id: rawId,
      customerId: rawCustomerId,
      barberId: rawBarberId,
      serviceId: rawServiceId,
      dateTime: DateTime.parse(map['start_at'] ?? map['dateTime'] ?? DateTime.now().toIso8601String()),
      status: statusVal,
      note: map['customer_note'] ?? map['note'],
      createdAt: DateTime.parse(map['created_at'] ?? map['createdAt'] ?? DateTime.now().toIso8601String()),
      serviceName: serviceNameVal,
      barberName: barberNameVal,
      price: priceVal,
      isReviewed: map['isReviewed'] == true || map['is_reviewed'] == true,
      icalUrl: map['icalUrl'] ?? map['ical_url'],
      rating: map['rating'] != null ? int.tryParse(map['rating'].toString()) : null,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'customerId': customerId,
      'customer_id': customerId,
      'barberId': barberId,
      'employee_id': barberId,
      'serviceId': serviceId,
      'dateTime': dateTime.toIso8601String(),
      'start_at': dateTime.toIso8601String(),
      'status': status == AppointmentStatus.noShow
          ? 'no_show'
          : (status == AppointmentStatus.inProgress
              ? 'in_progress'
              : status.name),
      'note': note,
      'customer_note': note,
      'createdAt': createdAt.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'serviceName': serviceName,
      'barberName': barberName,
      'price': price,
      'isReviewed': isReviewed,
      'icalUrl': icalUrl,
      'rating': rating,
    };
  }

  AppointmentModel copyWith({
    String? id,
    String? customerId,
    String? barberId,
    String? serviceId,
    DateTime? dateTime,
    AppointmentStatus? status,
    String? note,
    DateTime? createdAt,
    String? serviceName,
    String? barberName,
    double? price,
    bool? isReviewed,
    String? icalUrl,
    int? rating,
  }) {
    return AppointmentModel(
      id: id ?? this.id,
      customerId: customerId ?? this.customerId,
      barberId: barberId ?? this.barberId,
      serviceId: serviceId ?? this.serviceId,
      dateTime: dateTime ?? this.dateTime,
      status: status ?? this.status,
      note: note ?? this.note,
      createdAt: createdAt ?? this.createdAt,
      serviceName: serviceName ?? this.serviceName,
      barberName: barberName ?? this.barberName,
      price: price ?? this.price,
      isReviewed: isReviewed ?? this.isReviewed,
      icalUrl: icalUrl ?? this.icalUrl,
      rating: rating ?? this.rating,
    );
  }
}
