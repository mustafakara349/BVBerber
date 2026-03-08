/// Form validasyon fonksiyonları
class Validators {
  Validators._();

  /// E-posta doğrulama
  static String? email(String? value) {
    if (value == null || value.isEmpty) {
      return 'E-posta adresi gerekli';
    }
    final emailRegex = RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$');
    if (!emailRegex.hasMatch(value)) {
      return 'Geçerli bir e-posta adresi girin';
    }
    return null;
  }

  /// Şifre doğrulama
  static String? password(String? value) {
    if (value == null || value.isEmpty) {
      return 'Şifre gerekli';
    }
    if (value.length < 6) {
      return 'Şifre en az 6 karakter olmalı';
    }
    return null;
  }

  /// Şifre tekrar doğrulama
  static String? confirmPassword(String? value, String password) {
    if (value == null || value.isEmpty) {
      return 'Şifre tekrarı gerekli';
    }
    if (value != password) {
      return 'Şifreler eşleşmiyor';
    }
    return null;
  }

  /// İsim doğrulama
  static String? name(String? value) {
    if (value == null || value.isEmpty) {
      return 'Bu alan gerekli';
    }
    if (value.length < 2) {
      return 'En az 2 karakter girin';
    }
    return null;
  }

  /// Telefon doğrulama
  static String? phone(String? value) {
    if (value == null || value.isEmpty) {
      return 'Telefon numarası gerekli';
    }
    final phoneRegex = RegExp(r'^\+?[\d\s-]{10,}$');
    if (!phoneRegex.hasMatch(value)) {
      return 'Geçerli bir telefon numarası girin';
    }
    return null;
  }

  /// Boş alan doğrulama
  static String? required(String? value) {
    if (value == null || value.isEmpty) {
      return 'Bu alan boş bırakılamaz';
    }
    return null;
  }
}
