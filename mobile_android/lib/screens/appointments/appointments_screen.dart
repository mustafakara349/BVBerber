import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:intl/intl.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/core/enums.dart';
import 'package:mobile_android/models/appointment_model.dart';
import 'package:mobile_android/providers/appointment_provider.dart';
import 'package:mobile_android/routes/app_routes.dart';

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
                    onTap: () {
                      Navigator.pushNamed(context, AppRoutes.createAppointment);
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
    return ListView.builder(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      itemCount: items.length,
      itemBuilder: (_, i) => _AppointmentCard(appointment: items[i], isUpcoming: isUpcoming),
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
        FutureBuilder<List<DocumentSnapshot>>(
          future: Future.wait([
            FirebaseFirestore.instance.collection('services').doc(appointment.serviceId).get(),
            FirebaseFirestore.instance.collection('barbers').doc(appointment.barberId).get(),
          ]),
          builder: (context, snap) {
            final serviceName = snap.data?[0].exists == true ? (snap.data![0].data() as Map)['name'] ?? 'Hizmet' : 'Hizmet';
            final servicePrice = snap.data?[0].exists == true ? (snap.data![0].data() as Map)['price']?.toDouble() ?? 0.0 : 0.0;
            final barberName = snap.data?[1].exists == true ? '${(snap.data![1].data() as Map)['name'] ?? ''} ${(snap.data![1].data() as Map)['surname'] ?? ''}' : '';

            return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: [
                Container(width: 44, height: 44,
                  decoration: BoxDecoration(
                    color: AppTheme.secondaryColor.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(12)),
                  child: const Icon(Icons.content_cut, color: AppTheme.secondaryColor, size: 22)),
                const SizedBox(width: 12),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text(serviceName, style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600)),
                  if (barberName.trim().isNotEmpty)
                    Text(barberName.trim(), style: const TextStyle(color: Colors.white38, fontSize: 13)),
                ])),
                Text('₺${servicePrice.toStringAsFixed(0)}', style: const TextStyle(
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
            ]);
          },
        ),
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

        // Yaklaşan: İptal Et + Yol Tarifi
        if (isUpcoming && appointment.status != AppointmentStatus.cancelled) ...[
          const SizedBox(height: 16),
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
              onPressed: () {
                // TODO: Harita yol tarifi
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
              child: const Text('Yol Tarifi', style: TextStyle(fontWeight: FontWeight.bold)),
            ))),
          ]),
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
        TextButton(onPressed: () {
          Navigator.pop(context);
          Provider.of<AppointmentProvider>(context, listen: false).cancelAppointment(appointment.id);
        }, child: const Text('İptal Et', style: TextStyle(color: AppTheme.errorColor, fontWeight: FontWeight.bold))),
      ],
    ));
  }
}
