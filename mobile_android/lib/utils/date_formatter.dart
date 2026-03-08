import 'package:intl/intl.dart';

/// Tarih formatlama yardımcıları
class DateFormatter {
  DateFormatter._();

  /// Tam tarih formatı: 8 Mart 2026
  static String fullDate(DateTime date) {
    return DateFormat('d MMMM y', 'tr_TR').format(date);
  }

  /// Kısa tarih formatı: 08.03.2026
  static String shortDate(DateTime date) {
    return DateFormat('dd.MM.yyyy').format(date);
  }

  /// Saat formatı: 14:30
  static String time(DateTime date) {
    return DateFormat('HH:mm').format(date);
  }

  /// Tarih ve saat: 08.03.2026 14:30
  static String dateTime(DateTime date) {
    return DateFormat('dd.MM.yyyy HH:mm').format(date);
  }

  /// Gün adı: Pazartesi
  static String dayName(DateTime date) {
    return DateFormat('EEEE', 'tr_TR').format(date);
  }

  /// Göreceli tarih: Bugün, Yarın, veya tarih
  static String relative(DateTime date) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final target = DateTime(date.year, date.month, date.day);
    final diff = target.difference(today).inDays;

    if (diff == 0) return 'Bugün';
    if (diff == 1) return 'Yarın';
    if (diff == -1) return 'Dün';
    return fullDate(date);
  }
}
