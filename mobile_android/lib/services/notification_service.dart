import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:timezone/data/latest.dart' as tz;
import 'package:timezone/timezone.dart' as tz;
import 'package:mobile_android/models/appointment_model.dart';
import 'package:intl/intl.dart';

/// Yerel bildirim yönetimi ve planlama servisi
class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;
  NotificationService._internal();

  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  bool _initialized = false;

  /// Servisi başlat
  Future<void> initialize() async {
    if (_initialized) return;

    // Timezone verilerini başlat
    tz.initializeTimeZones();
    // Varsayılan olarak Türkiye saat dilimini ayarla
    try {
      final location = tz.getLocation('Europe/Istanbul');
      tz.setLocalLocation(location);
    } catch (e) {
      debugPrint('Timezone Europe/Istanbul bulunamadı: $e');
    }

    const AndroidInitializationSettings androidSettings = AndroidInitializationSettings('@mipmap/ic_launcher');
    const DarwinInitializationSettings iosSettings = DarwinInitializationSettings(
      requestAlertPermission: false,
      requestBadgePermission: false,
      requestSoundPermission: false,
    );

    const InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse details) {
        debugPrint('Bildirime tıklandı: ${details.payload}');
      },
    );

    _initialized = true;
    debugPrint('NotificationService başarıyla başlatıldı.');
  }

  /// Bildirim izinlerini iste
  Future<void> requestPermission() async {
    await initialize();

    // Android için izin isteme (Android 13+)
    final androidImplementation = _localNotifications.resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin>();
    if (androidImplementation != null) {
      await androidImplementation.requestNotificationsPermission();
    }

    // iOS için izin isteme
    final iosImplementation = _localNotifications.resolvePlatformSpecificImplementation<
        IOSFlutterLocalNotificationsPlugin>();
    if (iosImplementation != null) {
      await iosImplementation.requestPermissions(
        alert: true,
        badge: true,
        sound: true,
      );
    }
  }

  /// Anlık (hemen) bir yerel bildirim göster
  Future<void> showNotification({
    required int id,
    required String title,
    required String body,
    String? payload,
  }) async {
    await initialize();

    const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
      'bvbarber_channel',
      'B&V Barber Bildirimleri',
      channelDescription: 'Randevu hatırlatıcıları ve kampanya bildirimleri',
      importance: Importance.max,
      priority: Priority.high,
    );

    const NotificationDetails details = NotificationDetails(
      android: androidDetails,
      iOS: DarwinNotificationDetails(),
    );

    await _localNotifications.show(id, title, body, details, payload: payload);
  }

  /// Belirli bir randevu için 2 saat öncesine bildirim planla
  Future<void> scheduleAppointmentReminder(AppointmentModel appointment) async {
    try {
      await initialize();

      final reminderTime = appointment.dateTime.subtract(const Duration(hours: 2));
      final now = DateTime.now();

      if (reminderTime.isBefore(now)) {
        debugPrint('Randevu zamanı geçtiği veya 2 saatten az kaldığı için bildirim planlanmadı: ${appointment.id}');
        return;
      }

      final tz.TZDateTime scheduledTZDate = tz.TZDateTime.from(reminderTime, tz.local);
      final int notificationId = appointment.id.hashCode;

      const AndroidNotificationDetails androidDetails = AndroidNotificationDetails(
        'bvbarber_reminders',
        'B&V Barber Randevu Hatırlatıcıları',
        channelDescription: 'Yaklaşan randevularınız için hatırlatma bildirimleri',
        importance: Importance.max,
        priority: Priority.high,
      );

      const NotificationDetails details = NotificationDetails(
        android: androidDetails,
        iOS: DarwinNotificationDetails(),
      );

      final timeStr = DateFormat('HH:mm').format(appointment.dateTime);
      final barberStr = appointment.barberName ?? 'Berberiniz';

      await _localNotifications.zonedSchedule(
        notificationId,
        'Randevunuz Yaklaşıyor! 📅',
        'Saat $timeStr konumundaki randevunuza ($barberStr) 2 saat kaldı.',
        scheduledTZDate,
        details,
        androidScheduleMode: AndroidScheduleMode.inexactAllowWhileIdle,
        uiLocalNotificationDateInterpretation: UILocalNotificationDateInterpretation.absoluteTime,
        payload: appointment.id,
      );

      debugPrint('Randevu bildirim planlandı: ID $notificationId, Zaman: $scheduledTZDate');
    } catch (e) {
      debugPrint('Randevu bildirim planlama hatası: $e');
    }
  }

  /// Tüm planlanmış bildirimleri iptal et
  Future<void> cancelAllNotifications() async {
    await initialize();
    await _localNotifications.cancelAll();
  }

  /// Belirli bir randevunun bildirimini iptal et
  Future<void> cancelAppointmentReminder(String appointmentId) async {
    await initialize();
    await _localNotifications.cancel(appointmentId.hashCode);
  }
}
