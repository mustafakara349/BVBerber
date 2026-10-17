import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/screens/services/barber_services_screen.dart';
import 'package:mobile_android/screens/services/cafe_menu_screen.dart';
import 'package:mobile_android/screens/services/sale_products_screen.dart';
import 'package:mobile_android/screens/services/campaigns_screen.dart';

/// Hizmetler Hub Ekranı – iOS ServicesView ile birebir aynı tasarım
class ServicesScreen extends StatelessWidget {
  const ServicesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);

    return Scaffold(
      backgroundColor: AppTheme.bg(context),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 16),

              // Header: HİZMETLERİMİZ + Başlık + Kampanyalar butonu
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'HİZMETLERİMİZ',
                          style: TextStyle(
                            color: AppTheme.secondaryColor,
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1.2,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          'Sizin İçin Neler\nYapabiliriz?',
                          style: TextStyle(
                            color: isDark ? Colors.white : Colors.black87,
                            fontSize: 28,
                            fontWeight: FontWeight.bold,
                            height: 1.2,
                          ),
                        ),
                      ],
                    ),
                  ),
                  // Kampanyalar Butonu
                  GestureDetector(
                    onTap: () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const CampaignsScreen()),
                      );
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                      decoration: BoxDecoration(
                        color: AppTheme.secondaryColor,
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                            color: AppTheme.secondaryColor.withOpacity(0.4),
                            blurRadius: 10,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: const Text(
                        'Kampanyalar',
                        style: TextStyle(
                          color: Colors.black,
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // 1. Berber & Bakım Kartı
              _buildPremiumCategoryCard(
                context,
                title: 'Berber & Bakım',
                subtitle: 'Saç kesimi, sakal tıraşı ve cilt bakımı ile kendinizi yenileyin.',
                imageUrl: 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?q=80&w=600&auto=format&fit=crop',
                icon: Icons.content_cut_rounded,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const BarberServicesScreen()),
                  );
                },
              ),
              const SizedBox(height: 20),

              // 2. Kafe Kartı
              _buildPremiumCategoryCard(
                context,
                title: 'Kafe',
                subtitle: 'Sıcak & soğuk içeceklerimiz ve özel atıştırmalıklarımızla mola verin.',
                imageUrl: 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=600&auto=format&fit=crop',
                icon: Icons.local_cafe_rounded,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const CafeMenuScreen()),
                  );
                },
              ),
              const SizedBox(height: 20),

              // 3. Ürünlerimiz Kartı
              _buildPremiumCategoryCard(
                context,
                title: 'Ürünlerimiz',
                subtitle: 'Kullandığımız premium saç ve sakal bakım ürünlerini keşfedin.',
                imageUrl: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=600&auto=format&fit=crop',
                icon: Icons.shopping_bag_rounded,
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const SaleProductsScreen()),
                  );
                },
              ),
              const SizedBox(height: 100), // Bottom nav bar payı
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPremiumCategoryCard(
    BuildContext context, {
    required String title,
    required String subtitle,
    required String imageUrl,
    required IconData icon,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 220,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(22),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.15),
              blurRadius: 15,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(22),
          child: Stack(
            fit: StackFit.expand,
            children: [
              // Arka plan görseli
              Image.network(
                imageUrl,
                fit: BoxFit.cover,
                errorBuilder: (_, _, _) => Container(color: Colors.grey.shade900),
              ),

              // Koyu gradyan overlay
              Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      Colors.black.withOpacity(0.85),
                      Colors.black.withOpacity(0.35),
                      Colors.transparent,
                    ],
                    begin: Alignment.bottomCenter,
                    end: Alignment.topCenter,
                  ),
                ),
              ),

              // Kart İçeriği
              Positioned(
                bottom: 20,
                left: 20,
                right: 20,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Icon(icon, color: AppTheme.secondaryColor, size: 24),
                        const SizedBox(width: 10),
                        Text(
                          title,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      subtitle,
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.9),
                        fontSize: 14,
                        height: 1.3,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
