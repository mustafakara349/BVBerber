import 'package:flutter/material.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:firebase_auth/firebase_auth.dart';
import 'package:mobile_android/models/appointment_model.dart';
import 'package:mobile_android/core/enums.dart';

/// Randevu state yönetimi
class AppointmentProvider extends ChangeNotifier {
  final FirebaseFirestore _db = FirebaseFirestore.instance;
  List<AppointmentModel> _appointments = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<AppointmentModel> get appointments => _appointments;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Yaklaşan randevular (bugün ve sonrası, iptal edilmemiş)
  List<AppointmentModel> get upcomingAppointments {
    final now = DateTime.now();
    return _appointments
        .where((a) =>
            a.dateTime.isAfter(now) &&
            a.status != AppointmentStatus.cancelled &&
            a.status != AppointmentStatus.completed)
        .toList()
      ..sort((a, b) => a.dateTime.compareTo(b.dateTime));
  }

  /// Geçmiş randevular (tamamlanmış veya tarihi geçmiş)
  List<AppointmentModel> get pastAppointments {
    final now = DateTime.now();
    return _appointments
        .where((a) =>
            a.dateTime.isBefore(now) ||
            a.status == AppointmentStatus.completed ||
            a.status == AppointmentStatus.cancelled)
        .toList()
      ..sort((a, b) => b.dateTime.compareTo(a.dateTime));
  }

  /// Randevuları Firestore'dan yükle (mevcut kullanıcının randevuları)
  Future<void> loadAppointments() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final user = FirebaseAuth.instance.currentUser;
      if (user == null) throw Exception('Kullanıcı bulunamadı');

      final snapshot = await _db
          .collection('appointments')
          .where('customerId', isEqualTo: user.uid)
          .get();

      _appointments = snapshot.docs
          .map((doc) => AppointmentModel.fromMap(doc.data(), doc.id))
          .toList();
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
    _errorMessage = null;
    notifyListeners();

    try {
      final docRef = await _db.collection('appointments').add(appointment.toMap());
      _appointments.add(AppointmentModel(
        id: docRef.id,
        customerId: appointment.customerId,
        barberId: appointment.barberId,
        serviceId: appointment.serviceId,
        dateTime: appointment.dateTime,
        status: appointment.status,
        note: appointment.note,
        createdAt: appointment.createdAt,
      ));
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
      await _db.collection('appointments').doc(appointmentId).update({
        'status': AppointmentStatus.cancelled.name,
      });

      final index = _appointments.indexWhere((a) => a.id == appointmentId);
      if (index != -1) {
        _appointments[index] = _appointments[index].copyWith(
          status: AppointmentStatus.cancelled,
        );
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
