/// Uygulama genelinde kullanılan sabit değerler
class AppConstants {
  AppConstants._();

  // Uygulama bilgileri
  static const String appName = 'BVBarber';
  static const String appVersion = '1.0.0';

  // Firebase koleksiyon adları
  static const String usersCollection = 'users';
  static const String appointmentsCollection = 'appointments';
  static const String servicesCollection = 'services';
  static const String barbersCollection = 'barbers';

  // SharedPreferences anahtarları
  static const String tokenKey = 'auth_token';
  static const String userIdKey = 'user_id';
  static const String userRoleKey = 'user_role';

  // Randevu slot süreleri (dakika)
  static const int defaultSlotDuration = 30;

  // Sayfalama
  static const int defaultPageSize = 20;
}
