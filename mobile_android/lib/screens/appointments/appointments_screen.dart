import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/core/enums.dart';
import 'package:mobile_android/models/appointment_model.dart';
import 'package:mobile_android/providers/appointment_provider.dart';
import 'package:mobile_android/routes/app_routes.dart';

import 'package:url_launcher/url_launcher.dart';
import 'package:mobile_android/services/api_service.dart';

class AppointmentsScreen extends StatefulWidget {
  const AppointmentsScreen({super.key});

  @override
  State<AppointmentsScreen> createState() => _AppointmentsScreenState();
}

class _AppointmentsScreenState extends State<AppointmentsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<AppointmentProvider>(context, listen: false).loadAppointments();
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A1A1A),
      body: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Randevularım', style: TextStyle(
                    color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold)),
                  InkWell(
                    onTap: () async {
                      final result = await Navigator.pushNamed(context, AppRoutes.createAppointment);
                      if (result == true && mounted) {
                        Provider.of<AppointmentProvider>(context, listen: false).loadAppointments();
                      }
                    },
                    borderRadius: BorderRadius.circular(21),
                    child: Container(
                      width: 42, height: 42,
                      decoration: const BoxDecoration(
                        shape: BoxShape.circle, color: AppTheme.secondaryColor),
                      child: const Icon(Icons.add, color: Colors.black, size: 24),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: TabBar(
                controller: _tabController,
                indicatorColor: AppTheme.secondaryColor,
                indicatorWeight: 3,
                indicatorSize: TabBarIndicatorSize.tab,
                labelColor: Colors.white,
                unselectedLabelColor: Colors.white38,
                labelStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                unselectedLabelStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
                dividerColor: Colors.transparent,
                tabs: const [Tab(text: 'Yaklaşan'), Tab(text: 'Geçmiş')],
              ),
            ),
            Expanded(
              child: Consumer<AppointmentProvider>(
                builder: (context, provider, _) {
                  if (provider.isLoading) {
                    return const Center(
                      child: CircularProgressIndicator(color: AppTheme.secondaryColor));
                  }
                  return TabBarView(
                    controller: _tabController,
                    children: [
                      _buildList(provider.upcomingAppointments,
                        'Henüz Yaklaşan Randevunuz Yok',
                        'Bakımınızı planlamak için hemen yeni bir\nrandevu oluşturabilirsiniz.', true),
                      _buildList(provider.pastAppointments,
                        'Geçmiş Randevunuz Bulunmuyor',
                        'Daha önce herhangi bir randevu geçmişiniz\nbulunmamaktadır.', false),
                    ],
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildList(List<AppointmentModel> items, String emptyTitle, String emptyDesc, bool isUpcoming) {
    if (items.isEmpty) return _buildEmpty(emptyTitle, emptyDesc);
    return RefreshIndicator(
      onRefresh: () => Provider.of<AppointmentProvider>(context, listen: false).loadAppointments(),
      color: AppTheme.secondaryColor,
      backgroundColor: const Color(0xFF2A2A2A),
      child: ListView.builder(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        itemCount: items.length,
        itemBuilder: (_, i) => _AppointmentCard(appointment: items[i], isUpcoming: isUpcoming),
      ),
    );
  }

  Widget _buildEmpty(String title, String description) {
    return Center(child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(width: 70, height: 70,
          decoration: BoxDecoration(color: const Color(0xFF2A2A2A), borderRadius: BorderRadius.circular(16)),
          child: Stack(alignment: Alignment.center, children: [
            const Icon(Icons.calendar_month, color: Colors.white24, size: 36),
            Positioned(bottom: 10, right: 10, child: Container(
              width: 18, height: 18,
              decoration: const BoxDecoration(shape: BoxShape.circle, color: Color(0xFF1A1A1A)),
              child: const Icon(Icons.error_outline, color: Colors.white38, size: 14),
            )),
          ])),
        const SizedBox(height: 24),
        Text(title, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold), textAlign: TextAlign.center),
        const SizedBox(height: 10),
        Text(description, style: const TextStyle(color: Colors.white38, fontSize: 14, height: 1.5), textAlign: TextAlign.center),
      ],
    ));
  }
}

/// Randevu kartı – Firestore'dan hizmet ve berber bilgilerini çeker
class _AppointmentCard extends StatelessWidget {
  final AppointmentModel appointment;
  final bool isUpcoming;
  const _AppointmentCard({required this.appointment, required this.isUpcoming});

  void _showSnackBar(ScaffoldMessengerState messenger, String msg, Color color) {
    messenger.showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  Future<void> _addToGoogleCalendar(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    final startUtc = appointment.dateTime.toUtc();
    final duration = 30; // Varsayılan 30 dk
    final endUtc = startUtc.add(Duration(minutes: duration));

    final startStr = _formatGoogleDate(startUtc);
    final endStr = _formatGoogleDate(endUtc);

    final title = 'B&V Barber Randevusu - ${appointment.barberName ?? 'Usta'}';
    final details = 'Alınan Hizmet: ${appointment.serviceName ?? 'Berberlik Hizmeti'}';
    final location = 'B&V Barber & Coffee, Tarsus/Mersin';

    final googleUrl = 'https://calendar.google.com/calendar/render'
        '?action=TEMPLATE'
        '&text=${Uri.encodeComponent(title)}'
        '&dates=${startStr}/${endStr}'
        '&details=${Uri.encodeComponent(details)}'
        '&location=${Uri.encodeComponent(location)}';

    final uri = Uri.parse(googleUrl);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        _showSnackBar(messenger, 'Google Takvim açılamadı.', AppTheme.errorColor);
      }
    } catch (e) {
      _showSnackBar(messenger, 'Google Takvim açılamadı: $e', AppTheme.errorColor);
    }
  }

  Future<void> _addToAppleCalendar(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    if (appointment.icalUrl == null || appointment.icalUrl!.isEmpty) {
      _showSnackBar(messenger, 'Takvim indirme linki alınamadı.', AppTheme.errorColor);
      return;
    }

    final uri = Uri.parse(appointment.icalUrl!);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        _showSnackBar(messenger, 'Cihaz takvimi başlatılamadı.', AppTheme.errorColor);
      }
    } catch (e) {
      _showSnackBar(messenger, 'Cihaz takvimi başlatılamadı: $e', AppTheme.errorColor);
    }
  }

  String _formatGoogleDate(DateTime dt) {
    return '${dt.year.toString().padLeft(4, '0')}'
        '${dt.month.toString().padLeft(2, '0')}'
        '${dt.day.toString().padLeft(2, '0')}T'
        '${dt.hour.toString().padLeft(2, '0')}'
        '${dt.minute.toString().padLeft(2, '0')}'
        '${dt.second.toString().padLeft(2, '0')}Z';
  }

  void _showCalendarOptions(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: const Color(0xFF2A2A2A),
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (BuildContext bc) {
        return SafeArea(
          child: Wrap(
            children: <Widget>[
              const Padding(
                padding: EdgeInsets.all(20.0),
                child: Text(
                  'Takvime Ekle',
                  style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                ),
              ),
              ListTile(
                leading: const Icon(Icons.calendar_month, color: AppTheme.secondaryColor),
                title: const Text('Google Takvim (Web/Uygulama)', style: TextStyle(color: Colors.white)),
                onTap: () {
                  Navigator.pop(bc);
                  _addToGoogleCalendar(context);
                },
              ),
              ListTile(
                leading: const Icon(Icons.download, color: AppTheme.secondaryColor),
                title: const Text('Apple / Cihaz Takvimi (.ics Dosyası)', style: TextStyle(color: Colors.white)),
                onTap: () {
                  Navigator.pop(bc);
                  _addToAppleCalendar(context);
                },
              ),
              const SizedBox(height: 20),
            ],
          ),
        );
      },
    );
  }

  void _showReviewDialog(BuildContext context) {
    int selectedRating = 5;
    final commentController = TextEditingController();

    showDialog(
      context: context,
      builder: (BuildContext ctx) {
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              backgroundColor: const Color(0xFF2A2A2A),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: const Text(
                'Randevuyu Değerlendir',
                style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text(
                      'Hizmet kalitesini ve berberinizi puanlayın:',
                      style: TextStyle(color: Colors.white70, fontSize: 13),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(5, (index) {
                        final starValue = index + 1;
                        return GestureDetector(
                          onTap: () {
                            setState(() {
                              selectedRating = starValue;
                            });
                          },
                          child: Icon(
                            starValue <= selectedRating ? Icons.star : Icons.star_border,
                            color: AppTheme.secondaryColor,
                            size: 36,
                          ),
                        );
                      }),
                    ),
                    const SizedBox(height: 20),
                    TextField(
                      controller: commentController,
                      style: const TextStyle(color: Colors.white),
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: 'Yorumunuzu buraya yazabilirsiniz (isteğe bağlı)...',
                        hintStyle: const TextStyle(color: Colors.white30),
                        fillColor: const Color(0xFF1A1A1A),
                        filled: true,
                        focusedBorder: OutlineInputBorder(
                          borderSide: const BorderSide(color: AppTheme.secondaryColor),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderSide: const BorderSide(color: Colors.white12),
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(ctx),
                  child: const Text('Vazgeç', style: TextStyle(color: Colors.white54)),
                ),
                ElevatedButton(
                  onPressed: () async {
                    final messenger = ScaffoldMessenger.of(context);
                    final apptProvider = Provider.of<AppointmentProvider>(context, listen: false);
                    try {
                      final success = await ApiService.createReview(
                        appointmentId: appointment.id,
                        rating: selectedRating,
                        comment: commentController.text.trim().isEmpty ? null : commentController.text.trim(),
                      );
                      if (success) {
                        Navigator.pop(ctx);
                        _showSnackBar(messenger, 'Değerlendirmeniz başarıyla gönderildi.', AppTheme.successColor);
                        apptProvider.markAsReviewed(appointment.id, selectedRating);
                        apptProvider.loadAppointments();
                      }
                    } catch (e) {
                      _showSnackBar(messenger, e.toString().replaceAll('Exception: ', ''), AppTheme.errorColor);
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.secondaryColor,
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text('Gönder', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final dateStr = DateFormat('d MMMM, EEEE · HH:mm', 'tr_TR').format(appointment.dateTime);

    Color statusColor;
    String statusText;
    switch (appointment.status) {
      case AppointmentStatus.pending:
        statusColor = Colors.orange; statusText = 'Beklemede'; break;
      case AppointmentStatus.confirmed:
        statusColor = AppTheme.successColor; statusText = 'Aktif'; break;
      case AppointmentStatus.cancelled:
        statusColor = AppTheme.errorColor; statusText = 'İptal'; break;
      case AppointmentStatus.completed:
        statusColor = Colors.blueGrey; statusText = 'Tamamlandı'; break;
      case AppointmentStatus.rejected:
        statusColor = AppTheme.errorColor; statusText = 'Reddedildi'; break;
      case AppointmentStatus.noShow:
        statusColor = Colors.blueGrey; statusText = 'Gelmedi'; break;
      case AppointmentStatus.inProgress:
        statusColor = Colors.blue; statusText = 'Devam Ediyor'; break;
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFF2A2A2A),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFF3A3A3A)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Hizmet + Fiyat + Durum
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [
            Container(width: 44, height: 44,
              decoration: BoxDecoration(
                color: AppTheme.secondaryColor.withOpacity(0.15),
                borderRadius: BorderRadius.circular(12)),
              child: const Icon(Icons.content_cut, color: AppTheme.secondaryColor, size: 22)),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(appointment.serviceName ?? 'Hizmet', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600)),
              if (appointment.barberName != null && appointment.barberName!.trim().isNotEmpty)
                Text(appointment.barberName!.trim(), style: const TextStyle(color: Colors.white38, fontSize: 13)),
            ])),
            Text('₺${(appointment.price ?? 0.0).toStringAsFixed(0)}', style: const TextStyle(
              color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
          ]),
          const SizedBox(height: 4),
          Align(alignment: Alignment.centerRight, child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: statusColor.withOpacity(0.15),
              borderRadius: BorderRadius.circular(6)),
            child: Text(statusText, style: TextStyle(
              color: statusColor, fontSize: 11, fontWeight: FontWeight.w600)),
          )),
        ]),
        const SizedBox(height: 12),
        // Tarih
        Row(children: [
          const Icon(Icons.access_time, color: Colors.white38, size: 16),
          const SizedBox(width: 8),
          Text(dateStr, style: const TextStyle(color: Colors.white54, fontSize: 13)),
        ]),
        const SizedBox(height: 8),
        // Konum
        const Row(children: [
          Icon(Icons.near_me, color: Colors.white38, size: 16),
          SizedBox(width: 8),
          Text('B&V Coffee Barber – Tarsus/Mersin', style: TextStyle(color: Colors.white54, fontSize: 13)),
        ]),

        // Takvime Ekle (Yaklaşan randevular için aktifse)
        if (isUpcoming && appointment.status == AppointmentStatus.confirmed) ...[
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 40,
            child: OutlinedButton.icon(
              onPressed: () => _showCalendarOptions(context),
              icon: const Icon(Icons.calendar_today, size: 16, color: AppTheme.secondaryColor),
              label: const Text('Takvime Ekle', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: AppTheme.secondaryColor),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],

        // Yaklaşan: İptal Et + Yol Tarifi
        if (isUpcoming && appointment.status != AppointmentStatus.cancelled) ...[
          const SizedBox(height: 12),
          Row(children: [
            Expanded(child: SizedBox(height: 42, child: OutlinedButton(
              onPressed: () => _showCancelDialog(context),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppTheme.errorColor,
                side: const BorderSide(color: AppTheme.errorColor),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
              child: const Text('İptal Et', style: TextStyle(fontWeight: FontWeight.bold)),
            ))),
            const SizedBox(width: 12),
            Expanded(child: SizedBox(height: 42, child: ElevatedButton(
              onPressed: () async {
                const latitude = 36.923826;
                const longitude = 34.903672;
                final webUri = Uri.parse('https://www.google.com/maps/search/?api=1&query=$latitude,$longitude');
                try {
                  if (await canLaunchUrl(webUri)) {
                    await launchUrl(webUri, mode: LaunchMode.externalApplication);
                  }
                } catch (_) {}
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
              child: const Text('Yol Tarifi', style: TextStyle(fontWeight: FontWeight.bold)),
            ))),
          ]),
        ],

        // Yorum & Değerlendirme (Geçmiş ve tamamlanmış randevular için henüz yorumlanmamışsa)
        if (!isUpcoming && appointment.status == AppointmentStatus.completed && !appointment.isReviewed) ...[
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 46,
            child: ElevatedButton.icon(
              onPressed: () => _showReviewDialog(context),
              icon: const Icon(Icons.star_rate_rounded, size: 20, color: Colors.black),
              label: const Text('Hizmeti Değerlendir & Puanla', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold)),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                elevation: 0,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ),
        ],

        // Yorum & Değerlendirme yıldızları (Zaten değerlendirilmişse kart içinde göster)
        if (!isUpcoming && appointment.status == AppointmentStatus.completed && appointment.isReviewed) ...[
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: const Color(0xFF1E1E1E),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: Colors.white12),
            ),
            child: Row(
              children: [
                const Icon(Icons.check_circle_outline_rounded, color: AppTheme.successColor, size: 18),
                const SizedBox(width: 8),
                const Text(
                  'Değerlendirildi',
                  style: TextStyle(color: Colors.white70, fontSize: 13, fontWeight: FontWeight.bold),
                ),
                const Spacer(),
                Row(
                  children: List.generate(5, (index) {
                    final ratingVal = appointment.rating ?? 5;
                    return Icon(
                      index < ratingVal ? Icons.star_rounded : Icons.star_border_rounded,
                      color: AppTheme.secondaryColor,
                      size: 18,
                    );
                  }),
                ),
              ],
            ),
          ),
        ],
      ]),
    );
  }

  void _showCancelDialog(BuildContext context) {
    showDialog(context: context, builder: (_) => AlertDialog(
      backgroundColor: const Color(0xFF2A2A2A),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: const Text('Randevuyu İptal Et', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
      content: const Text('Bu randevuyu iptal etmek istediğinize emin misiniz?', style: TextStyle(color: Colors.white54)),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context),
          child: const Text('Vazgeç', style: TextStyle(color: Colors.white54))),
        TextButton(
          onPressed: () async {
            final messenger = ScaffoldMessenger.of(context);
            final apptProvider = Provider.of<AppointmentProvider>(context, listen: false);
            Navigator.pop(context);
            try {
              await apptProvider.cancelAppointment(appointment.id);
              _showSnackBar(messenger, 'Randevunuz başarıyla iptal edildi.', AppTheme.successColor);
            } catch (e) {
              _showSnackBar(messenger, 'İptal hatası: ${e.toString().replaceAll('Exception: ', '')}', AppTheme.errorColor);
            }
          },
          child: const Text('İptal Et', style: TextStyle(color: AppTheme.errorColor, fontWeight: FontWeight.bold)),
        ),
      ],
    ));
  }
}
