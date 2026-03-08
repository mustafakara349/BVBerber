import 'package:flutter/material.dart';
import 'package:mobile_android/models/appointment_model.dart';

/// Randevu state yönetimi
class AppointmentProvider extends ChangeNotifier {
  List<AppointmentModel> _appointments = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<AppointmentModel> get appointments => _appointments;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Randevuları yükle
  Future<void> loadAppointments() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
      _appointments = [];
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Yeni randevu oluştur
  Future<void> createAppointment(AppointmentModel appointment) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
      _appointments.add(appointment);
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Randevu iptal et
  Future<void> cancelAppointment(String appointmentId) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: FirestoreService ile entegre et
      _appointments.removeWhere((a) => a.id == appointmentId);
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
