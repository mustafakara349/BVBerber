import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:mobile_android/models/user_model.dart';

/// Kimlik doğrulama state yönetimi
class AuthProvider extends ChangeNotifier {
  bool _isLoading = false;
  bool _isLoggedIn = false;
  String? _errorMessage;
  UserModel? _currentUser;

  bool get isLoading => _isLoading;
  bool get isLoggedIn => _isLoggedIn;
  String? get errorMessage => _errorMessage;
  UserModel? get currentUser => _currentUser;

  /// Oturum durumunu başlat (Uygulama açılışında çağrılır)
  Future<bool> tryAutoLogin() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('auth_token');
      final rememberMe = prefs.getBool('remember_me') ?? false;
      
      if (token != null && rememberMe) {
        final userData = await ApiService.getMe();
        _currentUser = UserModel.fromMap(userData);
        _isLoggedIn = true;
        return true;
      } else {
        // Token var ama Beni Hatırla seçilmediyse veya token yoksa temizle
        if (token != null) {
          await ApiService.logout();
        }
        _currentUser = null;
        _isLoggedIn = false;
        return false;
      }
    } catch (e) {
      debugPrint('Auto login hatası: $e');
      _currentUser = null;
      _isLoggedIn = false;
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Giriş yap
  Future<void> signIn(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final loginData = await ApiService.login(email, password);
      _currentUser = UserModel.fromMap(loginData['user']);
      _isLoggedIn = true;
    } catch (e) {
      _isLoggedIn = false;
      _currentUser = null;
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Kayıt ol
  Future<void> signUp({
    required String email,
    required String password,
    required String name,
    required String surname,
    required String phone,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final registerData = await ApiService.register(
        email: email,
        password: password,
        name: name,
        surname: surname,
        phone: phone,
      );
      _currentUser = UserModel.fromMap(registerData['user']);
      _isLoggedIn = true;
    } catch (e) {
      _isLoggedIn = false;
      _currentUser = null;
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Şifre Sıfırlama
  Future<void> resetPassword(String email) async {
    _errorMessage = 'Şifre sıfırlama işlemi şu anda desteklenmiyor.';
    notifyListeners();
  }

  /// Çıkış yap
  Future<void> signOut() async {
    _isLoading = true;
    notifyListeners();

    try {
      await ApiService.logout();
      _isLoggedIn = false;
      _currentUser = null;
      
      // Çıkış yapıldığında Beni Hatırla tercihini sıfırla
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove('remember_me');
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hata mesajını temizle
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
