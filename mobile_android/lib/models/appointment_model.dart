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

  const AppointmentModel({
    required this.id,
    required this.customerId,
    required this.barberId,
    required this.serviceId,
    required this.dateTime,
    required this.status,
    this.note,
    required this.createdAt,
  });

  factory AppointmentModel.fromMap(Map<String, dynamic> map, String id) {
    return AppointmentModel(
      id: id,
      customerId: map['customerId'] ?? '',
      barberId: map['barberId'] ?? '',
      serviceId: map['serviceId'] ?? '',
      dateTime: DateTime.parse(map['dateTime']),
      status: AppointmentStatus.values.firstWhere(
        (e) => e.name == map['status'],
        orElse: () => AppointmentStatus.pending,
      ),
      note: map['note'],
      createdAt: DateTime.parse(map['createdAt'] ?? DateTime.now().toIso8601String()),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'customerId': customerId,
      'barberId': barberId,
      'serviceId': serviceId,
      'dateTime': dateTime.toIso8601String(),
      'status': status.name,
      'note': note,
      'createdAt': createdAt.toIso8601String(),
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
    );
  }
}
