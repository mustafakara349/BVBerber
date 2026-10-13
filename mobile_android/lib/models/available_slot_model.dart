/// Müsait randevu saati (slot) modeli
class AvailableSlotModel {
  final String time;
  final bool isAvailable;
  final String? reason;

  const AvailableSlotModel({
    required this.time,
    required this.isAvailable,
    this.reason,
  });

  factory AvailableSlotModel.fromMap(Map<String, dynamic> map) {
    return AvailableSlotModel(
      time: map['time']?.toString() ?? '',
      isAvailable: map['is_available'] == true,
      reason: map['reason']?.toString(),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'time': time,
      'is_available': isAvailable,
      'reason': reason,
    };
  }
}
