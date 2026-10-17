import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/services/api_service.dart';

/// Yüksek puan (4-5 yıldız) veren kullanıcıları Google Haritalar'a yönlendiren BottomSheet bileşeni
class GoogleReviewInviteBottomSheet extends StatelessWidget {
  final int reviewId;
  final String googleReviewUrl;

  const GoogleReviewInviteBottomSheet({
    super.key,
    required this.reviewId,
    required this.googleReviewUrl,
  });

  /// Modal pencereyi pratik şekilde açmak için statik yardımcı fonksiyon
  static Future<void> show(
    BuildContext context, {
    required int reviewId,
    required String googleReviewUrl,
  }) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => GoogleReviewInviteBottomSheet(
        reviewId: reviewId,
        googleReviewUrl: googleReviewUrl,
      ),
    );
  }

  Future<void> _handleGoogleRedirect(BuildContext context) async {
    final messenger = ScaffoldMessenger.of(context);
    Navigator.pop(context);

    // Güvenlik doğrulaması: URL sadece Google Review şemasına uygun olmalı
    final trimmedUrl = googleReviewUrl.trim();
    if (!trimmedUrl.startsWith('https://search.google.com/local/writereview') &&
        !trimmedUrl.startsWith('https://maps.google.com') &&
        !trimmedUrl.startsWith('https://goo.gl')) {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Geçersiz Google yönlendirme bağlantısı.'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
      return;
    }

    try {
      final uri = Uri.parse(trimmedUrl);
      final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
      if (launched) {
        // Tıklama kaydedildiğinde 90 günlük kilit devreye girer
        await ApiService.trackGoogleReviewClick(reviewId);
      } else {
        messenger.showSnackBar(
          const SnackBar(
            content: Text('Google Haritalar açılamadı. Lütfen tarayıcınızı kontrol edin.'),
            backgroundColor: AppTheme.errorColor,
          ),
        );
      }
    } catch (e) {
      debugPrint('Google review redirect error: $e');
      messenger.showSnackBar(
        SnackBar(
          content: Text('Yönlendirme sırasında hata oluştu: $e'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);

    return Container(
      padding: EdgeInsets.only(
        left: 24,
        right: 24,
        top: 16,
        bottom: MediaQuery.of(context).viewInsets.bottom + 28,
      ),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        border: Border.all(
          color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200,
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(isDark ? 0.6 : 0.15),
            blurRadius: 25,
            offset: const Offset(0, -6),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Çekme tutamacı (Pull handle)
          Container(
            width: 44,
            height: 5,
            decoration: BoxDecoration(
              color: isDark ? Colors.white24 : Colors.grey.shade300,
              borderRadius: BorderRadius.circular(3),
            ),
          ),
          const SizedBox(height: 24),

          // Google & Yıldız İkon Rozeti
          Stack(
            alignment: Alignment.center,
            children: [
              Container(
                width: 76,
                height: 76,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppTheme.secondaryColor.withOpacity(0.15),
                  border: Border.all(
                    color: AppTheme.secondaryColor.withOpacity(0.3),
                    width: 2,
                  ),
                ),
                child: const Icon(
                  Icons.star_rounded,
                  color: AppTheme.secondaryColor,
                  size: 44,
                ),
              ),
              Positioned(
                right: 0,
                bottom: 0,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF2C2C30) : Colors.white,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.2),
                        blurRadius: 6,
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.place_rounded,
                    color: Colors.redAccent,
                    size: 20,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),

          // Başlık
          Text(
            'B&V Barber\'a Destek Olun! 🌟',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: isDark ? Colors.white : Colors.black87,
              fontSize: 20,
              fontWeight: FontWeight.bold,
              letterSpacing: -0.3,
            ),
          ),
          const SizedBox(height: 10),

          // Açıklama Metni
          Text(
            'Hizmetimizden memnun kaldığınıza çok sevindik! Deneyiminizi 1 dakikanızı ayırarak Google Haritalar\'da paylaşarak salonumuza destek olmak ister misiniz?',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: isDark ? const Color(0xFF9CA3AF) : Colors.black54,
              fontSize: 14,
              height: 1.45,
            ),
          ),
          const SizedBox(height: 24),

          // Ana Yönlendirme Butonu
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton.icon(
              onPressed: () => _handleGoogleRedirect(context),
              icon: const Icon(
                Icons.open_in_new_rounded,
                size: 20,
                color: Colors.black,
              ),
              label: const Text(
                'Google\'da Yorum Yap',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Colors.black,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Vazgeç / Şimdilik Geç Butonu
          SizedBox(
            width: double.infinity,
            height: 44,
            child: TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text(
                'Şimdilik Geç',
                style: TextStyle(
                  color: isDark ? Colors.white60 : Colors.black45,
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
