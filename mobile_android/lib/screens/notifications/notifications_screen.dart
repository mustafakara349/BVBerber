import 'package:flutter/material.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:mobile_android/models/notification_model.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:intl/intl.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<NotificationModel> _notifications = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    try {
      final data = await ApiService.getNotifications();
      if (mounted) {
        setState(() {
          _notifications = data.map((d) => NotificationModel.fromMap(d)).toList();
          _isLoading = false;
        });
      }
    } catch (e) {
      debugPrint('Bildirimler yüklenirken hata: $e');
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);

    return Scaffold(
      backgroundColor: AppTheme.bg(context),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: isDark ? Colors.white : Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text('Bildirimler', style: TextStyle(
          color: isDark ? Colors.white : Colors.black87, fontSize: 18, fontWeight: FontWeight.bold)),
        centerTitle: true,
      ),
      body: RefreshIndicator(
        onRefresh: _loadNotifications,
        color: AppTheme.secondaryColor,
        child: _isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
          : _notifications.isEmpty
            ? _buildEmptyState(isDark)
            : _buildNotificationList(isDark),
      ),
    );
  }

  Widget _buildEmptyState(bool isDark) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(height: MediaQuery.of(context).size.height * 0.25),
        Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 70, height: 70,
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Icon(Icons.notifications_off_outlined, color: isDark ? Colors.white24 : Colors.black12, size: 36),
              ),
              const SizedBox(height: 24),
              Text('Henüz Bildiriminiz Yok', style: TextStyle(
                color: isDark ? Colors.white : Colors.black87, fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 10),
              Text('Randevu hatırlatmaları ve kampanyalar\nburada görüntülenecektir.', 
                textAlign: TextAlign.center,
                style: TextStyle(color: isDark ? Colors.white60 : Colors.black54, fontSize: 14, height: 1.5)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildNotificationList(bool isDark) {
    return ListView.builder(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      itemCount: _notifications.length,
      itemBuilder: (context, index) {
        final notification = _notifications[index];
        final timeStr = DateFormat('dd MMM yyyy HH:mm', 'tr_TR').format(notification.createdAt);

        IconData getNotificationIcon(String type) {
          switch (type) {
            case 'campaign':
              return Icons.local_offer_outlined;
            case 'appointment':
              return Icons.calendar_today_outlined;
            case 'system':
              return Icons.error_outline;
            default:
              return Icons.notifications_outlined;
          }
        }

        Color getNotificationColor(String type) {
          switch (type) {
            case 'campaign':
              return const Color(0xFFEAB308); // Gold
            case 'appointment':
              return const Color(0xFF10B981); // Emerald Green
            case 'system':
              return const Color(0xFFEF4444); // Red
            default:
              return AppTheme.secondaryColor;
          }
        }

        final iconData = getNotificationIcon(notification.icon);
        final color = getNotificationColor(notification.icon);

        return Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade300,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(isDark ? 0.35 : 0.05),
                blurRadius: 10,
                offset: const Offset(0, 5),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(iconData, color: color, size: 22),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(notification.title, style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87, fontSize: 15, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 6),
                    Text(notification.message, style: TextStyle(
                      color: isDark ? const Color(0xFF9CA3AF) : Colors.black54, fontSize: 13, height: 1.4)),
                    const SizedBox(height: 10),
                    Text(timeStr, style: TextStyle(
                      color: isDark ? Colors.white38 : Colors.black38, fontSize: 11, fontWeight: FontWeight.w500)),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
