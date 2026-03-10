import 'package:flutter/material.dart';

import 'package:mobile_android/services/auth_service.dart';

/// Kimlik doğrulama state yönetimi
class AuthProvider extends ChangeNotifier {
  final AuthService _authService = AuthService();
  bool _isLoading = false;
  bool _isLoggedIn = false;
  String? _errorMessage;

  bool get isLoading => _isLoading;
  bool get isLoggedIn {
    // Uygulama her başladığında durumu AuthService'ten alır
    return _authService.isLoggedIn;
  }
  String? get errorMessage => _errorMessage;

  /// Giriş yap
  Future<void> signIn(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _authService.signInWithEmail(email, password);
      _isLoggedIn = true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', ''); // Hata mesajındaki gereksiz Exception yazısını kaldırıyoruz
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
      await _authService.signUpWithEmail(
        email: email, 
        password: password, 
        name: name, 
        surname: surname, 
        phone: phone,
      );
      _isLoggedIn = true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Şifre Sıfırlama
  Future<void> resetPassword(String email) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();
    
    try {
      await _authService.resetPassword(email);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
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
      await _authService.signOut();
      _isLoggedIn = false;
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
