/// Push bildirim servisi
/// 
/// Firebase Cloud Messaging yapılandırması tamamlandığında
/// bu sınıf bildirim yönetimini sağlayacak.
class NotificationService {
  // TODO: FirebaseMessaging instance eklenecek
  // final FirebaseMessaging _messaging = FirebaseMessaging.instance;

  /// Bildirim izinlerini iste
  Future<void> requestPermission() async {
    // No-op
  }

  /// FCM token al
  Future<String?> getToken() async {
    return null;
  }

  /// Foreground bildirim dinleyicisi
  void listenToForegroundMessages() {
    // TODO: FCM entegrasyonu
  }

  /// Bildirim ile uygulama açıldığında
  void handleNotificationTap() {
    // TODO: FCM entegrasyonu
  }
}
