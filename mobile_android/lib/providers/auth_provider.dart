import 'package:flutter/material.dart';

/// Kimlik doğrulama state yönetimi
class AuthProvider extends ChangeNotifier {
  bool _isLoading = false;
  bool _isLoggedIn = false;
  String? _errorMessage;

  bool get isLoading => _isLoading;
  bool get isLoggedIn => _isLoggedIn;
  String? get errorMessage => _errorMessage;

  /// Giriş yap
  Future<void> signIn(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // TODO: AuthService ile entegre et
      await Future.delayed(const Duration(seconds: 1));
      _isLoggedIn = true;
    } catch (e) {
      _errorMessage = e.toString();
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
      // TODO: AuthService ile entegre et
      await Future.delayed(const Duration(seconds: 1));
      _isLoggedIn = true;
    } catch (e) {
      _errorMessage = e.toString();
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
      // TODO: AuthService ile entegre et
      _isLoggedIn = false;
    } catch (e) {
      _errorMessage = e.toString();
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
