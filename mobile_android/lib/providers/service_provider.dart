import 'package:flutter/material.dart';
import 'package:mobile_android/models/service_model.dart';
import 'package:mobile_android/services/api_service.dart';

/// Hizmet state yönetimi
class ServiceProvider extends ChangeNotifier {
  List<ServiceModel> _services = [];
  List<ServiceModel> _cafeServices = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<ServiceModel> get services => _services;
  List<ServiceModel> get cafeServices => _cafeServices;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Hizmetleri API'den yükle
  Future<void> loadServices() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Berber hizmetlerini yükle
      final barberData = await ApiService.getServices(type: 'barber');
      _services = barberData
          .map((data) => ServiceModel.fromMap(data))
          .toList();

      // Kafe hizmetlerini yükle
      final cafeData = await ApiService.getServices(type: 'cafe');
      _cafeServices = cafeData
          .map((data) => ServiceModel.fromMap(data))
          .toList();
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Hizmet ekle (Yerel durum güncellemesi - Admin paneli Laravel web'e taşındı)
  Future<void> addService(ServiceModel service) async {
    _services.add(service);
    notifyListeners();
  }

  /// Hizmet güncelle (Yerel durum güncellemesi - Admin paneli Laravel web'e taşındı)
  Future<void> updateService(ServiceModel service) async {
    final index = _services.indexWhere((s) => s.id == service.id);
    if (index != -1) {
      _services[index] = service;
      notifyListeners();
    }
  }

  /// Hizmet sil (Yerel durum güncellemesi - Admin paneli Laravel web'e taşındı)
  Future<void> deleteService(String serviceId) async {
    _services.removeWhere((s) => s.id == serviceId);
    notifyListeners();
  }
}
