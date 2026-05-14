import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:mobile_android/models/service_model.dart';

/// Hizmet state yönetimi
class ServiceProvider extends ChangeNotifier {
  final FirebaseFirestore _db = FirebaseFirestore.instance;
  List<ServiceModel> _services = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<ServiceModel> get services => _services;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Hizmetleri Firestore'dan yükle
  Future<void> loadServices() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final snapshot = await _db
          .collection('services')
          .where('isActive', isEqualTo: true)
          .get();

      _services = snapshot.docs
          .map((doc) => ServiceModel.fromMap(doc.data(), doc.id))
          .toList();
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hizmet ekle (Admin)
  Future<void> addService(ServiceModel service) async {
    _isLoading = true;
    notifyListeners();

    try {
      final docRef = await _db.collection('services').add(service.toMap());
      _services.add(ServiceModel(
        id: docRef.id,
        name: service.name,
        description: service.description,
        price: service.price,
        durationMinutes: service.durationMinutes,
        imageUrl: service.imageUrl,
        isActive: service.isActive,
      ));
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hizmet güncelle (Admin)
  Future<void> updateService(ServiceModel service) async {
    _isLoading = true;
    notifyListeners();

    try {
      await _db.collection('services').doc(service.id).update(service.toMap());
      final index = _services.indexWhere((s) => s.id == service.id);
      if (index != -1) {
        _services[index] = service;
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hizmet sil (Admin)
  Future<void> deleteService(String serviceId) async {
    _isLoading = true;
    notifyListeners();

    try {
      await _db.collection('services').doc(serviceId).delete();
      _services.removeWhere((s) => s.id == serviceId);
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
