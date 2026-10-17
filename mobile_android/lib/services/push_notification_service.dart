import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:mobile_android/firebase_options.dart';
import 'package:mobile_android/main.dart';
import 'package:mobile_android/routes/app_routes.dart';
import 'package:mobile_android/services/api_service.dart';

/// Uygulama tamamen kapalıyken (killed/terminated) FCM bildirimini karşılayan üst düzey işleyici.
/// NOT: Bu fonksiyon mutlaka class dışında ve @pragma('vm:entry-point') ile tanımlanmalıdır.
@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    if (Firebase.apps.isEmpty) {
      await Firebase.initializeApp(
        options: DefaultFirebaseOptions.currentPlatform,
      );
    }
  } catch (e) {
    debugPrint('Firebase background init warning: $e');
  }
  debugPrint('Arka planda bildirim alındı: ${message.messageId} | Veri: ${message.data}');
}

/// Firebase Cloud Messaging (FCM) ve anlık bildirim yöneticisi
class PushNotificationService {
  static final PushNotificationService _instance = PushNotificationService._internal();
  factory PushNotificationService() => _instance;
  PushNotificationService._internal();

  /// Güvenli FirebaseMessaging erişimi
  FirebaseMessaging? get _fcm {
    try {
      if (Firebase.apps.isNotEmpty) {
        return FirebaseMessaging.instance;
      }
    } catch (e) {
      debugPrint('FirebaseMessaging erişim uyarısı: $e');
    }
    return null;
  }

  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  bool _isInitialized = false;

  /// Android Yüksek Öncelikli Bildirim Kanalı
  static const AndroidNotificationChannel highPriorityChannel = AndroidNotificationChannel(
    'bvbarber_channel',
    'B&V Barber Bildirimleri',
    description: 'Randevu hatırlatmaları, duyurular ve özel kampanya bildirimleri.',
    importance: Importance.max,
    playSound: true,
    enableVibration: true,
  );

  /// Bildirim altyapısını başlat
  Future<void> initialize() async {
    if (_isInitialized) return;

    // Firebase başlatılmamışsa başlat
    try {
      if (Firebase.apps.isEmpty) {
        await Firebase.initializeApp(
          options: DefaultFirebaseOptions.currentPlatform,
        );
      }
    } catch (e) {
      debugPrint('Firebase başlatma uyarısı: $e');
    }

    // 1. Android & iOS bildirim izinlerini iste
    await _requestPermissions();

    // 2. Android bildirim kanalını işletim sistemine kaydet
    final androidImplementation = _localNotifications.resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin>();
    if (androidImplementation != null) {
      await androidImplementation.createNotificationChannel(highPriorityChannel);
    }

    // 3. Yerel bildirim eklentisini hazırla
    const AndroidInitializationSettings androidSettings =
        AndroidInitializationSettings('@mipmap/ic_launcher');
    const DarwinInitializationSettings iosSettings = DarwinInitializationSettings();

    const InitializationSettings initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _localNotifications.initialize(
      initSettings,
      onDidReceiveNotificationResponse: (NotificationResponse response) {
        if (response.payload != null && response.payload!.isNotEmpty) {
          try {
            final data = jsonDecode(response.payload!) as Map<String, dynamic>;
            handleNotificationRouting(data);
          } catch (_) {
            navigatorKey.currentState?.pushNamed(AppRoutes.main);
          }
        }
      },
    );

    final fcm = _fcm;
    if (fcm != null) {
      // 4. Uygulama ÖN PLANDAYKEN gelen bildirimleri ekranda Heads-up olarak göster
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        debugPrint('Ön planda bildirim alındı: ${message.notification?.title}');
        final notification = message.notification;
        if (notification != null) {
          _localNotifications.show(
            notification.hashCode,
            notification.title,
            notification.body,
            NotificationDetails(
              android: AndroidNotificationDetails(
                highPriorityChannel.id,
                highPriorityChannel.name,
                channelDescription: highPriorityChannel.description,
                icon: '@mipmap/ic_launcher',
                importance: Importance.max,
                priority: Priority.high,
                playSound: true,
                enableVibration: true,
              ),
              iOS: const DarwinNotificationDetails(
                presentAlert: true,
                presentBadge: true,
                presentSound: true,
              ),
            ),
            payload: jsonEncode(message.data),
          );
        }
      });

      // 5. Uygulama ARKA PLANDAYKEN bildirime tıklandığında sayfaya yönlendir
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        debugPrint('Arka plan bildirimine tıklandı: ${message.data}');
        handleNotificationRouting(message.data);
      });

      // 6. Uygulama TAMAMEN KAPALIYKEN bildirime tıklanarak açıldıysa kontrol et
      try {
        final initialMessage = await fcm.getInitialMessage();
        if (initialMessage != null) {
          debugPrint('Kapalıyken tıklanan bildirimle uygulama açıldı: ${initialMessage.data}');
          Future.delayed(const Duration(milliseconds: 500), () {
            handleNotificationRouting(initialMessage.data);
          });
        }
      } catch (e) {
        debugPrint('getInitialMessage hatası: $e');
      }

      // 7. Cihaz FCM Token'ını al ve backend'e kaydet
      await syncTokenWithBackend();

      // 8. Token yenilendiğinde backend'e otomatik güncelle
      fcm.onTokenRefresh.listen((newToken) {
        debugPrint('FCM Token yenilendi: $newToken');
        ApiService.saveDeviceToken(newToken);
      });
    } else {
      debugPrint('Firebase hazır olmadığı için FCM dinleyicileri atlandı, sadece yerel bildirimler aktif.');
    }

    _isInitialized = true;
    debugPrint('PushNotificationService başarıyla başlatıldı.');
  }

  /// Bildirim izinlerini iste (Android 13+ ve iOS)
  Future<void> _requestPermissions() async {
    final fcm = _fcm;
    if (fcm != null) {
      try {
        final settings = await fcm.requestPermission(
          alert: true,
          announcement: false,
          badge: true,
          carPlay: false,
          criticalAlert: false,
          provisional: false,
          sound: true,
        );
        debugPrint('FCM Bildirim İzin Durumu: ${settings.authorizationStatus}');
      } catch (e) {
        debugPrint('FCM izin isteme uyarısı: $e');
      }
    }

    final androidImplementation = _localNotifications.resolvePlatformSpecificImplementation<
        AndroidFlutterLocalNotificationsPlugin>();
    if (androidImplementation != null) {
      await androidImplementation.requestNotificationsPermission();
    }
  }

  /// Cihazın FCM Token'ını sunucuya senkronize et
  Future<void> syncTokenWithBackend() async {
    try {
      if (Firebase.apps.isEmpty) {
        await Firebase.initializeApp(
          options: DefaultFirebaseOptions.currentPlatform,
        );
      }
      final fcm = _fcm;
      if (fcm == null) {
        debugPrint('Firebase hazır değil, token senkronizasyonu ertelendi.');
        return;
      }
      final token = await fcm.getToken();
      if (token != null && token.isNotEmpty) {
        debugPrint('Aktif FCM Token: $token');
        await ApiService.saveDeviceToken(token);
      }
    } catch (e) {
      debugPrint('FCM Token alınamadı / kaydedilemedi: $e');
    }
  }

  /// Bildirim payload verisine göre ilgili ekrana yönlendirme yap
  void handleNotificationRouting(Map<String, dynamic> data) {
    final action = data['action']?.toString();
    debugPrint('Bildirim yönlendirme eylemi: $action');

    switch (action) {
      case 'open_review':
      case 'open_appointment':
        navigatorKey.currentState?.pushNamed(AppRoutes.main);
        break;
      case 'open_campaign':
      case 'campaign':
        navigatorKey.currentState?.pushNamed(AppRoutes.campaigns);
        break;
      case 'open_notifications':
        navigatorKey.currentState?.pushNamed(AppRoutes.notifications);
        break;
      default:
        navigatorKey.currentState?.pushNamed(AppRoutes.main);
        break;
    }
  }
}
