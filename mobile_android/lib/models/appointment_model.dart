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
      statusVal = AppointmentStatus.values.firstWhere(
        (e) => e.name == rawStatus,
        orElse: () => AppointmentStatus.pending,
      );
    }

    // Get serviceName and price
    String? serviceNameVal = map['serviceName'];
    double? priceVal = map['price'] != null ? (double.tryParse(map['price'].toString()) ?? 0.0) : null;
    if (servicesList != null && servicesList.isNotEmpty) {
      final firstServiceItem = servicesList.first;
      if (firstServiceItem is Map) {
        final serviceMap = firstServiceItem['service'] as Map?;
        if (serviceMap != null) {
          serviceNameVal = serviceMap['name']?.toString();
          priceVal = double.tryParse(serviceMap['price']?.toString() ?? '') ?? 0.0;
        } else {
          serviceNameVal = firstServiceItem['service_name']?.toString();
          priceVal = double.tryParse(firstServiceItem['unit_price']?.toString() ?? '') ?? 0.0;
        }
      }
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
      'status': status.name,
      'note': note,
      'customer_note': note,
      'createdAt': createdAt.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'serviceName': serviceName,
      'barberName': barberName,
      'price': price,
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
    );
  }
}
