import 'package:cloud_firestore/cloud_firestore.dart';

/// Firestore veritabanı servisi
///
/// Koleksiyonlar üzerinde CRUD işlemlerini yönetir.
class FirestoreService {
  final FirebaseFirestore _db = FirebaseFirestore.instance;

  /// Belge oluştur
  Future<void> createDocument({
    required String collection,
    required String docId,
    required Map<String, dynamic> data,
  }) async {
    await _db.collection(collection).doc(docId).set(data);
  }

  /// Belge oku
  Future<Map<String, dynamic>?> getDocument({
    required String collection,
    required String docId,
  }) async {
    final doc = await _db.collection(collection).doc(docId).get();
    return doc.data();
  }

  /// Belge güncelle
  Future<void> updateDocument({
    required String collection,
    required String docId,
    required Map<String, dynamic> data,
  }) async {
    await _db.collection(collection).doc(docId).update(data);
  }

  /// Belge sil
  Future<void> deleteDocument({
    required String collection,
    required String docId,
  }) async {
    await _db.collection(collection).doc(docId).delete();
  }

  /// Koleksiyon sorgula
  Future<List<Map<String, dynamic>>> getCollection({
    required String collection,
  }) async {
    final snapshot = await _db.collection(collection).get();
    return snapshot.docs.map((doc) {
      final data = doc.data();
      data['id'] = doc.id;
      return data;
    }).toList();
  }

  /// Koleksiyonu gerçek zamanlı dinle
  Stream<List<Map<String, dynamic>>> streamCollection({
    required String collection,
  }) {
    return _db.collection(collection).snapshots().map((snapshot) {
      return snapshot.docs.map((doc) {
        final data = doc.data();
        data['id'] = doc.id;
        return data;
      }).toList();
    });
  }

  /// Belirli bir belgeyi gerçek zamanlı dinle
  Stream<Map<String, dynamic>?> streamDocument({
    required String collection,
    required String docId,
  }) {
    return _db.collection(collection).doc(docId).snapshots().map((doc) {
      return doc.data();
    });
  }
}
