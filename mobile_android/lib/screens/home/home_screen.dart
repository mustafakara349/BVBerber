import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/providers/auth_provider.dart';
import 'package:mobile_android/providers/service_provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/service_model.dart';
import 'package:mobile_android/routes/app_routes.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_android/services/api_service.dart';

/// Ana sayfa ekranı
class HomeScreen extends StatefulWidget {
  final VoidCallback? onNavigateToServices;
  final VoidCallback? onNavigateToProfile;
  const HomeScreen({
    super.key,
    this.onNavigateToServices,
    this.onNavigateToProfile,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _userData;
  List<ServiceModel> _services = [];
  bool _isLoading = true;
  Timer? _notificationTimer;
  bool _hasUnreadNotifications = false;

  @override
  void initState() {
    super.initState();
    _loadData();
    _startNotificationPolling();
  }

  @override
  void dispose() {
    _notificationTimer?.cancel();
    super.dispose();
  }

  void _startNotificationPolling() {
    // İlk kontrolü 5 saniye sonra yap
    Timer(const Duration(seconds: 5), _checkNotifications);
    // Sonra her 30 saniyede bir sorgula
    _notificationTimer = Timer.periodic(const Duration(seconds: 30), (timer) {
      _checkNotifications();
    });
  }

  Future<void> _checkNotifications() async {
    if (!mounted) return;
    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      if (authProvider.currentUser == null) return;

      final notifications = await ApiService.getNotifications();
      final prefs = await SharedPreferences.getInstance();
      final lastReadCount = prefs.getInt('last_read_notification_count') ?? 0;
      
      if (mounted) {
        setState(() {
          _hasUnreadNotifications = notifications.length > lastReadCount;
        });
      }
    } catch (e) {
      debugPrint('Bildirim kontrolünde hata: $e');
    }
  }

  Future<void> _loadData() async {
    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      if (authProvider.currentUser == null) {
        await authProvider.tryAutoLogin();
      }
      
      final user = authProvider.currentUser;
      if (user != null) {
        _userData = user.toMap();
        
        final serviceProvider = Provider.of<ServiceProvider>(context, listen: false);
        await serviceProvider.loadServices();
        _services = serviceProvider.services;
      }
    } catch (e) {
      debugPrint('Veri yüklenirken hata: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _launchNativeMap() async {
    final label = Uri.encodeComponent('B&V Coffee Barber');
    
    // Android: geo URI triggers the native app chooser list
    final androidUri = Uri.parse('geo:0,0?q=$label');
    // iOS: maps URI
    final iosUri = Uri.parse('maps://?q=$label');
    
    // Fallback web URL
    final webUri = Uri.parse('https://www.google.com/maps/search/?api=1&query=$label');
    
    final isAndroid = Theme.of(context).platform == TargetPlatform.android;
    final targetUri = isAndroid ? androidUri : iosUri;
    
    try {
      if (await canLaunchUrl(targetUri)) {
        await launchUrl(targetUri, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(webUri, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      try {
        await launchUrl(webUri, mode: LaunchMode.externalApplication);
      } catch (_) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Harita veya tarayıcı açılamadı.'),
              backgroundColor: AppTheme.errorColor,
            ),
          );
        }
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: AppTheme.secondaryColor),
      );
    }

    final userName = _userData?['name'] ?? 'Kullanıcı';
    final profileUrl = _userData?['profileImageUrl'];

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 16),
              // Üst bar: Profil + İsim + Bildirim
              Row(
                children: [
                  // Profil fotoğrafı
                  GestureDetector(
                    onTap: () => widget.onNavigateToProfile?.call(),
                    child: Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: AppTheme.secondaryColor, width: 2),
                      ),
                      child: ClipOval(
                        child: profileUrl != null
                            ? Image.network(profileUrl, fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => const Icon(Icons.person, color: Colors.black38))
                            : const Icon(Icons.person, color: Colors.black38),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  // Hoş geldin mesajı
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Hoş geldin,',
                          style: TextStyle(color: Colors.black54, fontSize: 13),
                        ),
                        Text(
                          userName,
                          style: const TextStyle(
                            color: Colors.black87,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Bildirim ikonu
                  GestureDetector(
                    onTap: () async {
                      await Navigator.pushNamed(context, AppRoutes.notifications);
                      try {
                        final prefs = await SharedPreferences.getInstance();
                        final notifications = await ApiService.getNotifications();
                        await prefs.setInt('last_read_notification_count', notifications.length);
                      } catch (_) {}
                      if (mounted) {
                        setState(() {
                          _hasUnreadNotifications = false;
                        });
                      }
                    },
                    child: Stack(
                      children: [
                        Container(
                          width: 42,
                          height: 42,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white,
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: const Icon(Icons.notifications_outlined, color: Colors.black87, size: 22),
                        ),
                        if (_hasUnreadNotifications)
                          Positioned(
                            right: 2,
                            top: 2,
                            child: Container(
                              width: 10,
                              height: 10,
                              decoration: const BoxDecoration(
                                color: AppTheme.secondaryColor,
                                shape: BoxShape.circle,
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // B&V COFFEE BARBER Banner
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(24),
                  gradient: const LinearGradient(
                    colors: [Color(0xFF1F1F1F), Color(0xFFE5B942)],
                    begin: Alignment.centerLeft,
                    end: Alignment.centerRight,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.1),
                      blurRadius: 15,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'B&V COFFEE BARBER',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Usta ellerden profesyonel tasarım ve bakım\nhizmeti alın.',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 13,
                        height: 1.5,
                      ),
                    ),
                    const SizedBox(height: 24),
                    Row(
                      children: [
                        // Faces placeholder - stack of circular images
                        SizedBox(
                          width: 80,
                          height: 36,
                          child: Stack(
                            children: [
                              Positioned(left: 0, child: Container(width: 36, height: 36, decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.grey.shade300, border: Border.all(color: const Color(0xFF1F1F1F), width: 2)), child: const Icon(Icons.person, size: 20, color: Colors.black54))),
                              Positioned(left: 20, child: Container(width: 36, height: 36, decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.grey.shade400, border: Border.all(color: const Color(0xFF1F1F1F), width: 2)), child: const Icon(Icons.person, size: 20, color: Colors.black54))),
                              Positioned(left: 40, child: Container(width: 36, height: 36, decoration: BoxDecoration(shape: BoxShape.circle, color: AppTheme.secondaryColor, border: Border.all(color: const Color(0xFF1F1F1F), width: 2)), child: const Center(child: Text('+1', style: TextStyle(color: Colors.black, fontSize: 12, fontWeight: FontWeight.bold))))),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        // Hemen Randevu Al butonu
                        Expanded(
                          child: SizedBox(
                            height: 42,
                            child: ElevatedButton(
                              onPressed: () {
                                Navigator.pushNamed(context, AppRoutes.createAppointment);
                              },
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppTheme.secondaryColor,
                                foregroundColor: Colors.black,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                textStyle: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              child: const Text('Hemen Randevu Al'),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // Hizmetlerimiz başlık
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Hizmetlerimiz',
                    style: TextStyle(
                      color: Colors.black87,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  GestureDetector(
                    onTap: () {
                      widget.onNavigateToServices?.call();
                    },
                    child: const Text(
                      'Tümünü Gör',
                      style: TextStyle(
                        color: AppTheme.secondaryColor,
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Hizmet kartları grid
              if (_services.isEmpty)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(32),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Center(
                    child: Text(
                      'Henüz hizmet eklenmemiş',
                      style: TextStyle(color: Colors.black54, fontSize: 14),
                    ),
                  ),
                )
              else
                GridView.builder(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 2,
                    crossAxisSpacing: 14,
                    mainAxisSpacing: 14,
                    childAspectRatio: 0.85,
                  ),
                  itemCount: _services.length > 4 ? 4 : _services.length,
                  itemBuilder: (context, index) {
                    return _buildServiceCard(_services[index]);
                  },
                ),
              const SizedBox(height: 24),

              // Konum kartı
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: const Color(0xFFF3F4F6),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: Row(
                  children: [
                    // Harita ön izleme
                    Container(
                      width: 70,
                      height: 70,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        color: AppTheme.backgroundColor,
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(16),
                        child: Stack(
                          children: [
                            Image.network(
                              'https://maps.googleapis.com/maps/api/staticmap?center=Tarsus,Mersin&zoom=15&size=140x140&maptype=roadmap',
                              fit: BoxFit.cover,
                              width: double.infinity,
                              height: double.infinity,
                              errorBuilder: (_, __, ___) => const Center(
                                child: Icon(Icons.map_outlined, color: Colors.black26, size: 28),
                              ),
                            ),
                            const Center(
                              child: Icon(Icons.location_on, color: AppTheme.errorColor, size: 28),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    // Adres bilgileri
                    const Expanded(
                      child: Column(
                         crossAxisAlignment: CrossAxisAlignment.start,
                         children: [
                           Text(
                             'B&V COFFEE...',
                             style: TextStyle(
                               color: Colors.black87,
                               fontSize: 15,
                               fontWeight: FontWeight.w900,
                             ),
                             maxLines: 1,
                             overflow: TextOverflow.ellipsis,
                           ),
                           SizedBox(height: 6),
                           Text(
                             'Fatih Mah. Çağlayan\nCad. No:39D/B\nTarsus/Mersin',
                             style: TextStyle(
                               color: Colors.black54,
                               fontSize: 12,
                               height: 1.4,
                             ),
                           ),
                         ],
                      ),
                    ),
                    // Yol tarifi butonu
                    GestureDetector(
                      onTap: _launchNativeMap,
                      child: Container(
                        width: 48,
                        height: 48,
                        decoration: const BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppTheme.secondaryColor,
                        ),
                        child: const Icon(Icons.directions_outlined, color: Colors.white, size: 24),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 100), // Space for navbar
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildServiceCard(ServiceModel service) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Hizmet görseli + fiyat
        Expanded(
          child: Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: service.imageUrl != null
                    ? Image.network(
                        service.imageUrl!,
                        fit: BoxFit.cover,
                        width: double.infinity,
                        height: double.infinity,
                        errorBuilder: (_, __, ___) => Container(
                          color: AppTheme.backgroundColor,
                          child: const Center(
                            child: Icon(Icons.content_cut, color: Colors.black12, size: 32),
                          ),
                        ),
                      )
                    : Container(
                        color: AppTheme.backgroundColor,
                        child: const Center(
                          child: Icon(Icons.content_cut, color: Colors.black12, size: 32),
                        ),
                      ),
              ),
              // Fiyat etiketi
              Positioned(
                bottom: 8,
                left: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(0.7),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Text(
                    '₺${service.price.toStringAsFixed(0)}',
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
        // Hizmet adı
        Padding(
          padding: const EdgeInsets.only(top: 10, left: 4),
          child: Text(
            service.name,
            style: const TextStyle(
              color: Colors.black87,
              fontSize: 15,
              fontWeight: FontWeight.w900,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ),
      ],
    );
  }
}
