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
  ServiceModel? _selectedService;
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

  Future<void> _loadBusyTimes() async {
    if (_selectedBarber == null || _selectedDate == null) return;
    setState(() { _isLoadingTimes = true; _selectedTime = null; });
    try {
      _busyTimes = [];
    } catch (_) {}
    if (mounted) setState(() => _isLoadingTimes = false);
  }

  List<DateTime> get _availableDates {
    final now = DateTime.now();
    return List.generate(30, (i) => DateTime(now.year, now.month, now.day + i + 1));
  }

  void _showConfirmationSheet() {
    if (_selectedBarber == null || _selectedService == null ||
        _selectedDate == null || _selectedTime == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Lütfen tüm alanları seçin'), backgroundColor: Colors.orange));
      return;
    }

    final dateStr = DateFormat('d MMMM EEEE', 'tr_TR').format(_selectedDate!);

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
          _summaryRow(Icons.content_cut, 'Hizmet', _selectedService!.name),
          _summaryRow(Icons.monetization_on_outlined, 'Ücret',
            '₺${_selectedService!.price.toStringAsFixed(0)}'),
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
        Text(value, style: const TextStyle(
          color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
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
        serviceId: _selectedService!.id,
        dateTime: appointmentDT,
        status: AppointmentStatus.pending,
        createdAt: DateTime.now(),
      );

      await Provider.of<AppointmentProvider>(context, listen: false)
          .createAppointment(appointment);

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
            : Column(children: _services.map((s) => _buildServiceCard(s)).toList()),
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
        setState(() => _selectedDate = date);
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
    final isSelected = _selectedService?.id == service.id;
    return GestureDetector(
      onTap: () => setState(() => _selectedService = service),
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: const Color(0xFF2A2A2A),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor : const Color(0xFF3A3A3A),
            width: isSelected ? 2 : 1),
        ),
        child: Row(children: [
          ClipRRect(borderRadius: BorderRadius.circular(10),
            child: service.imageUrl != null
              ? Image.network(service.imageUrl!, width: 50, height: 50, fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => Container(width: 50, height: 50,
                    color: const Color(0xFF3A3A3A),
                    child: const Icon(Icons.content_cut, color: Colors.white24)))
              : Container(width: 50, height: 50, color: const Color(0xFF3A3A3A),
                  child: const Icon(Icons.content_cut, color: Colors.white24))),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(service.name, style: const TextStyle(
              color: Colors.white, fontSize: 15, fontWeight: FontWeight.w600)),
            const SizedBox(height: 4),
            Row(children: [
              const Icon(Icons.access_time, color: Colors.white38, size: 13),
              const SizedBox(width: 4),
              Text('${service.durationMinutes} dk', style: const TextStyle(
                color: Colors.white38, fontSize: 12)),
              const SizedBox(width: 8),
              const Text('·', style: TextStyle(color: Colors.white24)),
              const SizedBox(width: 8),
              const Text('Hair', style: TextStyle(color: Colors.white38, fontSize: 12)),
            ]),
          ])),
          Text('₺${service.price.toStringAsFixed(0)}', style: TextStyle(
            color: isSelected ? AppTheme.secondaryColor : Colors.white,
            fontSize: 16, fontWeight: FontWeight.bold)),
          if (isSelected) ...[
            const SizedBox(width: 8),
            const Icon(Icons.check_circle, color: AppTheme.secondaryColor, size: 22),
          ],
        ]),
      ),
    );
  }

  Widget _buildTimeChip(String time) {
    final isBusy = _busyTimes.contains(time);
    final isSelected = _selectedTime == time;
    return GestureDetector(
      onTap: isBusy ? null : () => setState(() => _selectedTime = time),
      child: Container(
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.secondaryColor
            : isBusy ? AppTheme.secondaryColor.withOpacity(0.15) : const Color(0xFF2A2A2A),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor
              : isBusy ? AppTheme.secondaryColor.withOpacity(0.3) : const Color(0xFF3A3A3A)),
        ),
        child: Center(child: Text(time, style: TextStyle(
          color: isSelected ? Colors.black
            : isBusy ? AppTheme.secondaryColor.withOpacity(0.5) : Colors.white70,
          fontSize: 13, fontWeight: FontWeight.w600))),
      ),
    );
  }
}
