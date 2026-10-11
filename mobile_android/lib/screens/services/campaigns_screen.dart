import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/campaign_model.dart';
import 'package:mobile_android/providers/campaign_provider.dart';

class CampaignsScreen extends StatefulWidget {
  const CampaignsScreen({super.key});

  @override
  State<CampaignsScreen> createState() => _CampaignsScreenState();
}

class _CampaignsScreenState extends State<CampaignsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<CampaignProvider>(context, listen: false).fetchCampaigns();
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
          'Kampanyalar',
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
          : campaignProvider.campaigns.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.local_offer_outlined, size: 64, color: isDark ? Colors.white24 : Colors.black26),
                      const SizedBox(height: 16),
                      Text(
                        'Şu an aktif bir kampanya bulunmamaktadır.',
                        style: TextStyle(color: isDark ? Colors.white60 : Colors.black54, fontSize: 15),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  itemCount: campaignProvider.campaigns.length,
                  itemBuilder: (context, index) {
                    final campaign = campaignProvider.campaigns[index];
                    return _buildCampaignCard(campaign, isDark);
                  },
                ),
    );
  }

  Widget _buildCampaignCard(CampaignModel campaign, bool isDark) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200,
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(isDark ? 0.35 : 0.05),
            blurRadius: 15,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Tag & Son gün
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: AppTheme.secondaryColor.withOpacity(0.18),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'KAMPANYA',
                  style: TextStyle(
                    color: AppTheme.secondaryColor,
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              if (campaign.endDate != null)
                Row(
                  children: [
                    const Icon(Icons.schedule, size: 14, color: AppTheme.secondaryColor),
                    const SizedBox(width: 4),
                    Text(
                      'Son gün: ${campaign.endDate}',
                      style: TextStyle(
                        color: isDark ? Colors.white60 : Colors.black54,
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
            ],
          ),
          const SizedBox(height: 14),

          // Başlık
          Text(
            campaign.title,
            style: TextStyle(
              color: isDark ? Colors.white : Colors.black87,
              fontSize: 18,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),

          // Açıklama
          Text(
            campaign.description,
            style: TextStyle(
              color: isDark ? const Color(0xFF9CA3AF) : Colors.black54,
              fontSize: 14,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 16),

          // İndirim veya hediye rozeti
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF242428) : const Color(0xFFF9FAFB),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isDark ? const Color(0xFF333338) : Colors.grey.shade200,
              ),
            ),
            child: Row(
              children: [
                const Icon(Icons.card_giftcard, color: AppTheme.secondaryColor, size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    campaign.rewardType == 'gift_product'
                        ? 'Hediye Ürün Kampanyası'
                        : campaign.rewardType == 'gift_cafe'
                            ? 'Hediye Kafe İkramı'
                            : campaign.isPercentage
                                ? '%${campaign.discountValue.toInt()} İndirim Fırsatı'
                                : '${campaign.discountValue.toStringAsFixed(0)} ₺ İndirim Fırsatı',
                    style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87,
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),

          if (campaign.terms != null && campaign.terms!.isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              'Şartlar: ${campaign.terms}',
              style: TextStyle(
                color: isDark ? Colors.white38 : Colors.black38,
                fontSize: 12,
                fontStyle: FontStyle.italic,
              ),
            ),
          ],
        ],
      ),
    );
  }
}
