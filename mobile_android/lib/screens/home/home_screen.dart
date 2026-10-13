import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/providers/auth_provider.dart';
import 'package:mobile_android/providers/service_provider.dart';
import 'package:mobile_android/providers/cafe_provider.dart';
import 'package:mobile_android/providers/sale_products_provider.dart';
import 'package:mobile_android/providers/campaign_provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/barber_model.dart';
import 'package:mobile_android/models/service_model.dart';
import 'package:mobile_android/models/cafe_product_model.dart';
import 'package:mobile_android/models/sale_product_model.dart';
import 'package:mobile_android/routes/app_routes.dart';
import 'package:mobile_android/screens/services/barber_services_screen.dart';
import 'package:mobile_android/screens/services/cafe_menu_screen.dart';
import 'package:mobile_android/screens/services/sale_products_screen.dart';
import 'package:mobile_android/screens/services/campaigns_screen.dart';
import 'package:url_launcher/url_launcher.dart';
import 'dart:async';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:mobile_android/widgets/campaign_ticket_carousel.dart';

/// Ana sayfa ekranı – iOS MainContentView ile birebir aynı bölümler ve akış
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
  List<BarberModel> _barbers = [];
  bool _isLoading = true;
  Timer? _notificationTimer;
  bool _hasUnreadNotifications = false;

  @override
  void initState() {
    super.initState();
    _loadAllHomeData();
    _startNotificationPolling();
  }

  @override
  void dispose() {
    _notificationTimer?.cancel();
    super.dispose();
  }

  void _startNotificationPolling() {
    Timer(const Duration(seconds: 4), _checkNotifications);
    _notificationTimer = Timer.periodic(const Duration(seconds: 30), (_) {
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
    } catch (_) {}
  }

  Future<void> _loadAllHomeData() async {
    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      if (authProvider.currentUser == null) {
        await authProvider.tryAutoLogin();
      }
      if (authProvider.currentUser != null) {
        _userData = authProvider.currentUser!.toMap();
      }

      // Berberleri al
      final barberData = await ApiService.getEmployees();
      _barbers = barberData.map((d) => BarberModel.fromMap(d)).toList();

      // Tüm sağlayıcılardan verileri paralel yükle
      if (mounted) {
        final serviceProv = Provider.of<ServiceProvider>(context, listen: false);
        final cafeProv = Provider.of<CafeProvider>(context, listen: false);
        final saleProv = Provider.of<SaleProductsProvider>(context, listen: false);
        final campaignProv = Provider.of<CampaignProvider>(context, listen: false);

        await Future.wait([
          serviceProv.loadServices(),
          cafeProv.fetchProducts(),
          saleProv.fetchProducts(),
          campaignProv.fetchCampaigns(),
        ]);
      }
    } catch (e) {
      debugPrint('Ana sayfa veri hatası: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _launchNativeMap() async {
    final label = Uri.encodeComponent('B&V Coffee Barber');
    final androidUri = Uri.parse('geo:36.9238616,34.9011379?q=$label');
    final webUri = Uri.parse('https://www.google.com/maps/search/?api=1&query=$label');

    try {
      if (await canLaunchUrl(androidUri)) {
        await launchUrl(androidUri, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(webUri, mode: LaunchMode.externalApplication);
      }
    } catch (_) {
      await launchUrl(webUri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);

    if (_isLoading) {
      return Scaffold(
        backgroundColor: AppTheme.bg(context),
        body: const Center(
          child: CircularProgressIndicator(color: AppTheme.secondaryColor),
        ),
      );
    }

    final userName = _userData?['name'] ?? 'Kullanıcı';
    final profileUrl = _userData?['profileImageUrl'];
    final initials = (_userData?['name'] != null && _userData!['name'].toString().isNotEmpty)
        ? _userData!['name'].toString()[0].toUpperCase()
        : 'B';

    final serviceProvider = Provider.of<ServiceProvider>(context);
    final cafeProvider = Provider.of<CafeProvider>(context);
    final saleProvider = Provider.of<SaleProductsProvider>(context);
    final campaignProvider = Provider.of<CampaignProvider>(context);

    return Scaffold(
      backgroundColor: AppTheme.bg(context),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 16),

              // 1. ÜST BAR (Profil Avatar + İsim + Bildirim İkonu)
              Row(
                children: [
                  GestureDetector(
                    onTap: () => widget.onNavigateToProfile?.call(),
                    child: Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: AppTheme.secondaryColor, width: 2),
                      ),
                      child: ClipOval(
                        child: profileUrl != null && profileUrl.isNotEmpty
                            ? Image.network(
                                profileUrl,
                                fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => _buildInitialsAvatar(initials),
                              )
                            : _buildInitialsAvatar(initials),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Hoş geldin,',
                          style: TextStyle(
                            color: AppTheme.secondaryColor,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          userName,
                          style: TextStyle(
                            color: isDark ? Colors.white : Colors.black87,
                            fontSize: 19,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  GestureDetector(
                    onTap: () async {
                      await Navigator.pushNamed(context, AppRoutes.notifications);
                      try {
                        final notifications = await ApiService.getNotifications();
                        final prefs = await SharedPreferences.getInstance();
                        await prefs.setInt('last_read_notification_count', notifications.length);
                      } catch (_) {}
                      if (mounted) setState(() => _hasUnreadNotifications = false);
                    },
                    child: Stack(
                      children: [
                        Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: isDark ? const Color(0xFF222224) : Colors.white,
                            border: Border.all(color: isDark ? const Color(0xFF333336) : Colors.grey.shade300),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(isDark ? 0.3 : 0.04),
                                blurRadius: 6,
                              )
                            ],
                          ),
                          child: Icon(Icons.notifications_outlined, color: isDark ? Colors.white : Colors.black87, size: 22),
                        ),
                        if (_hasUnreadNotifications)
                          Positioned(
                            right: 3,
                            top: 3,
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

              // 2. PROMO CARD (B&V COFFEE BARBER Banner)
              _buildPromoBanner(context),
              const SizedBox(height: 28),

              // 3. KAMPANYALAR BÖLÜMÜ (3D Animasyonlu Bilet Carousel)
              if (campaignProvider.campaigns.isNotEmpty) ...[
                _buildSectionHeader(
                  title: 'Kampanyalar',
                  isCampaign: true,
                  onSeeAll: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const CampaignsScreen()),
                    );
                  },
                ),
                const SizedBox(height: 14),
                Campaign3DCarousel(
                  campaigns: campaignProvider.campaigns,
                  onCampaignTap: (c) {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const CampaignsScreen()),
                    );
                  },
                ),
                const SizedBox(height: 28),
              ],

              // 4. HİZMETLERİMİZ BÖLÜMÜ
              _buildSectionHeader(
                title: 'Hizmetlerimiz',
                onSeeAll: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const BarberServicesScreen()),
                  );
                },
              ),
              const SizedBox(height: 14),
              SizedBox(
                height: 195,
                child: serviceProvider.services.isEmpty
                    ? const Center(child: Text('Hizmet bulunamadı', style: TextStyle(color: Colors.black45)))
                    : ListView.builder(
                        scrollDirection: Axis.horizontal,
                        itemCount: serviceProvider.services.length > 6 ? 6 : serviceProvider.services.length,
                        itemBuilder: (context, index) {
                          final service = serviceProvider.services[index];
                          return _buildServiceCarouselCard(context, service);
                        },
                      ),
              ),
              const SizedBox(height: 28),

              // 5. KAFE MENÜSÜ BÖLÜMÜ (iOS Cafe Section)
              if (cafeProvider.allProducts.isNotEmpty) ...[
                _buildSectionHeader(
                  title: 'Kafe Menüsü',
                  onSeeAll: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const CafeMenuScreen()),
                    );
                  },
                ),
                const SizedBox(height: 14),
                SizedBox(
                  height: 195,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: cafeProvider.allProducts.length > 6 ? 6 : cafeProvider.allProducts.length,
                    itemBuilder: (context, index) {
                      final product = cafeProvider.allProducts[index];
                      return _buildCafeCarouselCard(context, product);
                    },
                  ),
                ),
                const SizedBox(height: 28),
              ],

              // 6. ÖNE ÇIKAN ÜRÜNLER (iOS Sale Products Section)
              if (saleProvider.allProducts.isNotEmpty) ...[
                _buildSectionHeader(
                  title: 'Öne Çıkan Ürünler',
                  onSeeAll: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (_) => const SaleProductsScreen()),
                    );
                  },
                ),
                const SizedBox(height: 14),
                SizedBox(
                  height: 205,
                  child: ListView.builder(
                    scrollDirection: Axis.horizontal,
                    itemCount: saleProvider.allProducts.length > 6 ? 6 : saleProvider.allProducts.length,
                    itemBuilder: (context, index) {
                      final product = saleProvider.allProducts[index];
                      return _buildSaleProductCarouselCard(context, product);
                    },
                  ),
                ),
                const SizedBox(height: 28),
              ],

              // 7. YOL TARİFİ KARTI (Direction Card)
              _buildDirectionCard(),
              const SizedBox(height: 100), // Bottom bar padding
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInitialsAvatar(String initials) {
    return Container(
      color: AppTheme.secondaryColor.withOpacity(0.2),
      child: Center(
        child: Text(
          initials,
          style: const TextStyle(
            color: AppTheme.secondaryColor,
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader({
    required String title,
    required VoidCallback onSeeAll,
    bool isCampaign = false,
  }) {
    final isDark = AppTheme.isDark(context);
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (isCampaign) ...[
              const Icon(Icons.local_offer, color: AppTheme.secondaryColor, size: 20),
              const SizedBox(width: 8),
            ],
            Text(
              title,
              style: TextStyle(
                color: isDark ? Colors.white : Colors.black87,
                fontSize: 20,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
        GestureDetector(
          onTap: onSeeAll,
          child: const Text(
            'Tümünü Gör',
            style: TextStyle(
              color: AppTheme.secondaryColor,
              fontSize: 14,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPromoBanner(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: const LinearGradient(
          colors: [Color(0xFF141414), Color(0xFF2C2411)],
          begin: Alignment.centerLeft,
          end: Alignment.centerRight,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.12),
            blurRadius: 16,
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
          const SizedBox(height: 6),
          const Text(
            'Usta ellerden profesyonel tasarım ve bakım hizmeti alın.',
            style: TextStyle(
              color: Colors.white70,
              fontSize: 13,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              // Berber Avatarları
              SizedBox(
                width: 90,
                height: 36,
                child: Stack(
                  children: [
                    for (int i = 0; i < (_barbers.length > 3 ? 3 : _barbers.length); i++)
                      Positioned(
                        left: i * 22.0,
                        child: Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: const Color(0xFF141414), width: 2),
                            color: AppTheme.secondaryColor.withOpacity(0.3),
                          ),
                          child: ClipOval(
                            child: _barbers[i].profileImageUrl != null && _barbers[i].profileImageUrl!.isNotEmpty
                                ? Image.network(
                                    _barbers[i].profileImageUrl!,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) => Center(
                                      child: Text(
                                        _barbers[i].initials,
                                        style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  )
                                : Center(
                                    child: Text(
                                      _barbers[i].initials,
                                      style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                                    ),
                                  ),
                          ),
                        ),
                      ),
                    if (_barbers.length > 3)
                      Positioned(
                        left: 3 * 22.0,
                        child: Container(
                          width: 36,
                          height: 36,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: AppTheme.secondaryColor,
                            border: Border.all(color: const Color(0xFF141414), width: 2),
                          ),
                          child: Center(
                            child: Text(
                              '+${_barbers.length - 3}',
                              style: const TextStyle(color: Colors.black, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              const Spacer(),
              // Hemen Randevu Al Butonu
              ElevatedButton(
                onPressed: () {
                  Navigator.pushNamed(context, AppRoutes.createAppointment);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.secondaryColor,
                  foregroundColor: Colors.black,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 11),
                  textStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                child: const Text('Hemen Randevu Al'),
              ),
            ],
          ),
        ],
      ),
    );
  }
  Widget _buildServiceCarouselCard(BuildContext context, ServiceModel service) {
    final isDark = AppTheme.isDark(context);
    return GestureDetector(
      onTap: () {
        Navigator.pushNamed(context, AppRoutes.createAppointment);
      },
      child: Container(
        width: 145,
        margin: const EdgeInsets.only(right: 14),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200,
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.35 : 0.04),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              child: Container(
                height: 95,
                width: double.infinity,
                color: isDark ? const Color(0xFF242428) : Colors.grey.shade100,
                child: service.imageUrl != null && service.imageUrl!.isNotEmpty
                    ? Image.network(
                        service.imageUrl!,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Icon(
                          Icons.content_cut,
                          color: isDark ? Colors.white38 : Colors.black26,
                          size: 28,
                        ),
                      )
                    : Icon(
                        Icons.content_cut,
                        color: isDark ? Colors.white38 : Colors.black26,
                        size: 28,
                      ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    service.name,
                    style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${service.durationMinutes} dk',
                    style: TextStyle(
                      color: isDark ? Colors.white60 : Colors.black45,
                      fontSize: 11,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    service.formattedPrice,
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCafeCarouselCard(BuildContext context, CafeProductModel product) {
    final isDark = AppTheme.isDark(context);
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => CafeMenuScreen(initialProduct: product)),
        );
      },
      child: Container(
        width: 145,
        margin: const EdgeInsets.only(right: 14),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200,
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.35 : 0.04),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              child: Container(
                height: 95,
                width: double.infinity,
                color: isDark ? const Color(0xFF242428) : Colors.grey.shade100,
                child: product.imageUrl.isNotEmpty
                    ? Image.network(
                        product.imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Icon(
                          Icons.coffee,
                          color: isDark ? Colors.white38 : Colors.black26,
                          size: 28,
                        ),
                      )
                    : Icon(
                        Icons.coffee,
                        color: isDark ? Colors.white38 : Colors.black26,
                        size: 28,
                      ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    product.categoryName ?? 'Kafe',
                    style: TextStyle(
                      color: isDark ? Colors.white60 : Colors.black45,
                      fontSize: 11,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  Text(
                    product.formattedPrice,
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSaleProductCarouselCard(BuildContext context, SaleProductModel product) {
    final isDark = AppTheme.isDark(context);
    return GestureDetector(
      onTap: () {
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => SaleProductsScreen(initialProduct: product)),
        );
      },
      child: Container(
        width: 145,
        margin: const EdgeInsets.only(right: 14),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200,
            width: 1,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.35 : 0.04),
              blurRadius: 8,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(18)),
              child: Container(
                height: 95,
                width: double.infinity,
                color: isDark ? const Color(0xFF242428) : Colors.grey.shade100,
                child: product.imageUrl.isNotEmpty
                    ? Image.network(
                        product.imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Icon(
                          Icons.shopping_bag,
                          color: isDark ? Colors.white38 : Colors.black26,
                          size: 28,
                        ),
                      )
                    : Icon(
                        Icons.shopping_bag,
                        color: isDark ? Colors.white38 : Colors.black26,
                        size: 28,
                      ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87,
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    product.stockStatus.label,
                    style: TextStyle(
                      color: product.isInStock ? AppTheme.successColor : AppTheme.errorColor,
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    product.formattedPrice,
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDirectionCard() {
    final isDark = AppTheme.isDark(context);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1C1C1E) : const Color(0xFFF3F4F6),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(
          color: isDark ? const Color(0xFF2C2C30) : Colors.transparent,
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: BoxDecoration(
              color: AppTheme.secondaryColor.withOpacity(0.18),
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(Icons.location_on, color: AppTheme.secondaryColor, size: 30),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'B&V Coffee Barber',
                  style: TextStyle(
                    color: isDark ? Colors.white : Colors.black87,
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Fatih Mah. Çağlayan Cad. No:39D/B Tarsus/Mersin',
                  style: TextStyle(
                    color: isDark ? Colors.white60 : Colors.black54,
                    fontSize: 12,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          ElevatedButton(
            onPressed: _launchNativeMap,
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark ? const Color(0xFF2C2C30) : Colors.white,
              foregroundColor: isDark ? Colors.white : Colors.black87,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: BorderSide(
                  color: isDark ? const Color(0xFF3C3C42) : Colors.grey.shade300,
                ),
              ),
              textStyle: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
            ),
            child: const Text('Yol Tarifi'),
          ),
        ],
      ),
    );
  }
}
