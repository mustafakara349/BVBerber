import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/core/enums.dart';
import 'package:mobile_android/models/barber_model.dart';
import 'package:mobile_android/models/service_model.dart';
import 'package:mobile_android/models/appointment_model.dart';
import 'package:mobile_android/providers/auth_provider.dart';
import 'package:mobile_android/providers/appointment_provider.dart';
import 'package:mobile_android/providers/campaign_provider.dart';
import 'package:mobile_android/screens/profile/coupons_screen.dart';
import 'package:mobile_android/services/api_service.dart';

enum BookingDiscountMode { campaign, coupon }

class CreateAppointmentScreen extends StatefulWidget {
  const CreateAppointmentScreen({super.key});

  @override
  State<CreateAppointmentScreen> createState() => _CreateAppointmentScreenState();
}

class _CreateAppointmentScreenState extends State<CreateAppointmentScreen> {
  List<BarberModel> _barbers = [];
  List<ServiceModel> _services = [];
  BarberModel? _selectedBarber;
  final Set<ServiceModel> _selectedServices = {};
  DateTime? _selectedDate;
  String? _selectedTime;
  List<String> _busyTimes = [];
  Set<String> _serverAvailableTimes = {};

  bool _isLoadingBarbers = true;
  bool _isLoadingServices = true;
  bool _isLoadingTimes = false;

  final Set<String> _expandedCategories = {};
  final List<String> _allTimes = [];

  @override
  void initState() {
    super.initState();
    // 08:00 - 21:30 arası 30 dakika aralıklarla saatler
    for (int h = 8; h <= 21; h++) {
      _allTimes.add('${h.toString().padLeft(2, '0')}:00');
      if (h < 21 || true) _allTimes.add('${h.toString().padLeft(2, '0')}:30');
    }

    _selectedDate = _availableDates.first;

    _loadBarbers();
    _loadServices();

    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<CampaignProvider>(context, listen: false).fetchCampaigns();
    });
  }

  Future<void> _loadBarbers() async {
    try {
      final data = await ApiService.getEmployees();
      _barbers = data.map((d) => BarberModel.fromMap(d)).toList();
      if (_barbers.isNotEmpty) {
        _selectedBarber = _barbers.first;
        _loadBusyTimes();
      }
    } catch (e) {
      debugPrint('Berber yüklenirken hata: $e');
    }
    if (mounted) setState(() => _isLoadingBarbers = false);
  }

  Future<void> _loadServices() async {
    try {
      final data = await ApiService.getServices(type: 'barber');
      _services = data.map((d) => ServiceModel.fromMap(d)).toList();
      if (_services.isNotEmpty) {
        final first = _services.first;
        _selectedServices.add(first);
        _expandedCategories.add(first.categoryName);
      }
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

  double get _totalPrice {
    return _selectedServices.fold(0.0, (sum, service) => sum + service.effectivePrice);
  }

  bool _isSlotAvailable(String time) {
    if (_isTimeInPast(time)) return false;
    if (_busyTimes.contains(time)) return false;

    // Sunucu müsaitlik kısıtı döndürdüyse doğrudan kontrol et
    if (_serverAvailableTimes.isNotEmpty && !_serverAvailableTimes.contains(time)) {
      return false;
    }

    final index = _allTimes.indexOf(time);
    if (index == -1) return false;

    final needed = _slotsNeeded;
    if (needed <= 0) return true;

    if (index + needed > _allTimes.length) return false;

    for (int k = 0; k < needed; k++) {
      final slotTime = _allTimes[index + k];
      if (_busyTimes.contains(slotTime) || _isTimeInPast(slotTime)) {
        return false;
      }
    }

    return true;
  }

  List<ServiceModel> get _popularServices {
    return _services.where((s) => s.isPopular == true || s.isFeatured == true).toList();
  }

  Map<String, List<ServiceModel>> get _groupedServices {
    final map = <String, List<ServiceModel>>{};
    for (final service in _services) {
      map.putIfAbsent(service.categoryName, () => []).add(service);
    }
    return map;
  }

  String _categoryDisplayName(String category) {
    switch (category.toLowerCase()) {
      case 'hair':
      case 'saç':
      case 'sac':
        return 'Saç Kesim & Tasarım';
      case 'shave':
      case 'sakal':
        return 'Sakal Tıraşı & Bakımı';
      case 'care':
      case 'cilt':
      case 'cilt bakımı':
      case 'bakım':
        return 'Cilt Bakımı & Maske';
      case 'hair_care':
      case 'saç bakımı':
        return 'Saç Bakımı & Fön';
      default:
        return category.isNotEmpty ? '${category[0].toUpperCase()}${category.substring(1)}' : category;
    }
  }

  Future<void> _loadBusyTimes() async {
    if (_selectedBarber == null || _selectedDate == null) return;
    setState(() {
      _isLoadingTimes = true;
      _selectedTime = null;
    });
    try {
      final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate!);
      final empId = int.tryParse(_selectedBarber!.id) ?? 1;
      
      final data = await ApiService.getAvailableSlots(
        employeeId: empId,
        date: dateStr,
        duration: _totalDuration > 0 ? _totalDuration : 30,
      );

      final busyList = <String>[];
      if (data['busy_times'] is List) {
        for (final item in data['busy_times']) {
          busyList.add(item.toString());
        }
      }

      final availableSet = <String>{};
      if (data['available_times'] is List) {
        for (final item in data['available_times']) {
          availableSet.add(item.toString());
        }
      }

      if (mounted) {
        setState(() {
          _busyTimes = busyList;
          _serverAvailableTimes = availableSet;
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

  bool get _canConfirm {
    return _selectedBarber != null &&
        _selectedServices.isNotEmpty &&
        _selectedDate != null &&
        _selectedTime != null;
  }

  void _toggleService(ServiceModel service) {
    setState(() {
      if (_selectedServices.contains(service)) {
        if (_selectedServices.length > 1) {
          _selectedServices.remove(service);
        }
      } else {
        _selectedServices.add(service);
      }
      _expandedCategories.add(service.categoryName);

      if (_selectedTime != null && !_isSlotAvailable(_selectedTime!)) {
        _selectedTime = null;
      }
    });
    _loadBusyTimes();
  }

  void _showBookingSummarySheet() {
    if (!_canConfirm) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Lütfen personel, hizmet, tarih ve saat alanlarını seçin.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _BookingSummarySheet(
        selectedBarber: _selectedBarber!,
        selectedServices: _selectedServices.toList(),
        selectedDate: _selectedDate!,
        selectedTime: _selectedTime!,
        totalDuration: _totalDuration,
        totalPrice: _totalPrice,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        centerTitle: true,
        title: const Text(
          'Randevu Oluştur',
          style: TextStyle(
            color: Colors.black87,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      bottomNavigationBar: Container(
        color: Colors.white,
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 10, 20, 10),
            child: SizedBox(
              height: 52,
              child: ElevatedButton(
                onPressed: _canConfirm ? _showBookingSummarySheet : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.secondaryColor,
                  foregroundColor: Colors.black,
                  disabledBackgroundColor: Colors.grey.shade300,
                  disabledForegroundColor: Colors.grey.shade500,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  elevation: 0,
                ),
                child: const Text('Randevuyu Onayla'),
              ),
            ),
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // 1. PERSONEL SEÇ
            _buildSectionHeader('Personel Seç'),
            const SizedBox(height: 14),
            _isLoadingBarbers
                ? const Center(child: Padding(padding: EdgeInsets.all(20), child: CircularProgressIndicator(color: AppTheme.secondaryColor)))
                : SizedBox(
                    height: 106,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      itemCount: _barbers.length,
                      itemBuilder: (_, i) => _buildBarberCard(_barbers[i]),
                    ),
                  ),
            const SizedBox(height: 26),

            // 2. TARİH SEÇİN
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildSectionHeader('Tarih Seçin'),
                if (_selectedDate != null)
                  Text(
                    DateFormat('MMMM yyyy', 'tr_TR').format(_selectedDate!),
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 14),
            SizedBox(
              height: 72,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: _availableDates.length,
                itemBuilder: (_, i) => _buildDateCard(_availableDates[i]),
              ),
            ),
            const SizedBox(height: 26),

            // 3. HİZMET SEÇİN
            _buildSectionHeader('Hizmet Seçin'),
            const SizedBox(height: 14),
            _isLoadingServices
                ? const Center(child: Padding(padding: EdgeInsets.all(20), child: CircularProgressIndicator(color: AppTheme.secondaryColor)))
                : Column(
                    children: [
                      // Popüler & Öne Çıkanlar
                      if (_popularServices.isNotEmpty) ...[
                        Row(
                          children: [
                            const Icon(Icons.star_rounded, color: AppTheme.secondaryColor, size: 20),
                            const SizedBox(width: 6),
                            Text(
                              'Popüler & Öne Çıkanlar',
                              style: TextStyle(
                                color: Colors.grey.shade700,
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        ..._popularServices.map((service) => _buildServiceCard(service)),
                        const SizedBox(height: 12),
                      ],

                      // Accordion Kategorileri
                      ..._groupedServices.entries.map((entry) {
                        final category = entry.key;
                        final categoryServices = entry.value;
                        final isExpanded = _expandedCategories.contains(category);
                        final displayName = _categoryDisplayName(category);

                        return Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: Colors.grey.shade200),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.02),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Column(
                            children: [
                              InkWell(
                                onTap: () {
                                  setState(() {
                                    if (isExpanded) {
                                      _expandedCategories.remove(category);
                                    } else {
                                      _expandedCategories.add(category);
                                    }
                                  });
                                },
                                borderRadius: BorderRadius.circular(16),
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          displayName,
                                          style: const TextStyle(
                                            color: Colors.black87,
                                            fontSize: 15,
                                            fontWeight: FontWeight.bold,
                                          ),
                                        ),
                                      ),
                                      Text(
                                        '${categoryServices.length} Hizmet',
                                        style: const TextStyle(color: Colors.black45, fontSize: 12),
                                      ),
                                      const SizedBox(width: 8),
                                      Icon(
                                        isExpanded ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                                        color: AppTheme.secondaryColor,
                                        size: 20,
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              if (isExpanded) ...[
                                const Divider(height: 1, indent: 16, endIndent: 16),
                                const SizedBox(height: 10),
                                ...categoryServices.map((service) => _buildServiceCard(service)),
                                const SizedBox(height: 6),
                              ],
                            ],
                          ),
                        );
                      }),
                    ],
                  ),
            const SizedBox(height: 26),

            // 4. SAAT SEÇİN
            if (_selectedDate != null) ...[
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _buildSectionHeader('Saat Seçin'),
                  Row(
                    children: [
                      Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: Colors.grey,
                        ),
                      ),
                      const SizedBox(width: 6),
                      const Text(
                        'DOLU',
                        style: TextStyle(
                          color: Colors.black45,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 14),
              _isLoadingTimes
                  ? const Center(child: Padding(padding: EdgeInsets.all(20), child: CircularProgressIndicator(color: AppTheme.secondaryColor)))
                  : GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 4,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                        childAspectRatio: 2.1,
                      ),
                      itemCount: _allTimes.length,
                      itemBuilder: (_, i) => _buildTimeCard(_allTimes[i]),
                    ),
            ],
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Text(
      title,
      style: const TextStyle(
        color: Colors.black87,
        fontSize: 17,
        fontWeight: FontWeight.bold,
        letterSpacing: -0.3,
      ),
    );
  }

  Widget _buildBarberCard(BarberModel barber) {
    final isSelected = _selectedBarber?.id == barber.id;
    final initials = barber.initials;

    return GestureDetector(
      onTap: () {
        setState(() => _selectedBarber = barber);
        _loadBusyTimes();
      },
      child: Container(
        width: 76,
        margin: const EdgeInsets.only(right: 12),
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.secondaryColor.withOpacity(0.12) : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor : Colors.grey.shade200,
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isSelected ? AppTheme.secondaryColor.withOpacity(0.2) : Colors.grey.shade100,
                border: Border.all(
                  color: isSelected ? AppTheme.secondaryColor : Colors.grey.shade300,
                  width: 1.5,
                ),
              ),
              child: barber.profileImageUrl != null
                  ? ClipOval(
                      child: Image.network(
                        barber.profileImageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Center(
                          child: Text(
                            initials,
                            style: TextStyle(
                              color: isSelected ? AppTheme.secondaryColor : Colors.black87,
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      ),
                    )
                  : Center(
                      child: Text(
                        initials,
                        style: TextStyle(
                          color: isSelected ? AppTheme.secondaryColor : Colors.black87,
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                        ),
                      ),
                    ),
            ),
            const SizedBox(height: 6),
            Text(
              barber.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: Colors.black87,
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.star_rounded, color: AppTheme.secondaryColor, size: 13),
                const SizedBox(width: 2),
                Text(
                  barber.rating.toStringAsFixed(1),
                  style: const TextStyle(color: Colors.black54, fontSize: 10, fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDateCard(DateTime date) {
    final isSelected = _selectedDate != null &&
        _selectedDate!.day == date.day &&
        _selectedDate!.month == date.month;

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
        width: 56,
        margin: const EdgeInsets.only(right: 10),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.secondaryColor : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor : Colors.grey.shade200,
            width: isSelected ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.02),
              blurRadius: 4,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              DateFormat('E', 'tr_TR').format(date).toUpperCase(),
              style: TextStyle(
                color: isSelected ? Colors.black87 : Colors.black45,
                fontSize: 11,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              '${date.day}',
              style: TextStyle(
                color: isSelected ? Colors.black : Colors.black87,
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              DateFormat('MMM', 'tr_TR').format(date),
              style: TextStyle(
                color: isSelected ? Colors.black87 : Colors.black45,
                fontSize: 10,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildServiceCard(ServiceModel service) {
    final isSelected = _selectedServices.contains(service);

    return GestureDetector(
      onTap: () => _toggleService(service),
      child: Container(
        margin: const EdgeInsets.only(left: 12, right: 12, bottom: 8),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected ? AppTheme.secondaryColor.withOpacity(0.08) : const Color(0xFFF9FAFB),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected ? AppTheme.secondaryColor : Colors.grey.shade200,
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(10),
              child: service.imageUrl != null
                  ? Image.network(
                      service.imageUrl!,
                      width: 48,
                      height: 48,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        width: 48,
                        height: 48,
                        color: Colors.grey.shade100,
                        child: const Icon(Icons.content_cut, color: Colors.black26, size: 22),
                      ),
                    )
                  : Container(
                      width: 48,
                      height: 48,
                      color: Colors.grey.shade100,
                      child: const Icon(Icons.content_cut, color: Colors.black26, size: 22),
                    ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    service.name,
                    style: TextStyle(
                      color: isSelected ? Colors.black87 : Colors.black87,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.access_time_rounded, color: Colors.black38, size: 13),
                      const SizedBox(width: 4),
                      Text(
                        '${service.durationMinutes} dk',
                        style: const TextStyle(color: Colors.black54, fontSize: 12),
                      ),
                      const SizedBox(width: 8),
                      Text('•', style: TextStyle(color: Colors.grey.shade400)),
                      const SizedBox(width: 8),
                      Text(
                        _categoryDisplayName(service.categoryName),
                        style: const TextStyle(color: Colors.black54, fontSize: 12),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                if (service.hasDiscount) ...[
                  Text(
                    '₺${service.price.toStringAsFixed(0)}',
                    style: const TextStyle(
                      color: Colors.black38,
                      fontSize: 11,
                      decoration: TextDecoration.lineThrough,
                    ),
                  ),
                  Text(
                    '₺${service.effectivePrice.toStringAsFixed(0)}',
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ] else ...[
                  Text(
                    '₺${service.price.toStringAsFixed(0)}',
                    style: TextStyle(
                      color: isSelected ? AppTheme.secondaryColor : Colors.black87,
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ],
            ),
            const SizedBox(width: 10),
            Icon(
              isSelected ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
              color: isSelected ? AppTheme.secondaryColor : Colors.grey.shade300,
              size: 22,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTimeCard(String time) {
    final isDark = AppTheme.isDark(context);
    final isPast = _isTimeInPast(time);
    final isOccupied = _busyTimes.contains(time);
    final isAvailable = _isSlotAvailable(time);
    final isSelected = _selectedTime == time;
    final bool disabled = isPast || isOccupied || !isAvailable;

    Color bgColor;
    Color borderColor;
    Color textColor;
    TextDecoration? textDecoration;

    if (isSelected) {
      bgColor = AppTheme.secondaryColor;
      borderColor = AppTheme.secondaryColor;
      textColor = Colors.black;
    } else if (disabled) {
      if (isOccupied) {
        bgColor = isDark ? const Color(0xFF1C1A1A) : const Color(0xFFFEE2E2).withOpacity(0.4);
        borderColor = isDark ? const Color(0xFF3A2020) : const Color(0xFFFCA5A5).withOpacity(0.5);
        textColor = isDark ? Colors.white30 : Colors.red.shade300;
        textDecoration = TextDecoration.lineThrough;
      } else {
        bgColor = isDark ? const Color(0xFF18181B) : Colors.grey.shade100;
        borderColor = isDark ? const Color(0xFF27272A) : Colors.grey.shade200;
        textColor = isDark ? Colors.white24 : Colors.black26;
      }
    } else {
      bgColor = isDark ? const Color(0xFF1C1C1E) : Colors.white;
      borderColor = isDark ? const Color(0xFF2C2C30) : Colors.grey.shade300;
      textColor = isDark ? Colors.white : Colors.black87;
    }

    return GestureDetector(
      onTap: disabled ? null : () => setState(() => _selectedTime = time),
      child: Container(
        decoration: BoxDecoration(
          color: bgColor,
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
            color: borderColor,
            width: isSelected ? 2 : 1,
          ),
        ),
        child: Center(
          child: Text(
            time,
            style: TextStyle(
              color: textColor,
              fontSize: 13,
              fontWeight: isSelected ? FontWeight.bold : FontWeight.w600,
              decoration: textDecoration,
            ),
          ),
        ),
      ),
    );
  }
}

/// iOS BookingSummarySheet counterpart
class _BookingSummarySheet extends StatefulWidget {
  final BarberModel selectedBarber;
  final List<ServiceModel> selectedServices;
  final DateTime selectedDate;
  final String selectedTime;
  final int totalDuration;
  final double totalPrice;

  const _BookingSummarySheet({
    required this.selectedBarber,
    required this.selectedServices,
    required this.selectedDate,
    required this.selectedTime,
    required this.totalDuration,
    required this.totalPrice,
  });

  @override
  State<_BookingSummarySheet> createState() => _BookingSummarySheetState();
}

class _BookingSummarySheetState extends State<_BookingSummarySheet> {
  BookingDiscountMode _discountMode = BookingDiscountMode.campaign;
  final TextEditingController _couponController = TextEditingController();

  String? _selectedCampaignId;
  bool _isValidatingDiscount = false;
  bool? _isCouponValid;
  String _couponMessage = '';
  double _validatedDiscountAmount = 0.0;
  String? _rewardProductName;

  bool _isCreating = false;

  @override
  void dispose() {
    _couponController.dispose();
    super.dispose();
  }

  Future<void> _validateDiscount({String? code, String? campaignId}) async {
    setState(() {
      _isValidatingDiscount = true;
      _couponMessage = '';
    });

    try {
      final serviceIds = widget.selectedServices.map((s) => int.parse(s.id)).toList();
      final res = await ApiService.validateCoupon(
        code: code,
        campaignId: campaignId,
        subtotal: widget.totalPrice,
        serviceIds: serviceIds,
      );

      setState(() {
        _isCouponValid = true;
        _validatedDiscountAmount = (res['discountAmount'] as num?)?.toDouble() ?? 0.0;
        _couponMessage = res['message'] ?? 'İndirim başarıyla uygulandı.';
        _rewardProductName = res['rewardProductName'];
      });
    } catch (e) {
      setState(() {
        _isCouponValid = false;
        _validatedDiscountAmount = 0.0;
        _rewardProductName = null;
        _couponMessage = e.toString().replaceAll('Exception: ', '');
      });
    } finally {
      if (mounted) setState(() => _isValidatingDiscount = false);
    }
  }

  Future<void> _handleConfirm() async {
    setState(() => _isCreating = true);
    final apptProvider = Provider.of<AppointmentProvider>(context, listen: false);
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;

    if (user == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Lütfen önce oturum açın.'), backgroundColor: AppTheme.errorColor),
      );
      setState(() => _isCreating = false);
      return;
    }

    try {
      final timeParts = widget.selectedTime.split(':');
      final apptDateTime = DateTime(
        widget.selectedDate.year,
        widget.selectedDate.month,
        widget.selectedDate.day,
        int.parse(timeParts[0]),
        int.parse(timeParts[1]),
      );

      final serviceIds = widget.selectedServices.map((s) => int.parse(s.id)).toList();

      final appointment = AppointmentModel(
        id: '',
        customerId: user.id,
        barberId: widget.selectedBarber.id,
        serviceId: widget.selectedServices.first.id,
        dateTime: apptDateTime,
        status: AppointmentStatus.pending,
        createdAt: DateTime.now(),
      );

      final couponCode = _discountMode == BookingDiscountMode.coupon ? _couponController.text.trim() : null;

      await apptProvider.createAppointment(
        appointment,
        serviceIds: serviceIds,
        couponCode: couponCode,
        discountAmount: _validatedDiscountAmount > 0 ? _validatedDiscountAmount : null,
      );

      if (mounted) {
        Navigator.pop(context); // Close summary sheet
        Navigator.pop(context, true); // Pop appointment screen
        apptProvider.loadAppointments();
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Randevunuz başarıyla oluşturuldu!'),
            backgroundColor: AppTheme.successColor,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Randevu oluşturulamadı: ${e.toString().replaceAll('Exception: ', '')}'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isCreating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final dateStr = DateFormat('d MMMM, EEEE', 'tr_TR').format(widget.selectedDate);
    final serviceNames = widget.selectedServices.map((s) => s.name).join(' + ');
    final finalPrice = (widget.totalPrice - _validatedDiscountAmount).clamp(0.0, double.infinity);
    final campaignProvider = Provider.of<CampaignProvider>(context);

    return Container(
      constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.88),
      padding: const EdgeInsets.fromLTRB(22, 12, 22, 24),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Drag handle
          Container(
            width: 44,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey.shade300,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 16),

          // Header with edit button
          Row(
            children: [
              const Text(
                'Randevu Özeti',
                style: TextStyle(
                  color: Colors.black87,
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const Spacer(),
              InkWell(
                onTap: () => Navigator.pop(context),
                borderRadius: BorderRadius.circular(8),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: AppTheme.secondaryColor.withOpacity(0.85),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text(
                    'Randevuyu Düzenle',
                    style: TextStyle(color: Colors.black, fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(height: 1),

          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(vertical: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _detailRow(Icons.calendar_today_rounded, 'Tarih', dateStr),
                  const Divider(height: 1, indent: 36),
                  _detailRow(Icons.access_time_filled_rounded, 'Saat', widget.selectedTime),
                  const Divider(height: 1, indent: 36),
                  _detailRow(Icons.person_rounded, 'Personel', widget.selectedBarber.fullName),
                  const Divider(height: 1, indent: 36),
                  _detailRow(Icons.content_cut_rounded, 'Hizmet', serviceNames),
                  const Divider(height: 1, indent: 36),
                  _detailRow(Icons.timer_rounded, 'Toplam Süre', '${widget.totalDuration} dk'),
                  const Divider(height: 1, indent: 36),

                  // Toplam Ücret
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    child: Row(
                      children: [
                        const Icon(Icons.payments_rounded, color: AppTheme.secondaryColor, size: 22),
                        const SizedBox(width: 14),
                        const Text('Toplam Ücret', style: TextStyle(color: Colors.black54, fontSize: 14)),
                        const Spacer(),
                        if (_isCouponValid == true && _validatedDiscountAmount > 0) ...[
                          Text(
                            '₺${widget.totalPrice.toStringAsFixed(0)}',
                            style: const TextStyle(
                              color: Colors.black38,
                              fontSize: 14,
                              decoration: TextDecoration.lineThrough,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '₺${finalPrice.toStringAsFixed(0)}',
                            style: const TextStyle(
                              color: AppTheme.successColor,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ] else ...[
                          Text(
                            '₺${widget.totalPrice.toStringAsFixed(0)}',
                            style: const TextStyle(
                              color: AppTheme.secondaryColor,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),

                  // Hediye Rozeti
                  if (_rewardProductName != null) ...[
                    const Divider(height: 1, indent: 36),
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      child: Row(
                        children: [
                          const Icon(Icons.card_giftcard_rounded, color: Colors.pinkAccent, size: 22),
                          const SizedBox(width: 14),
                          const Text('Kampanya Hediyesi', style: TextStyle(color: Colors.black54, fontSize: 14)),
                          const Spacer(),
                          Text(
                            _rewardProductName!,
                            style: const TextStyle(color: Colors.pinkAccent, fontSize: 14, fontWeight: FontWeight.bold),
                          ),
                        ],
                      ),
                    ),
                  ],

                  const Divider(height: 1),
                  const SizedBox(height: 16),

                  // İndirim Seçimi Segmenti
                  Container(
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.all(4),
                    child: Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: () {
                              setState(() {
                                _discountMode = BookingDiscountMode.campaign;
                                _couponController.clear();
                                _isCouponValid = null;
                                _couponMessage = '';
                                _validatedDiscountAmount = 0.0;
                                _rewardProductName = null;
                              });
                            },
                            borderRadius: BorderRadius.circular(10),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              decoration: BoxDecoration(
                                color: _discountMode == BookingDiscountMode.campaign ? Colors.white : Colors.transparent,
                                borderRadius: BorderRadius.circular(10),
                                boxShadow: _discountMode == BookingDiscountMode.campaign
                                    ? [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 4, offset: const Offset(0, 2))]
                                    : null,
                              ),
                              child: Center(
                                child: Text(
                                  'Kampanya Kullan',
                                  style: TextStyle(
                                    color: _discountMode == BookingDiscountMode.campaign ? Colors.black87 : Colors.black54,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                        Expanded(
                          child: InkWell(
                            onTap: () {
                              setState(() {
                                _discountMode = BookingDiscountMode.coupon;
                                _selectedCampaignId = null;
                                _isCouponValid = null;
                                _couponMessage = '';
                                _validatedDiscountAmount = 0.0;
                                _rewardProductName = null;
                              });
                            },
                            borderRadius: BorderRadius.circular(10),
                            child: Container(
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              decoration: BoxDecoration(
                                color: _discountMode == BookingDiscountMode.coupon ? Colors.white : Colors.transparent,
                                borderRadius: BorderRadius.circular(10),
                                boxShadow: _discountMode == BookingDiscountMode.coupon
                                    ? [BoxShadow(color: Colors.black.withOpacity(0.06), blurRadius: 4, offset: const Offset(0, 2))]
                                    : null,
                              ),
                              child: Center(
                                child: Text(
                                  'Kupon Kodu Gir',
                                  style: TextStyle(
                                    color: _discountMode == BookingDiscountMode.coupon ? Colors.black87 : Colors.black54,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // KAMPANYA SEÇİMİ
                  if (_discountMode == BookingDiscountMode.campaign) ...[
                    if (campaignProvider.campaigns.isEmpty)
                      const Padding(
                        padding: EdgeInsets.symmetric(vertical: 8),
                        child: Text(
                          'Size özel aktif bir kampanya bulunmuyor.',
                          style: TextStyle(color: Colors.black45, fontSize: 13),
                        ),
                      )
                    else
                      SizedBox(
                        height: 110,
                        child: ListView.builder(
                          scrollDirection: Axis.horizontal,
                          itemCount: campaignProvider.campaigns.length,
                          itemBuilder: (_, i) {
                            final campaign = campaignProvider.campaigns[i];
                            final isSel = _selectedCampaignId == campaign.id;

                            return GestureDetector(
                              onTap: () {
                                if (isSel) {
                                  setState(() {
                                    _selectedCampaignId = null;
                                    _isCouponValid = null;
                                    _couponMessage = '';
                                    _validatedDiscountAmount = 0.0;
                                    _rewardProductName = null;
                                  });
                                } else {
                                  setState(() => _selectedCampaignId = campaign.id);
                                  _validateDiscount(campaignId: campaign.id);
                                }
                              },
                              child: Container(
                                width: 220,
                                margin: const EdgeInsets.only(right: 12),
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: isSel ? AppTheme.secondaryColor.withOpacity(0.15) : const Color(0xFFF9FAFB),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(
                                    color: isSel ? AppTheme.secondaryColor : Colors.grey.shade200,
                                    width: isSel ? 1.5 : 1,
                                  ),
                                ),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Text(
                                            campaign.title,
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                            style: TextStyle(
                                              color: Colors.black87,
                                              fontSize: 13,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                        if (isSel)
                                          const Icon(Icons.check_circle, color: AppTheme.secondaryColor, size: 18),
                                      ],
                                    ),
                                    const SizedBox(height: 6),
                                    Text(
                                      campaign.description,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(color: Colors.black54, fontSize: 11),
                                    ),
                                    const Spacer(),
                                    Text(
                                      isSel ? 'Seçildi (Kaldırmak için dokunun)' : 'Seçmek için dokunun',
                                      style: TextStyle(
                                        color: isSel ? AppTheme.secondaryColor : Colors.black45,
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
                  ] else ...[
                    // KUPON KODU GİRİŞİ
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: _couponController,
                            textCapitalization: TextCapitalization.characters,
                            decoration: InputDecoration(
                              hintText: 'Kupon Kodu',
                              hintStyle: const TextStyle(color: Colors.black38),
                              prefixIcon: const Icon(Icons.confirmation_number_outlined, color: Colors.black45, size: 20),
                              filled: true,
                              fillColor: Colors.grey.shade100,
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                              border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                            ),
                          ),
                        ),
                        const SizedBox(width: 10),
                        ElevatedButton(
                          onPressed: _isValidatingDiscount || _couponController.text.trim().isEmpty
                              ? null
                              : () => _validateDiscount(code: _couponController.text.trim()),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.secondaryColor,
                            foregroundColor: Colors.black,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
                            elevation: 0,
                          ),
                          child: _isValidatingDiscount
                              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                              : const Text('Uygula', style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),

                    // Kuponlarımı Gör Butonu
                    InkWell(
                      onTap: () async {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => CouponsScreen(
                              onSelectCoupon: (selectedCode) {
                                _couponController.text = selectedCode;
                                _validateDiscount(code: selectedCode);
                              },
                            ),
                          ),
                        );
                      },
                      borderRadius: BorderRadius.circular(8),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(vertical: 4),
                        child: Row(
                          children: const [
                            Icon(Icons.confirmation_number_rounded, color: AppTheme.secondaryColor, size: 16),
                            SizedBox(width: 6),
                            Text(
                              'Kuponlarımı Gör',
                              style: TextStyle(
                                color: AppTheme.secondaryColor,
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Spacer(),
                            Icon(Icons.chevron_right, color: AppTheme.secondaryColor, size: 18),
                          ],
                        ),
                      ),
                    ),
                  ],

                  if (_couponMessage.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      _couponMessage,
                      style: TextStyle(
                        color: _isCouponValid == true ? AppTheme.successColor : AppTheme.errorColor,
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),

          const SizedBox(height: 12),

          // Onay Butonu
          SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton(
              onPressed: _isCreating ? null : _handleConfirm,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                elevation: 0,
              ),
              child: _isCreating
                  ? const SizedBox(width: 22, height: 22, child: CircularProgressIndicator(color: Colors.black, strokeWidth: 2.5))
                  : const Text('Randevuyu Onayla'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _detailRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: AppTheme.secondaryColor, size: 20),
          const SizedBox(width: 14),
          Text(label, style: const TextStyle(color: Colors.black54, fontSize: 14)),
          const Spacer(),
          Expanded(
            flex: 2,
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: const TextStyle(
                color: Colors.black87,
                fontSize: 14,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
