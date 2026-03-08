import 'package:flutter/material.dart';
import 'package:mobile_android/models/service_model.dart';

/// Hizmet state yönetimi
class ServiceProvider extends ChangeNotifier {
  List<ServiceModel> _services = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<ServiceModel> get services => _services;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Hizmetleri yükle
  Future<void> loadServices() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
      _services = [];
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hizmet ekle
  Future<void> addService(ServiceModel service) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
      _services.add(service);
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hizmet güncelle
  Future<void> updateService(ServiceModel service) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
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

  /// Hizmet sil
  Future<void> deleteService(String serviceId) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
      _services.removeWhere((s) => s.id == serviceId);
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
