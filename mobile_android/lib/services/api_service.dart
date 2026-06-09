import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Yerel emülatör testi için varsayılan IP. Canlı ortam veya fiziksel cihaz testi için kendi IP'nizle değiştirilebilir.
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1';

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
}
