/// Firebase Authentication servisi
/// 
/// Firebase yapılandırması tamamlandığında bu sınıf
/// login, register ve şifre sıfırlama işlemlerini yönetecek.
class AuthService {
  // TODO: Firebase Auth instance eklenecek
  // final FirebaseAuth _auth = FirebaseAuth.instance;

  /// E-posta ve şifre ile giriş
  Future<void> signInWithEmail(String email, String password) async {
    // TODO: Firebase Auth entegrasyonu
    throw UnimplementedError('Firebase yapılandırması bekleniyor');
  }

  /// Yeni kullanıcı kaydı
  Future<void> signUpWithEmail({
    required String email,
    required String password,
    required String name,
    required String surname,
    required String phone,
  }) async {
    // TODO: Firebase Auth entegrasyonu
    throw UnimplementedError('Firebase yapılandırması bekleniyor');
  }

  /// Şifre sıfırlama e-postası gönder
  Future<void> resetPassword(String email) async {
    // TODO: Firebase Auth entegrasyonu
    throw UnimplementedError('Firebase yapılandırması bekleniyor');
  }

  /// Çıkış yap
  Future<void> signOut() async {
    // TODO: Firebase Auth entegrasyonu
    throw UnimplementedError('Firebase yapılandırması bekleniyor');
  }

  /// Mevcut kullanıcıyı kontrol et
  bool get isLoggedIn => false; // TODO: Firebase auth state kontrolü
}
