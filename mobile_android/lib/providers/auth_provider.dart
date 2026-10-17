import 'dart:io';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:mobile_android/models/user_model.dart';
import 'package:mobile_android/services/push_notification_service.dart';

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
      final token = await ApiService.getAuthToken();
      final rememberMe = prefs.getBool('remember_me') ?? false;
      
      if (token != null && rememberMe) {
        final userData = await ApiService.getMe();
        _currentUser = UserModel.fromMap(userData);
        _isLoggedIn = true;
        try {
          PushNotificationService().syncTokenWithBackend();
        } catch (e) {
          debugPrint('Push token senkronizasyon hatası: $e');
        }
        return true;
      } else {
        // Token var ama Beni Hatırla seçilmediyse veya token yoksa temizle
        if (token != null) {
          ApiService.logout().catchError((e) => debugPrint('Logout hatası: $e'));
          await ApiService.clearAuthToken();
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
      try {
        PushNotificationService().syncTokenWithBackend();
      } catch (e) {
        debugPrint('Push token senkronizasyon hatası: $e');
      }
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
      try {
        PushNotificationService().syncTokenWithBackend();
      } catch (e) {
        debugPrint('Push token senkronizasyon hatası: $e');
      }
    } catch (e) {
      _isLoggedIn = false;
      _currentUser = null;
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Şifre Sıfırlama - OTP Gönder
  Future<void> sendOtp(String email) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      await ApiService.forgotPassword(email);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Şifre Sıfırlama - OTP Doğrula
  Future<void> verifyOtp(String email, String otp) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      await ApiService.verifyOtp(email, otp);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Şifre Sıfırlama - Yeni Şifre Onayla
  Future<void> confirmResetPassword({
    required String email,
    required String otp,
    required String password,
    required String passwordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      await ApiService.resetPassword(
        email: email,
        otp: otp,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Profil Bilgilerini Güncelle
  Future<void> updateProfile({
    required String name,
    required String surname,
    required String phone,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      await ApiService.updateUserProfile(name: name, surname: surname, phone: phone);
      if (_currentUser != null) {
        _currentUser = _currentUser!.copyWith(name: name, surname: surname, phone: phone);
      }
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Şifre Değiştir
  Future<void> updatePassword({
    required String currentPassword,
    required String newPassword,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      await ApiService.updatePassword(currentPassword: currentPassword, newPassword: newPassword);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Profil Fotoğrafı Güncelle
  Future<String> updateProfilePhoto(File file) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    try {
      final newUrl = await ApiService.uploadProfilePhoto(file);
      if (_currentUser != null) {
        _currentUser = _currentUser!.copyWith(profileImageUrl: newUrl);
      }
      return newUrl;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
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
