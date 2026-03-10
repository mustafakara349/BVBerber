import 'package:firebase_auth/firebase_auth.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:mobile_android/models/user_model.dart';
import 'package:mobile_android/core/enums.dart';

/// Firebase Authentication servisi
/// 
/// Firebase yapılandırması tamamlandığında bu sınıf
/// login, register ve şifre sıfırlama işlemlerini yönetecek.
class AuthService {
  final FirebaseAuth _auth = FirebaseAuth.instance;
  final FirebaseFirestore _firestore = FirebaseFirestore.instance;

  /// E-posta ve şifre ile giriş
  Future<void> signInWithEmail(String email, String password) async {
    try {
      await _auth.signInWithEmailAndPassword(email: email, password: password);
    } catch (e) {
      throw Exception('Giriş başarısız oldu: ${e.toString()}');
    }
  }

  /// Yeni kullanıcı kaydı
  Future<void> signUpWithEmail({
    required String email,
    required String password,
    required String name,
    required String surname,
    required String phone,
  }) async {
    try {
      // 1. Firebase Auth'da kullanıcı oluştur
      UserCredential credential = await _auth.createUserWithEmailAndPassword(
        email: email,
        password: password,
      );

      // 2. Kullanıcının UID'sini al
      String uid = credential.user!.uid;

      // 3. Kullanıcının detaylı bilgilerini Firestore'a (users koleksiyonu) kaydet
      UserModel newUser = UserModel(
        id: uid,
        name: name,
        surname: surname,
        email: email,
        phone: phone,
        role: UserRole.customer, // Yeni kayıt olan herkes varsayılan müşteri olur
        createdAt: DateTime.now(),
      );

      await _firestore.collection('users').doc(uid).set(newUser.toMap());
    } catch (e) {
      throw Exception('Kayıt başarısız oldu: ${e.toString()}');
    }
  }

  /// Şifre sıfırlama e-postası gönder
  Future<void> resetPassword(String email) async {
    try {
      await _auth.sendPasswordResetEmail(email: email);
    } catch (e) {
      throw Exception('Şifre sıfırlama maili gönderilemedi: ${e.toString()}');
    }
  }

  /// Çıkış yap
  Future<void> signOut() async {
    await _auth.signOut();
  }

  /// Mevcut kullanıcıyı kontrol et
  bool get isLoggedIn => _auth.currentUser != null; 
}
