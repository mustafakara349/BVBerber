import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_android/models/available_slot_model.dart';
import 'package:mobile_android/models/review_result_model.dart';

class ApiService {
  // Varsayılan API URL tanımları (Geliştirme vs Canlı/Production)
  static const String _defaultDevBaseUrl = 'http://10.0.2.2:8000/api/v1';
  static const String _defaultProdBaseUrl = 'https://api.bvbarber.com/api/v1';

  /// Derleme moduna ve yapılandırmaya göre API temel adresini belirler.
  /// Production / Release modunda HTTPS zorunludur; HTTP adresi verilirse hata fırlatır.
  static String resolveBaseUrl({bool? isRelease, String? customUrl}) {
    final release = isRelease ?? kReleaseMode;
    const envUrl = String.fromEnvironment('API_BASE_URL');
    final configured = customUrl ?? (envUrl.isNotEmpty ? envUrl : null);
    final candidate = configured ?? (release ? _defaultProdBaseUrl : _defaultDevBaseUrl);

    if (release && !candidate.toLowerCase().startsWith('https://')) {
      throw StateError(
        'Insecure HTTP communication is blocked in production release builds. Base URL must enforce HTTPS: $candidate',
      );
    }
    return candidate;
  }

  /// Aktif API URL adresi
  static String get baseUrl => resolveBaseUrl();

  /// Verilen URL'in release ortamında HTTPS olduğunu doğrular
  static String enforceHttps(String url, {bool? isRelease}) {
    final release = isRelease ?? kReleaseMode;
    if (release && url.toLowerCase().startsWith('http://')) {
      throw StateError(
        'Security Violation: Cleartext HTTP traffic is blocked in production: $url',
      );
    }
    return url;
  }

  // Android Keystore destekli güvenli depolama yapılandırması (RSA OAEP + AES GCM)
  static const AndroidOptions _androidOptions = AndroidOptions(
    resetOnError: true,
  );

  static const FlutterSecureStorage _defaultSecureStorage = FlutterSecureStorage(
    aOptions: _androidOptions,
  );

  @visibleForTesting
  static FlutterSecureStorage secureStorage = _defaultSecureStorage;

  static const String _authTokenKey = 'auth_token';

  /// Token'ı Android Keystore destekli güvenli depolamadan getirir.
  /// Eski SharedPreferences'ta token kalmışsa güvenli alana taşır ve eski kopyayı siler.
  static Future<String?> getAuthToken() async {
    try {
      final token = await secureStorage.read(key: _authTokenKey);
      if (token != null && token.isNotEmpty) {
        return token;
      }
    } catch (e) {
      debugPrint('SecureStorage okuma hatası: $e');
    }

    // Geriye dönük uyumluluk: SharedPreferences'taki eski token'ı Keystore'a migrate et
    try {
      final prefs = await SharedPreferences.getInstance();
      final legacyToken = prefs.getString(_authTokenKey);
      if (legacyToken != null && legacyToken.isNotEmpty) {
        await setAuthToken(legacyToken);
        await prefs.remove(_authTokenKey);
        return legacyToken;
      }
    } catch (e) {
      debugPrint('Eski token taşıma hatası: $e');
    }

    return null;
  }

  /// Token'ı Android Keystore destekli güvenli depolamaya yazar ve SharedPreferences'taki düz metin kopyaları siler.
  static Future<void> setAuthToken(String token) async {
    try {
      await secureStorage.write(key: _authTokenKey, value: token);
    } catch (e) {
      debugPrint('SecureStorage yazma hatası: $e');
    }

    try {
      final prefs = await SharedPreferences.getInstance();
      if (prefs.containsKey(_authTokenKey)) {
        await prefs.remove(_authTokenKey);
      }
    } catch (_) {}
  }

  /// Token'ı hem güvenli depolamadan hem de eski depolamadan temizler.
  static Future<void> clearAuthToken() async {
    try {
      await secureStorage.delete(key: _authTokenKey);
    } catch (e) {
      debugPrint('SecureStorage silme hatası: $e');
    }

    try {
      final prefs = await SharedPreferences.getInstance();
      if (prefs.containsKey(_authTokenKey)) {
        await prefs.remove(_authTokenKey);
      }
    } catch (_) {}
  }

  /// URL'deki localhost/127.0.0.1 adreslerini emülatörün erişebileceği IP/host ile değiştirir
  static String? normalizeImageUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    try {
      final uri = Uri.parse(baseUrl);
      final host = uri.host;
      final port = uri.port;
      final replacement = port != 0 && port != 80 && port != 443 ? '$host:$port' : host;
      
      var normalized = url
          .replaceAll('127.0.0.1:8000', replacement)
          .replaceAll('localhost:8000', replacement)
          .replaceAll('127.0.0.1', host)
          .replaceAll('localhost', host);

      if (kReleaseMode && normalized.startsWith('http://$host')) {
        normalized = normalized.replaceFirst('http://', 'https://');
      }
      return normalized;
    } catch (e) {
      return url;
    }
  }

  // Ortak Header yapılandırması (Token ile yetkilendirme)
  static Future<Map<String, String>> _getHeaders() async {
    final token = await getAuthToken();
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  // Giriş yap
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      final token = body['data']['token'];
      if (token is String && token.isNotEmpty) {
        await setAuthToken(token);
      }
      return body['data'];
    } else {
      throw Exception(body['message'] ?? 'Giriş başarısız oldu.');
    }
  }

  // Kayıt ol
  static Future<Map<String, dynamic>> register({
    required String email,
    required String password,
    required String name,
    required String surname,
    required String phone,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/register'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
        'first_name': name,
        'last_name': surname,
        'phone': phone,
      }),
    );
    
    final body = jsonDecode(response.body);
    if ((response.statusCode == 200 || response.statusCode == 201) && body['success'] == true) {
      final token = body['data']['token'];
      if (token is String && token.isNotEmpty) {
        await setAuthToken(token);
      }
      return body['data'];
    } else {
      throw Exception(body['message'] ?? 'Kayıt başarısız oldu.');
    }
  }

  // Çıkış yap
  static Future<void> logout() async {
    try {
      final headers = await _getHeaders();
      await http.post(
        Uri.parse('$baseUrl/auth/logout'),
        headers: headers,
      );
    } catch (e) {
      debugPrint('Logout API hatası: $e');
    } finally {
      await clearAuthToken();
    }
  }

  // Oturum açmış kullanıcının bilgilerini al
  static Future<Map<String, dynamic>> getMe() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/auth/me'),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'];
    } else {
      throw Exception(body['message'] ?? 'Kullanıcı bilgisi alınamadı.');
    }
  }

  // Hizmet listesini al (type = 'barber' veya 'cafe' filtresi destekler)
  static Future<List<dynamic>> getServices({String? type}) async {
    final headers = await _getHeaders();
    String url = '$baseUrl/services';
    if (type != null) {
      url += '?type=$type';
    }
    final response = await http.get(
      Uri.parse(url),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Hizmet listesi alınamadı.');
    }
  }

  // Aktif çalışanları (Berberleri) listele
  static Future<List<dynamic>> getEmployees() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/employees'),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Personel listesi alınamadı.');
    }
  }

  // Kullanıcının randevularını getir
  static Future<List<dynamic>> getAppointments() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/appointments?per_page=100'),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Randevu listesi alınamadı.');
    }
  }

  // Belirli bir personel ve tarih için müsaitlik ve meşgul saatleri getir
  static Future<Map<String, dynamic>> getAvailableSlots({
    required int employeeId,
    required String date,
    int? duration,
  }) async {
    final headers = await _getHeaders();
    var url = '$baseUrl/appointments/available-slots?employee_id=$employeeId&date=$date';
    if (duration != null && duration > 0) {
      url += '&duration=$duration';
    }
    final response = await http.get(
      Uri.parse(url),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as Map<String, dynamic>;
    } else {
      throw Exception(body['message'] ?? 'Müsait saatler alınamadı.');
    }
  }

  // Belirli bir personel ve tarih için randevuları getir (meşgul saatleri hesaplamak için)
  static Future<List<dynamic>> getEmployeeAppointments({
    required int employeeId,
    required String date,
  }) async {
    final headers = await _getHeaders();
    final url = '$baseUrl/appointments?employee_id=$employeeId&date_from=$date%2000:00:00&date_to=$date%2023:59:59&per_page=100';
    final response = await http.get(
      Uri.parse(url),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      if (body['data'] is Map && body['data']['data'] is List) {
        return body['data']['data'] as List;
      }
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Randevu listesi alınamadı.');
    }
  }

  // Merkezi Slot Servisi: Belirli personel, tarih ve toplam işlem süresine göre AvailableSlotModel listesi getir
  static Future<List<AvailableSlotModel>> getAvailableSlotModels({
    required int barberId,
    required String date,
    required int duration,
  }) async {
    final headers = await _getHeaders();
    final url = '$baseUrl/mobile/barbers/$barberId/available-slots?date=$date&duration=$duration';
    final response = await http.get(
      Uri.parse(url),
      headers: headers,
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      final list = body['data'] as List;
      return list.map((item) => AvailableSlotModel.fromMap(item as Map<String, dynamic>)).toList();
    } else {
      throw Exception(body['message'] ?? 'Müsait saatler alınamadı.');
    }
  }

  // Yeni Randevu oluştur
  static Future<Map<String, dynamic>> createAppointment({
    required String customerId,
    required int employeeId,
    required List<int> serviceIds,
    required String startAt,
    String? customerNote,
    String? couponCode,
    double? discountAmount,
  }) async {
    final headers = await _getHeaders();
    final bodyData = <String, dynamic>{
      'branch_id': 1,
      'customer_id': int.tryParse(customerId) ?? customerId,
      'employee_id': employeeId,
      'services': serviceIds.map((id) => {'service_id': id, 'quantity': 1}).toList(),
      'start_at': startAt,
      'customer_note': customerNote,
    };
    if (couponCode != null && couponCode.isNotEmpty) {
      bodyData['coupon_code'] = couponCode;
    }
    if (discountAmount != null && discountAmount > 0) {
      bodyData['discount_amount'] = discountAmount;
    }

    final response = await http.post(
      Uri.parse('$baseUrl/appointments'),
      headers: headers,
      body: jsonEncode(bodyData),
    );
    
    final body = jsonDecode(response.body);
    if ((response.statusCode == 200 || response.statusCode == 201) && body['success'] == true) {
      return body['data'];
    } else {
      throw Exception(body['message'] ?? 'Randevu oluşturulamadı.');
    }
  }

  // Randevu durumunu güncelle (Örn: iptal et)
  static Future<Map<String, dynamic>> updateAppointmentStatus(
    String appointmentId,
    String status, {
    String? note,
  }) async {
    final headers = await _getHeaders();
    final bodyMap = <String, dynamic>{
      'status': status,
    };
    if (note != null) {
      bodyMap['note'] = note;
    }

    final response = await http.put(
      Uri.parse('$baseUrl/mobile/document/appointments/$appointmentId'),
      headers: headers,
      body: jsonEncode(bodyMap),
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] != null ? Map<String, dynamic>.from(body['data']) : {};
    } else {
      throw Exception(body['message'] ?? 'Randevu durumu güncellenemedi.');
    }
  }

  // Bildirimleri getir
  static Future<List<dynamic>> getNotifications() async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/query'),
      headers: headers,
      body: jsonEncode({
        'collection': 'notifications',
      }),
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Bildirimler alınamadı.');
    }
  }

  // Yorum ve puanlama ekle
  static Future<ReviewResultModel> createReview({
    required String appointmentId,
    required int rating,
    String? comment,
  }) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/reviews'),
      headers: headers,
      body: jsonEncode({
        'appointment_id': appointmentId,
        'rating': rating,
        'comment': comment,
      }),
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return ReviewResultModel.fromMap(body, 'Değerlendirmeniz başarıyla kaydedildi.');
    } else {
      throw Exception(body['message'] ?? 'Yorum kaydedilemedi.');
    }
  }

  // Google Haritalar tıklama takibi (Arka planda sessizce çağrılır)
  static Future<void> trackGoogleReviewClick(int reviewId) async {
    try {
      final headers = await _getHeaders();
      await http.post(
        Uri.parse('$baseUrl/mobile/reviews/$reviewId/google-clicked'),
        headers: headers,
      );
    } catch (e) {
      debugPrint('Google review click tracking error: $e');
    }
  }

  // Cihaz push token kaydet
  static Future<void> saveDeviceToken(String fcmToken) async {
    try {
      final headers = await _getHeaders();
      final response = await http.post(
        Uri.parse('$baseUrl/mobile/save-token'),
        headers: headers,
        body: jsonEncode({
          'fcmToken': fcmToken,
          'device_type': 'android',
        }),
      );
      debugPrint('[FCM] saveDeviceToken tamamlandı: durum ${response.statusCode}');
    } catch (e) {
      debugPrint('[FCM] Device token save error: $e');
    }
  }

  // Kafe ürünlerini getir
  static Future<List<dynamic>> getCafeProducts() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/mobile/cafe-products'),
      headers: headers,
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Kafe ürünleri alınamadı.');
    }
  }

  // Satış ürünlerini getir
  static Future<List<dynamic>> getSaleProducts() async {
    final headers = await _getHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/mobile/sale-products'),
      headers: headers,
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Satış ürünleri alınamadı.');
    }
  }

  // Kampanyaları getir
  static Future<List<dynamic>> getCampaigns() async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/query'),
      headers: headers,
      body: jsonEncode({
        'collection': 'campaigns',
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Kampanyalar alınamadı.');
    }
  }

  // Kuponları getir
  static Future<List<dynamic>> getCoupons() async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/query'),
      headers: headers,
      body: jsonEncode({
        'collection': 'coupons',
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Kuponlar alınamadı.');
    }
  }

  // Kupon / Kampanya doğrula
  static Future<Map<String, dynamic>> validateCoupon({
    String? code,
    String? campaignId,
    required double subtotal,
    required List<int> serviceIds,
  }) async {
    final headers = await _getHeaders();
    final Map<String, dynamic> bodyData = {
      'subtotal': subtotal,
      'serviceIds': serviceIds,
    };
    if (code != null && code.isNotEmpty) {
      bodyData['couponCode'] = code;
    }
    if (campaignId != null && campaignId.isNotEmpty) {
      bodyData['campaignId'] = campaignId;
    }

    final response = await http.post(
      Uri.parse('$baseUrl/mobile/validate-coupon'),
      headers: headers,
      body: jsonEncode(bodyData),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as Map<String, dynamic>;
    } else {
      throw Exception(body['message'] ?? 'Kupon doğrulanamadı.');
    }
  }

  // Berber çalışma/izin durumu sorgula
  static Future<List<dynamic>> getBarberAvailability(int barberId) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/query'),
      headers: headers,
      body: jsonEncode({
        'collection': 'barberAvailability',
        'where': [
          {'field': 'barberId', 'value': barberId.toString()}
        ],
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Berber müsaitliği alınamadı.');
    }
  }

  // Şifremi Unuttum - Kod Gönder
  static Future<void> forgotPassword(String email) async {
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/forgot-password'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'email': email}),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode != 200 || body['success'] != true) {
      throw Exception(body['message'] ?? 'Şifre sıfırlama kodu gönderilemedi.');
    }
  }

  // Şifremi Unuttum - Kodu Doğrula
  static Future<void> verifyOtp(String email, String otp) async {
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/verify-otp'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'otp': otp,
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode != 200 || body['success'] != true) {
      throw Exception(body['message'] ?? 'Kod doğrulanamadı.');
    }
  }

  // Şifremi Unuttum - Yeni Şifre Belirle
  static Future<void> resetPassword({
    required String email,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/reset-password'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'otp': otp,
        'password': password,
        'password_confirmation': passwordConfirmation,
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode != 200 || body['success'] != true) {
      throw Exception(body['message'] ?? 'Şifre sıfırlanamadı.');
    }
  }

  // Profil Güncelle
  static Future<void> updateUserProfile({
    required String name,
    required String surname,
    required String phone,
  }) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/me/update'),
      headers: headers,
      body: jsonEncode({
        'name': name,
        'surname': surname,
        'phone': phone,
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode != 200 || body['success'] != true) {
      throw Exception(body['message'] ?? 'Profil güncellenemedi.');
    }
  }

  // Şifre Değiştir
  static Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/mobile/me/update-password'),
      headers: headers,
      body: jsonEncode({
        'currentPassword': currentPassword,
        'newPassword': newPassword,
      }),
    );

    final body = jsonDecode(response.body);
    if (response.statusCode != 200 || body['success'] != true) {
      throw Exception(body['message'] ?? 'Şifre değiştirilemedi.');
    }
  }

  // Profil Fotoğrafı Yükle
  static Future<String> uploadProfilePhoto(File file) async {
    final token = await getAuthToken();
    final uri = Uri.parse('$baseUrl/mobile/me/upload-photo');
    final request = http.MultipartRequest('POST', uri);
    request.headers['Accept'] = 'application/json';
    if (token != null && token.isNotEmpty) {
      request.headers['Authorization'] = 'Bearer $token';
    }

    request.files.add(await http.MultipartFile.fromPath('photo', file.path));

    final streamedResponse = await request.send();
    final response = await http.Response.fromStream(streamedResponse);
    final body = jsonDecode(response.body);

    if (response.statusCode == 200 && body['success'] == true) {
      final rawUrl = body['data']['profileImageUrl'] as String;
      return normalizeImageUrl(rawUrl) ?? rawUrl;
    } else {
      throw Exception(body['message'] ?? 'Profil fotoğrafı yüklenemedi.');
    }
  }
}

