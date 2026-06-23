import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Yerel emülatör testi için varsayılan IP. Canlı ortam veya fiziksel cihaz testi için kendi IP'nizle değiştirilebilir.
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1';

  /// URL'deki localhost/127.0.0.1 adreslerini emülatörün erişebileceği IP/host ile değiştirir
  static String? normalizeImageUrl(String? url) {
    if (url == null || url.isEmpty) return null;
    try {
      final uri = Uri.parse(baseUrl);
      final host = uri.host;
      final port = uri.port;
      final replacement = port != 0 && port != 80 && port != 443 ? '$host:$port' : host;
      
      return url
          .replaceAll('127.0.0.1:8000', replacement)
          .replaceAll('localhost:8000', replacement)
          .replaceAll('127.0.0.1', host)
          .replaceAll('localhost', host);
    } catch (e) {
      return url;
    }
  }

  // Ortak Header yapılandırması (Token ile yetkilendirme)
  static Future<Map<String, String>> _getHeaders() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (token != null) {
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
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', token);
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
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', token);
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
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('auth_token');
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
      Uri.parse('$baseUrl/appointments'),
      headers: headers,
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'] as List;
    } else {
      throw Exception(body['message'] ?? 'Randevu listesi alınamadı.');
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

  // Yeni Randevu oluştur
  static Future<Map<String, dynamic>> createAppointment({
    required String customerId,
    required int employeeId,
    required List<int> serviceIds,
    required String startAt,
    String? customerNote,
  }) async {
    final headers = await _getHeaders();
    final response = await http.post(
      Uri.parse('$baseUrl/appointments'),
      headers: headers,
      body: jsonEncode({
        'branch_id': 1,
        'customer_id': int.tryParse(customerId) ?? customerId,
        'employee_id': employeeId,
        'services': serviceIds.map((id) => {'service_id': id, 'quantity': 1}).toList(),
        'start_at': startAt,
        'customer_note': customerNote,
      }),
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

    final response = await http.patch(
      Uri.parse('$baseUrl/appointments/$appointmentId/status'),
      headers: headers,
      body: jsonEncode(bodyMap),
    );
    
    final body = jsonDecode(response.body);
    if (response.statusCode == 200 && body['success'] == true) {
      return body['data'];
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
}
