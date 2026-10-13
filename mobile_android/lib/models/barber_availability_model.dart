class BarberAvailabilityModel {
  final String? id;
  final String barberId;
  final String date; // "YYYY-MM-DD"
  final bool fullDayOff;
  final List<String> blockedSlots;
  final String? reason;

  BarberAvailabilityModel({
    this.id,
    required this.barberId,
    required this.date,
    required this.fullDayOff,
    this.blockedSlots = const [],
    this.reason,
  });

  factory BarberAvailabilityModel.fromMap(Map<String, dynamic> map) {
    return BarberAvailabilityModel(
      id: map['id']?.toString(),
      barberId: map['barberId']?.toString() ?? map['barber_id']?.toString() ?? '',
      date: map['date'] ?? '',
      fullDayOff: map['fullDayOff'] ?? map['full_day_off'] ?? false,
      blockedSlots: (map['blockedSlots'] is List)
          ? List<String>.from(map['blockedSlots'].map((e) => e.toString()))
          : (map['blocked_slots'] is List)
              ? List<String>.from(map['blocked_slots'].map((e) => e.toString()))
              : const [],
      reason: map['reason'],
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'barberId': barberId,
      'date': date,
      'fullDayOff': fullDayOff,
      'blockedSlots': blockedSlots,
      'reason': reason,
    };
  }
}
