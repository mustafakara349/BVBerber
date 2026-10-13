import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/coupon_model.dart';
import 'package:mobile_android/providers/campaign_provider.dart';

class CouponsScreen extends StatefulWidget {
  final Function(String couponCode)? onSelectCoupon;

  const CouponsScreen({super.key, this.onSelectCoupon});

  @override
  State<CouponsScreen> createState() => _CouponsScreenState();
}

class _CouponsScreenState extends State<CouponsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<CampaignProvider>(context, listen: false).fetchCoupons();
    });
  }

  @override
  Widget build(BuildContext context) {
    final campaignProvider = Provider.of<CampaignProvider>(context);
    final isDark = AppTheme.isDark(context);

    return Scaffold(
      backgroundColor: AppTheme.bg(context),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new, color: isDark ? Colors.white : Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Kuponlarım',
          style: TextStyle(
            color: isDark ? Colors.white : Colors.black87,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        centerTitle: true,
      ),
      body: campaignProvider.isLoading
          ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
          : campaignProvider.errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.error_outline, color: AppTheme.errorColor, size: 48),
                      const SizedBox(height: 12),
                      Text(
                        campaignProvider.errorMessage!,
                        style: const TextStyle(color: Colors.black54),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => campaignProvider.fetchCoupons(),
                        child: const Text('Tekrar Dene'),
                      ),
                    ],
                  ),
                )
              : campaignProvider.coupons.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.confirmation_number_outlined, size: 64, color: isDark ? Colors.white24 : Colors.black26),
                          const SizedBox(height: 16),
                          Text(
                            'Henüz kuponunuz bulunmuyor.',
                            style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontSize: 16, fontWeight: FontWeight.bold),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Size özel tanımlanan indirim kuponları burada listelenecektir.',
                            style: TextStyle(color: isDark ? Colors.white54 : Colors.black45, fontSize: 13),
                            textAlign: TextAlign.center,
                          ),
                        ],
                      ),
                    )
                  : ListView(
                      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                      children: [
                        if (campaignProvider.activeCoupons.isNotEmpty) ...[
                          Text(
                            'Aktif Kuponlar',
                            style: TextStyle(
                              color: isDark ? Colors.white : Colors.black87,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 12),
                          ...campaignProvider.activeCoupons.map((coupon) => _buildCouponCard(coupon, isActive: true)),
                          const SizedBox(height: 24),
                        ],
                        if (campaignProvider.pastCoupons.isNotEmpty) ...[
                          Text(
                            'Geçmiş Kuponlar',
                            style: TextStyle(
                              color: isDark ? Colors.white70 : Colors.black54,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 12),
                          ...campaignProvider.pastCoupons.map((coupon) => _buildCouponCard(coupon, isActive: false)),
                        ],
                      ],
                    ),
    );
  }

  Widget _buildCouponCard(CouponModel coupon, {required bool isActive}) {
    final isDark = AppTheme.isDark(context);

    return GestureDetector(
      onTap: () {
        if (widget.onSelectCoupon != null && isActive) {
          widget.onSelectCoupon!(coupon.code);
          Navigator.pop(context);
        } else {
          Clipboard.setData(ClipboardData(text: coupon.code));
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('${coupon.code} panoya kopyalandı!'),
              backgroundColor: AppTheme.secondaryColor,
              duration: const Duration(seconds: 2),
            ),
          );
        }
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isActive
                ? AppTheme.secondaryColor.withOpacity(0.5)
                : (isDark ? const Color(0xFF2C2C30) : Colors.grey.shade300),
            width: 1.5,
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.35 : 0.04),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          children: [
            // Kupon sol ikon
            Container(
              width: 50,
              height: 50,
              decoration: BoxDecoration(
                color: isActive
                    ? AppTheme.secondaryColor.withOpacity(0.2)
                    : (isDark ? const Color(0xFF242428) : Colors.grey.shade200),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(
                Icons.confirmation_number_rounded,
                color: isActive ? AppTheme.secondaryColor : (isDark ? Colors.white38 : Colors.grey),
                size: 26,
              ),
            ),
            const SizedBox(width: 16),

            // Kupon detayları
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    coupon.title,
                    style: TextStyle(
                      color: isActive
                          ? (isDark ? Colors.white : Colors.black87)
                          : (isDark ? Colors.white38 : Colors.black45),
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    coupon.description.isNotEmpty ? coupon.description : (coupon.isPercentage ? '%${coupon.discountValue.toInt()} İndirim' : '${coupon.discountValue.toStringAsFixed(0)} ₺ İndirim'),
                    style: TextStyle(color: isDark ? const Color(0xFF9CA3AF) : Colors.black54, fontSize: 13),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF242428) : const Color(0xFFF3F4F6),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          coupon.code,
                          style: TextStyle(
                            fontFamily: 'monospace',
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      if (coupon.expiresAt != null)
                        Text(
                          'Son: ${coupon.expiresAt}',
                          style: TextStyle(color: isDark ? Colors.white38 : Colors.black38, fontSize: 11),
                        ),
                    ],
                  ),
                ],
              ),
            ),

            // Seç veya Kopyala butonu
            Icon(
              widget.onSelectCoupon != null && isActive ? Icons.check_circle_outline : Icons.copy_rounded,
              color: isActive ? AppTheme.secondaryColor : Colors.grey.shade400,
              size: 22,
            ),
          ],
        ),
      ),
    );
  }
}
