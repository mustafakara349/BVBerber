import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/service_model.dart';
import 'package:mobile_android/providers/service_provider.dart';

/// Hizmetler ekranı – API'den veri çeker
class ServicesScreen extends StatefulWidget {
  const ServicesScreen({super.key});

  @override
  State<ServicesScreen> createState() => _ServicesScreenState();
}

class _ServicesScreenState extends State<ServicesScreen> {
  late Future<void> _loadServicesFuture;

  @override
  void initState() {
    super.initState();
    _loadServicesFuture = Provider.of<ServiceProvider>(context, listen: false).loadServices();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 20),
              const Text(
                'Hizmetlerimiz',
                style: TextStyle(
                  color: Colors.black87,
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 24),
              Expanded(
                child: FutureBuilder<void>(
                  future: _loadServicesFuture,
                  builder: (context, snapshot) {
                    if (snapshot.connectionState == ConnectionState.waiting) {
                      return const Center(
                        child: CircularProgressIndicator(color: AppTheme.secondaryColor),
                      );
                    }

                    if (snapshot.hasError) {
                      return Center(
                        child: Text(
                          'Hata oluştu: ${snapshot.error}',
                          style: const TextStyle(color: Colors.black54),
                        ),
                      );
                    }

                    return Consumer<ServiceProvider>(
                      builder: (context, serviceProvider, child) {
                        final services = [
                          ...serviceProvider.services,
                          ...serviceProvider.cafeServices,
                        ];

                        // Öne çıkan hizmetleri en üstte göstermek için sıralama yapalım
                        services.sort((a, b) {
                          if (a.isFeatured && !b.isFeatured) return -1;
                          if (!a.isFeatured && b.isFeatured) return 1;
                          return 0;
                        });

                        if (services.isEmpty) {
                          return const Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.content_cut, color: Colors.black12, size: 64),
                                SizedBox(height: 16),
                                Text(
                                  'Henüz hizmet eklenmemiş',
                                  style: TextStyle(color: Colors.black54, fontSize: 16),
                                ),
                              ],
                            ),
                          );
                        }

                        // Kategorilere göre gruplayalım
                        final grouped = <String, List<ServiceModel>>{};
                        for (final s in services) {
                          grouped.putIfAbsent(s.categoryName, () => []).add(s);
                        }

                        return ListView.builder(
                          padding: const EdgeInsets.only(bottom: 24),
                          itemCount: grouped.length,
                          itemBuilder: (context, index) {
                            final categoryName = grouped.keys.elementAt(index);
                            final categoryServices = grouped[categoryName]!;

                            return Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Padding(
                                  padding: const EdgeInsets.only(top: 20, bottom: 12),
                                  child: Row(
                                    children: [
                                      Container(
                                        width: 4, height: 18,
                                        decoration: BoxDecoration(
                                          color: AppTheme.secondaryColor,
                                          borderRadius: BorderRadius.circular(2),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        categoryName.toUpperCase(),
                                        style: const TextStyle(
                                          color: Colors.black87,
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 1.0,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                GridView.builder(
                                  shrinkWrap: true,
                                  physics: const NeverScrollableScrollPhysics(),
                                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                    crossAxisCount: 2,
                                    crossAxisSpacing: 14,
                                    mainAxisSpacing: 20,
                                    childAspectRatio: 0.72,
                                  ),
                                  itemCount: categoryServices.length,
                                  itemBuilder: (context, idx) {
                                    return _buildServiceCard(categoryServices[idx]);
                                  },
                                ),
                              ],
                            );
                          },
                        );
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildServiceCard(ServiceModel service) {
    final hasDiscount = service.discountedPrice != null && service.discountedPrice! < service.price && service.discountedPrice! > 0;
    
    // İndirim yüzdesini hesaplayalım
    int discountPercent = 0;
    if (hasDiscount) {
      discountPercent = (((service.price - service.discountedPrice!) / service.price) * 100).round();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Hizmet görseli + fiyat
        Expanded(
          child: Stack(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: service.imageUrl != null && service.imageUrl!.isNotEmpty
                    ? Image.network(
                        service.imageUrl!,
                        fit: BoxFit.cover,
                        width: double.infinity,
                        height: double.infinity,
                        errorBuilder: (_, __, ___) => Container(
                          decoration: BoxDecoration(
                            color: Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: const Center(
                            child: Icon(Icons.content_cut, color: Colors.black12, size: 36),
                          ),
                        ),
                      )
                    : Container(
                        decoration: BoxDecoration(
                          color: Colors.grey.shade200,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Center(
                          child: Icon(Icons.content_cut, color: Colors.black12, size: 36),
                        ),
                      ),
              ),
              
              // Hafif gölgeleme katmanı (Textlerin okunabilirliğini arttırmak için)
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(16),
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.transparent,
                        Colors.black.withOpacity(0.5),
                      ],
                    ),
                  ),
                ),
              ),

              // Öne Çıkan Rozeti (Featured)
              if (service.isFeatured)
                Positioned(
                  top: 10,
                  left: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFFD700), // Gold Renk
                      borderRadius: BorderRadius.circular(6),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.4),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.star, color: Colors.black, size: 12),
                        SizedBox(width: 4),
                        Text(
                          'ÖNE ÇIKAN',
                          style: TextStyle(
                            color: Colors.black,
                            fontSize: 9,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.5,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

              // İndirim Rozeti (% Oranlı)
              if (hasDiscount)
                Positioned(
                  top: 10,
                  right: 10,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE53935), // Kırmızı İndirim Rengi
                      borderRadius: BorderRadius.circular(6),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.4),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Text(
                      '%$discountPercent İNDİRİM',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),

              // Fiyat etiketi (Strikethrough desteğiyle)
              Positioned(
                bottom: 10,
                left: 10,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: hasDiscount ? const Color(0xFFE53935) : AppTheme.secondaryColor,
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.3),
                        blurRadius: 4,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      if (hasDiscount) ...[
                        Text(
                          '₺${service.price.toStringAsFixed(0)}',
                          style: const TextStyle(
                            color: Colors.white70,
                            fontSize: 11,
                            decoration: TextDecoration.lineThrough,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          '₺${service.discountedPrice!.toStringAsFixed(0)}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ] else ...[
                        Text(
                          '₺${service.price.toStringAsFixed(0)}',
                          style: const TextStyle(
                            color: Colors.black,
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        // Hizmet adı
        Text(
          service.name,
          style: const TextStyle(
            color: Colors.black87,
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        const SizedBox(height: 4),
        // Süre
        Row(
          children: [
            const Icon(Icons.access_time, color: Colors.black38, size: 14),
            const SizedBox(width: 4),
            Text(
              '${service.durationMinutes} dk',
              style: const TextStyle(color: Colors.black54, fontSize: 13),
            ),
          ],
        ),
      ],
    );
  }
}
