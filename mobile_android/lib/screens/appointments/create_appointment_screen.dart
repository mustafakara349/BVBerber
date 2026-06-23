import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobile_android/providers/auth_provider.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/core/enums.dart';
import 'package:mobile_android/models/barber_model.dart';
import 'package:mobile_android/models/service_model.dart';
import 'package:mobile_android/models/appointment_model.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/providers/appointment_provider.dart';

class CreateAppointmentScreen extends StatefulWidget {
  const CreateAppointmentScreen({super.key});

  @override
  State<CreateAppointmentScreen> createState() => _CreateAppointmentScreenState();
}

class _CreateAppointmentScreenState extends State<CreateAppointmentScreen> {
  List<BarberModel> _barbers = [];
  List<ServiceModel> _services = [];
  BarberModel? _selectedBarber;
  final List<ServiceModel> _selectedServices = [];
  DateTime? _selectedDate;
  String? _selectedTime;
  List<String> _busyTimes = [];
  bool _isLoadingBarbers = true;
  bool _isLoadingServices = true;
  bool _isLoadingTimes = false;
  bool _isCreating = false;

  final List<String> _allTimes = [];

  @override
  void initState() {
    super.initState();
    // 08:00 - 21:30 arası 30 dakika aralıklarla saatler
    for (int h = 8; h <= 21; h++) {
      _allTimes.add('${h.toString().padLeft(2, '0')}:00');
      if (h < 21 || true) _allTimes.add('${h.toString().padLeft(2, '0')}:30');
    }
    _loadBarbers();
    _loadServices();
  }

  Future<void> _loadBarbers() async {
    try {
      final data = await ApiService.getEmployees();
      _barbers = data.map((d) => BarberModel.fromMap(d)).toList();
      if (_barbers.isNotEmpty) _selectedBarber = _barbers.first;
    } catch (e) {
      debugPrint('Berber yüklenirken hata: $e');
    }
    if (mounted) setState(() => _isLoadingBarbers = false);
  }

  Future<void> _loadServices() async {
    try {
      final data = await ApiService.getServices(type: 'barber');
      _services = data.map((d) => ServiceModel.fromMap(d)).toList();
    } catch (e) {
      debugPrint('Hizmetler yüklenirken hata: $e');
    }
    if (mounted) setState(() => _isLoadingServices = false);
  }

  DateTime _getTurkishLocalTime() {
    return DateTime.now().toUtc().add(const Duration(hours: 3));
  }

  bool _isToday(DateTime date) {
    final now = _getTurkishLocalTime();
    return date.year == now.year && date.month == now.month && date.day == now.day;
  }

  bool _isTimeInPast(String timeStr) {
    if (_selectedDate == null) return false;
    if (!_isToday(_selectedDate!)) return false;
    final now = _getTurkishLocalTime();
    final parts = timeStr.split(':');
    final hour = int.parse(parts[0]);
    final minute = int.parse(parts[1]);
    
    if (hour < now.hour) return true;
    if (hour == now.hour && minute <= now.minute) return true;
    return false;
  }

  int get _totalDuration {
    return _selectedServices.fold(0, (sum, service) => sum + service.durationMinutes);
  }

  int get _slotsNeeded {
    return (_totalDuration / 30).ceil();
  }

  bool _isSlotAvailable(String time) {
    final index = _allTimes.indexOf(time);
    if (index == -1) return false;
    
    final needed = _slotsNeeded;
    if (needed <= 0) return true; // No services selected yet, allow selection
    
    // Check if there are enough consecutive slots
    if (index + needed > _allTimes.length) return false;
    
    for (int k = 0; k < needed; k++) {
      final slotTime = _allTimes[index + k];
      if (_busyTimes.contains(slotTime) || _isTimeInPast(slotTime)) {
        return false;
      }
    }
    
    return true;
  }

  Map<String, List<ServiceModel>> get _groupedServices {
    final map = <String, List<ServiceModel>>{};
    for (final service in _services) {
      map.putIfAbsent(service.categoryName, () => []).add(service);
    }
    return map;
  }

  Future<void> _loadBusyTimes() async {
    if (_selectedBarber == null || _selectedDate == null) return;
    setState(() { _isLoadingTimes = true; _selectedTime = null; });
    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate!);
      final empId = int.tryParse(_selectedBarber!.id) ?? 1;
      final appointmentsData = await ApiService.getEmployeeAppointments(
        employeeId: empId,
        date: dateStr,
      );
      
      final busyList = <String>[];
      for (final appMap in appointmentsData) {
        final statusStr = appMap['status']?.toString();
        if (statusStr == 'cancelled') continue;
        
        final startStr = appMap['start_at']?.toString();
        final endStr = appMap['end_at']?.toString();
        if (startStr == null || endStr == null) continue;
        
        final start = DateTime.parse(startStr);
        final end = DateTime.parse(endStr);
        
        for (final time in _allTimes) {
          final parts = time.split(':');
          final hour = int.parse(parts[0]);
          final minute = int.parse(parts[1]);
          final slotDateTime = DateTime(_selectedDate!.year, _selectedDate!.month, _selectedDate!.day, hour, minute);
          
          if ((slotDateTime.isAfter(start) || slotDateTime.isAtSameMomentAs(start)) &&
              slotDateTime.isBefore(end)) {
            if (!busyList.contains(time)) {
              busyList.add(time);
            }
          }
        }
      }
      
      if (mounted) {
        setState(() {
          _busyTimes = busyList;
        });
      }
    } catch (e) {
      debugPrint('Meşgul saatler yüklenirken hata: $e');
    }
    if (mounted) setState(() => _isLoadingTimes = false);
  }

  List<DateTime> get _availableDates {
    final now = _getTurkishLocalTime();
    return List.generate(30, (i) => DateTime(now.year, now.month, now.day + i));
  }

  void _showConfirmationSheet() {
    if (_selectedBarber == null || _selectedServices.isEmpty ||
        _selectedDate == null || _selectedTime == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Lütfen tüm alanları seçin'), backgroundColor: Colors.orange));
      return;
    }

    final dateStr = DateFormat('d MMMM EEEE', 'tr_TR').format(_selectedDate!);
    final serviceNames = _selectedServices.map((s) => s.name).join(', ');
    final totalPrice = _selectedServices.fold<double>(0, (sum, s) => sum + s.price);

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (_) => Container(
        padding: const EdgeInsets.fromLTRB(24, 16, 24, 24),
        decoration: const BoxDecoration(
          color: Color(0xFF2A2A2A),
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 40, height: 4, decoration: BoxDecoration(
            color: Colors.white24, borderRadius: BorderRadius.circular(2))),
          const SizedBox(height: 20),
          const Text('Randevu Özeti', style: TextStyle(
            color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 24),
          _summaryRow(Icons.calendar_today, 'Tarih', dateStr),
          _summaryRow(Icons.access_time, 'Saat', _selectedTime!),
          _summaryRow(Icons.person, 'Personel',
            '${_selectedBarber!.name} ${_selectedBarber!.surname}'),
          _summaryRow(Icons.content_cut, 'Hizmetler', serviceNames),
          _summaryRow(Icons.access_time_filled, 'Toplam Süre', '$_totalDuration dk'),
          _summaryRow(Icons.monetization_on_outlined, 'Ücret',
            '₺${totalPrice.toStringAsFixed(0)}'),
          const SizedBox(height: 24),
          SizedBox(width: double.infinity, height: 54, child: ElevatedButton.icon(
            onPressed: _isCreating ? null : _createAppointment,
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.secondaryColor,
              foregroundColor: Colors.black,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
              textStyle: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
            icon: _isCreating
              ? const SizedBox(width: 20, height: 20,
                  child: CircularProgressIndicator(color: Colors.black, strokeWidth: 2.5))
              : const Icon(Icons.check_circle, size: 22),
            label: const Text('Randevuyu Onayla'),
          )),
          const SizedBox(height: 12),
          GestureDetector(
            onTap: () => Navigator.pop(context),
            child: const Row(mainAxisAlignment: MainAxisAlignment.center, children: [
              Icon(Icons.edit, color: Colors.white54, size: 16),
              SizedBox(width: 6),
              Text('Düzenle', style: TextStyle(color: Colors.white54, fontSize: 15)),
            ]),
          ),
          const SizedBox(height: 8),
        ]),
      ),
    );
  }

  Widget _summaryRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(children: [
        Icon(icon, color: AppTheme.secondaryColor, size: 20),
        const SizedBox(width: 12),
        Text(label, style: const TextStyle(color: Colors.white54, fontSize: 14)),
        const Spacer(),
        Expanded(
          child: Text(value, textAlign: TextAlign.end, style: const TextStyle(
            color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
        ),
      ]),
    );
  }

  Future<void> _createAppointment() async {
    setState(() => _isCreating = true);
    try {
      final timeParts = _selectedTime!.split(':');
      final appointmentDT = DateTime(_selectedDate!.year, _selectedDate!.month,
          _selectedDate!.day, int.parse(timeParts[0]), int.parse(timeParts[1]));
      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
      if (user == null) throw Exception('Oturum bulunamadı');

      final appointment = AppointmentModel(
        id: '',
        customerId: user.id,
        barberId: _selectedBarber!.id,
        serviceId: _selectedServices.isNotEmpty ? _selectedServices.first.id : '',
        dateTime: appointmentDT,
        status: AppointmentStatus.pending,
        createdAt: DateTime.now(),
      );

      final serviceIds = _selectedServices.map((s) => int.parse(s.id)).toList();

      await Provider.of<AppointmentProvider>(context, listen: false)
          .createAppointment(appointment, serviceIds: serviceIds);

      if (!mounted) return;
      Navigator.pop(context); // close bottom sheet
      Navigator.pop(context); // back to appointments list
      Provider.of<AppointmentProvider>(context, listen: false).loadAppointments();
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Hata: $e'), backgroundColor: AppTheme.errorColor));
    } finally {
      if (mounted) setState(() => _isCreating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A1A1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A1A1A), elevation: 0,
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context)),
        centerTitle: true,
        title: const Text('Randevu Oluştur', style: TextStyle(
          color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
      ),
      bottomNavigationBar: Container(
        color: const Color(0xFF1A1A1A),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 10, 20, 10),
            child: SizedBox(height: 54, child: ElevatedButton(
              onPressed: _showConfirmationSheet,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                textStyle: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold)),
              child: const Text('Randevuyu Onayla'),
            )),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          const SizedBox(height: 8),
          // PERSONEL SEÇ
          const Text('Personel Seç', style: TextStyle(
            color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          _isLoadingBarbers
            ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
            : SizedBox(height: 100, child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: _barbers.length,
                itemBuilder: (_, i) => _buildBarberChip(_barbers[i]),
              )),
          const SizedBox(height: 28),

          // TARİH SEÇİN
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            const Text('Tarih Seçin', style: TextStyle(
              color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
            Text(DateFormat('MMMM yyyy', 'tr_TR').format(DateTime.now()),
              style: const TextStyle(color: AppTheme.secondaryColor, fontSize: 14,
                fontWeight: FontWeight.w600)),
          ]),
          const SizedBox(height: 16),
          SizedBox(height: 60, child: ListView.builder(
            scrollDirection: Axis.horizontal,
            itemCount: _availableDates.length,
            itemBuilder: (_, i) => _buildDateChip(_availableDates[i]),
          )),
          const SizedBox(height: 28),

          // HİZMET SEÇİN
          const Text('Hizmet Seçin', style: TextStyle(
            color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 16),
          _isLoadingServices
            ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
            : Column(
                children: _groupedServices.entries.map((entry) {
                  final category = entry.key;
                  final categoryServices = entry.value;
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFF2A2A2A),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFF3A3A3A)),
                    ),
                    child: Theme(
                      data: Theme.of(context).copyWith(dividerColor: Colors.transparent),
                      child: ExpansionTile(
                        title: Text(category, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                        iconColor: AppTheme.secondaryColor,
                        collapsedIconColor: Colors.white70,
                        children: categoryServices.map((s) => _buildServiceCard(s)).toList(),
                      ),
                    ),
                  );
                }).toList(),
              ),
          const SizedBox(height: 28),

          // SAAT SEÇİN
          if (_selectedDate != null) ...[
            Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
              const Text('Saat Seçin', style: TextStyle(
                color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
              Row(children: [
                Container(width: 8, height: 8, decoration: BoxDecoration(
                  shape: BoxShape.circle, color: AppTheme.secondaryColor)),
                const SizedBox(width: 6),
                const Text('DOLU', style: TextStyle(
                  color: Colors.white38, fontSize: 12, fontWeight: FontWeight.w600)),
              ]),
            ]),
            const SizedBox(height: 16),
            _isLoadingTimes
              ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
              : GridView.builder(
                  shrinkWrap: true, physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 4, crossAxisSpacing: 10,
                    mainAxisSpacing: 10, childAspectRatio: 2.2),
                  itemCount: _allTimes.length,
                  itemBuilder: (_, i) => _buildTimeChip(_allTimes[i]),
                ),
          ],
          const SizedBox(height: 24),
        ]),
      ),
    );
  }

  Widget _buildBarberChip(BarberModel barber) {
    final isSelected = _selectedBarber?.id == barber.id;
    final initials = '${barber.name.isNotEmpty ? barber.name[0] : ''}${barber.surname.isNotEmpty ? barber.surname[0] : ''}'.toUpperCase();
    return GestureDetector(
      onTap: () {
        setState(() => _selectedBarber = barber);
        if (_selectedDate != null) _loadBusyTimes();
      },
      child: Padding(
        padding: const EdgeInsets.only(right: 16),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(
            width: 56, height: 56,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: isSelected ? AppTheme.secondaryColor.withOpacity(0.2) : const Color(0xFF2A2A2A),
              border: Border.all(
                color: isSelected ? AppTheme.secondaryColor : const Color(0xFF3A3A3A), width: 2),
            ),
            child: barber.profileImageUrl != null
              ? ClipOval(child: Image.network(barber.profileImageUrl!, fit: BoxFit.cover))
              : Center(child: Text(initials, style: TextStyle(
                  color: isSelected ? AppTheme.secondaryColor : Colors.white54,
                  fontSize: 16, fontWeight: FontWeight.bold))),
          ),
          const SizedBox(height: 6),
          Text(barber.name, style: const TextStyle(color: Colors.white70, fontSize: 11)),
          Text(barber.surname, style: const TextStyle(color: Colors.white38, fontSize: 10)),
        ]),
      ),
    );
  }

  Widget _buildDateChip(DateTime date) {
    final isSelected = _selectedDate != null &&
        _selectedDate!.day == date.day && _selectedDate!.month == date.month;
    return GestureDetector(
      onTap: () {
        setState(() {
          _selectedDate = date;
          if (_selectedTime != null && _isTimeInPast(_selectedTime!)) {
            _selectedTime = null;
          }
        });
        _loadBusyTimes();
      },
      child: Container(
        width: 52, margin: const EdgeInsets.only(right: 10),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.secondaryColor : const Color(0xFF2A2A2A),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: isSelected ? AppTheme.secondaryColor : const Color(0xFF3A3A3A)),
        ),
        child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
          Text('${date.day}', style: TextStyle(
            color: isSelected ? Colors.black : Colors.white,
            fontSize: 18, fontWeight: FontWeight.bold)),
          Text(DateFormat('E', 'tr_TR').format(date).substring(0, 2), style: TextStyle(
            color: isSelected ? Colors.black54 : Colors.white38, fontSize: 11)),
        ]),
      ),
    );
  }

  Widget _buildServiceCard(ServiceModel service) {
    final isSelected = _selectedServices.any((s) => s.id == service.id);
    return GestureDetector(
      onTap: () {
        setState(() {
          if (isSelected) {
            _selectedServices.removeWhere((s) => s.id == service.id);
          } else {
            _selectedServices.add(service);
          }
          if (_selectedTime != null && !_isSlotAvailable(_selectedTime!)) {
            _selectedTime = null;
          }
        });
      },
      child: Container(
        margin: const EdgeInsets.only(left: 12, right: 12, bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected ? const Color(0xFF353535) : const Color(0xFF202020),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor : const Color(0xFF2C2C2C),
            width: isSelected ? 1.5 : 1),
        ),
        child: Row(children: [
          ClipRRect(borderRadius: BorderRadius.circular(8),
            child: service.imageUrl != null
              ? Image.network(service.imageUrl!, width: 44, height: 44, fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(width: 44, height: 44,
                    color: const Color(0xFF3A3A3A),
                    child: const Icon(Icons.content_cut, color: Colors.white24, size: 20)))
              : Container(width: 44, height: 44, color: const Color(0xFF3A3A3A),
                  child: const Icon(Icons.content_cut, color: Colors.white24, size: 20))),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(service.name, style: const TextStyle(
              color: Colors.white, fontSize: 14, fontWeight: FontWeight.w600)),
            const SizedBox(height: 4),
            Row(children: [
              const Icon(Icons.access_time, color: Colors.white38, size: 12),
              const SizedBox(width: 4),
              Text('${service.durationMinutes} dk', style: const TextStyle(
                color: Colors.white38, fontSize: 11)),
            ]),
          ])),
          Text('₺${service.price.toStringAsFixed(0)}', style: TextStyle(
            color: isSelected ? AppTheme.secondaryColor : Colors.white,
            fontSize: 15, fontWeight: FontWeight.bold)),
          const SizedBox(width: 8),
          Icon(
            isSelected ? Icons.check_box : Icons.check_box_outline_blank,
            color: isSelected ? AppTheme.secondaryColor : Colors.white30,
            size: 20,
          ),
        ]),
      ),
    );
  }

  Widget _buildTimeChip(String time) {
    final isPast = _isTimeInPast(time);
    final isBusy = _busyTimes.contains(time) || isPast;
    final isAvailable = _isSlotAvailable(time);
    final isSelected = _selectedTime == time;
    final bool disabled = isBusy || !isAvailable;

    return GestureDetector(
      onTap: disabled ? null : () => setState(() => _selectedTime = time),
      child: Container(
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.secondaryColor
            : disabled
              ? (isPast ? Colors.white10 : AppTheme.secondaryColor.withOpacity(0.15))
              : const Color(0xFF2A2A2A),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor
              : disabled
                ? (isPast ? Colors.white10 : AppTheme.secondaryColor.withOpacity(0.3))
                : const Color(0xFF3A3A3A)),
        ),
        child: Center(child: Text(time, style: TextStyle(
          color: isSelected ? Colors.black
            : disabled
              ? (isPast ? Colors.white24 : AppTheme.secondaryColor.withOpacity(0.5))
              : Colors.white70,
          fontSize: 13, fontWeight: FontWeight.w600))),
      ),
    );
  }
}
