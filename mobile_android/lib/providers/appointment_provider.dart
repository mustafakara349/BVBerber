import 'package:flutter/material.dart';
import 'package:mobile_android/models/appointment_model.dart';
import 'package:mobile_android/core/enums.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:mobile_android/services/notification_service.dart';

/// Randevu state yönetimi
class AppointmentProvider extends ChangeNotifier {
  List<AppointmentModel> _appointments = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<AppointmentModel> get appointments => _appointments;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Yaklaşan randevular (bugün ve sonrası, aktif durumda olanlar)
  List<AppointmentModel> get upcomingAppointments {
    final now = DateTime.now();
    return _appointments
        .where((a) =>
            a.dateTime.isAfter(now) &&
            a.status != AppointmentStatus.cancelled &&
            a.status != AppointmentStatus.rejected &&
            a.status != AppointmentStatus.completed &&
            a.status != AppointmentStatus.noShow)
        .toList()
      ..sort((a, b) => a.dateTime.compareTo(b.dateTime));
  }

  /// Geçmiş randevular (tamamlanmış, gelmedi, iptal/red veya tarihi geçmiş olanlar)
  List<AppointmentModel> get pastAppointments {
    final now = DateTime.now();
    return _appointments
        .where((a) =>
            a.dateTime.isBefore(now) ||
            a.status == AppointmentStatus.completed ||
            a.status == AppointmentStatus.noShow ||
            a.status == AppointmentStatus.cancelled ||
            a.status == AppointmentStatus.rejected)
        .toList()
      ..sort((a, b) => b.dateTime.compareTo(a.dateTime));
  }

  /// Randevuları API'den yükle (mevcut kullanıcının randevuları)
  Future<void> loadAppointments() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final dataList = await ApiService.getAppointments();
      _appointments = dataList
          .map((data) => AppointmentModel.fromMap(data))
          .toList();

      // Yaklaşan randevular için yerel hatırlatıcıları planla
      final notificationService = NotificationService();
      final now = DateTime.now();
      for (final appt in _appointments) {
        if (appt.dateTime.isAfter(now) &&
            appt.status != AppointmentStatus.cancelled &&
            appt.status != AppointmentStatus.rejected &&
            appt.status != AppointmentStatus.completed &&
            appt.status != AppointmentStatus.noShow) {
          await notificationService.scheduleAppointmentReminder(appt);
        }
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Yeni randevu oluştur
  Future<void> createAppointment(AppointmentModel appointment, {List<int>? serviceIds}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final empId = int.tryParse(appointment.barberId) ?? 1;
      final svcIds = serviceIds ?? [int.tryParse(appointment.serviceId) ?? 1];
      
      // format YYYY-MM-DD HH:MM:SS
      final startAtStr = appointment.dateTime.toString().substring(0, 19);

      final responseData = await ApiService.createAppointment(
        customerId: appointment.customerId,
        employeeId: empId,
        serviceIds: svcIds,
        startAt: startAtStr,
        customerNote: appointment.note,
      );

      final newAppointment = AppointmentModel.fromMap(responseData);
      _appointments.add(newAppointment);

      // Yeni randevu için hatırlatıcı planla
      await NotificationService().scheduleAppointmentReminder(newAppointment);
    } catch (e) {
      _errorMessage = e.toString();
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Randevu iptal et
  Future<void> cancelAppointment(String appointmentId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await ApiService.updateAppointmentStatus(appointmentId, 'cancelled');

      // Planlanmış yerel hatırlatıcıyı iptal et
      await NotificationService().cancelAppointmentReminder(appointmentId);

      final index = _appointments.indexWhere((a) => a.id == appointmentId);
      if (index != -1) {
        _appointments[index] = _appointments[index].copyWith(
          status: AppointmentStatus.cancelled,
        );
      }
    } catch (e) {
      _errorMessage = e.toString();
      rethrow;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Randevuyu yerel durumda değerlendirilmiş olarak işaretle
  void markAsReviewed(String appointmentId, int rating) {
    final index = _appointments.indexWhere((a) => a.id == appointmentId);
    if (index != -1) {
      _appointments[index] = _appointments[index].copyWith(
        isReviewed: true,
        rating: rating,
      );
      notifyListeners();
    }
  }
}
