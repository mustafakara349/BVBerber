class NotificationModel {
  final String id;
  final String icon;
  final String title;
  final String message;
  final String userId;
  final DateTime createdAt;

  NotificationModel({
    required this.id,
    required this.icon,
    required this.title,
    required this.message,
    required this.userId,
    required this.createdAt,
  });

  factory NotificationModel.fromMap(Map<String, dynamic> map) {
    return NotificationModel(
      id: map['id']?.toString() ?? '',
      icon: map['icon'] ?? 'bell',
      title: map['title'] ?? '',
      message: map['message'] ?? '',
      userId: map['userId']?.toString() ?? '',
      createdAt: DateTime.parse(map['createdAt'] ?? DateTime.now().toIso8601String()),
    );
  }
}
