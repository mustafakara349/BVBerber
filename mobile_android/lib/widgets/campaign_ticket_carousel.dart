import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/campaign_model.dart';

/// Bilet kesim geometrisi (Kupon çentikli kenarlar)
Path buildTicketPath(
  Size size, {
  double cornerRadius = 16.0,
  double notchRadius = 9.0,
  double notchXFromRight = 85.0,
}) {
  final w = size.width;
  final h = size.height;
  final notchX = w - notchXFromRight;
  final path = Path();

  // Sol üst köşeden başla
  path.moveTo(cornerRadius, 0);

  // Üst kenar boyunca üst çentiğe kadar git
  path.lineTo(notchX - notchRadius, 0);

  // Üst çentik (içe doğru yarım daire)
  path.arcToPoint(
    Offset(notchX + notchRadius, 0),
    radius: Radius.circular(notchRadius),
    clockwise: false,
  );

  // Sağ üst köşeye kadar git
  path.lineTo(w - cornerRadius, 0);
  path.arcToPoint(
    Offset(w, cornerRadius),
    radius: Radius.circular(cornerRadius),
    clockwise: true,
  );

  // Sağ kenar boyunca sağ alt köşeye git
  path.lineTo(w, h - cornerRadius);
  path.arcToPoint(
    Offset(w - cornerRadius, h),
    radius: Radius.circular(cornerRadius),
    clockwise: true,
  );

  // Alt kenar boyunca alt çentiğe kadar git
  path.lineTo(notchX + notchRadius, h);

  // Alt çentik (içe doğru yarım daire)
  path.arcToPoint(
    Offset(notchX - notchRadius, h),
    radius: Radius.circular(notchRadius),
    clockwise: false,
  );

  // Sol alt köşeye kadar git
  path.lineTo(cornerRadius, h);
  path.arcToPoint(
    Offset(0, h - cornerRadius),
    radius: Radius.circular(cornerRadius),
    clockwise: true,
  );

  // Sol kenar boyunca sol üst köşeye dön
  path.lineTo(0, cornerRadius);
  path.arcToPoint(
    Offset(cornerRadius, 0),
    radius: Radius.circular(cornerRadius),
    clockwise: true,
  );

  path.close();
  return path;
}

/// Bilet şekilli kırpıcı
class TicketClipper extends CustomClipper<Path> {
  final double cornerRadius;
  final double notchRadius;
  final double notchXFromRight;

  const TicketClipper({
    this.cornerRadius = 16.0,
    this.notchRadius = 9.0,
    this.notchXFromRight = 85.0,
  });

  @override
  Path getClip(Size size) {
    return buildTicketPath(
      size,
      cornerRadius: cornerRadius,
      notchRadius: notchRadius,
      notchXFromRight: notchXFromRight,
    );
  }

  @override
  bool shouldReclip(covariant TicketClipper oldClipper) => false;
}

/// Bilet kenarlık ve delikli dikey çizgi çizici
class TicketBorderPainter extends CustomPainter {
  final bool isDark;
  final double cornerRadius;
  final double notchRadius;
  final double notchXFromRight;

  TicketBorderPainter({
    required this.isDark,
    this.cornerRadius = 16.0,
    this.notchRadius = 9.0,
    this.notchXFromRight = 85.0,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final path = buildTicketPath(
      size,
      cornerRadius: cornerRadius,
      notchRadius: notchRadius,
      notchXFromRight: notchXFromRight,
    );

    // Altın sarısı gradyan kenarlık çizgisi
    final strokePaint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.3
      ..shader = LinearGradient(
        colors: isDark
            ? [
                const Color(0xFFFACC15).withOpacity(0.45),
                const Color(0xFFFACC15).withOpacity(0.12),
              ]
            : [
                const Color(0xFFFACC15).withOpacity(0.65),
                const Color(0xFFFACC15).withOpacity(0.20),
              ],
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
      ).createShader(Offset.zero & size);

    canvas.drawPath(path, strokePaint);

    // Çentikler arasındaki kesikli dikey çizgi
    final notchX = size.width - notchXFromRight;
    final startY = notchRadius + 4;
    final endY = size.height - notchRadius - 4;

    final dashPaint = Paint()
      ..color = const Color(0xFFFACC15).withOpacity(isDark ? 0.35 : 0.45)
      ..strokeWidth = 1.0
      ..style = PaintingStyle.stroke;

    const dashHeight = 4.0;
    const dashSpace = 4.0;
    double curY = startY;
    while (curY < endY) {
      final nextY = math.min(curY + dashHeight, endY);
      canvas.drawLine(Offset(notchX, curY), Offset(notchX, nextY), dashPaint);
      curY += dashHeight + dashSpace;
    }
  }

  @override
  bool shouldRepaint(covariant TicketBorderPainter oldDelegate) =>
      oldDelegate.isDark != isDark;
}

/// iOS ve görsellerdeki tasarımla birebir uyumlu Kampanya Kupon Kartı
class CampaignTicketCard extends StatelessWidget {
  final CampaignModel campaign;
  final VoidCallback? onTap;

  const CampaignTicketCard({
    super.key,
    required this.campaign,
    this.onTap,
  });

  String _formatCampaignDate(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '';
    try {
      final parsed = DateTime.tryParse(dateStr);
      if (parsed != null) {
        return DateFormat('d MMMM', 'tr_TR').format(parsed);
      }
    } catch (_) {}
    return dateStr;
  }

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);
    final cardBgColor = isDark ? const Color(0xFF1E1E20) : Colors.white;

    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Container(
        height: 132,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.45 : 0.08),
              blurRadius: 12,
              offset: const Offset(0, 5),
            ),
          ],
        ),
        child: CustomPaint(
          painter: TicketBorderPainter(isDark: isDark),
          child: ClipPath(
            clipper: const TicketClipper(),
            child: Container(
              color: cardBgColor,
              child: Row(
                children: [
                  // SOL TARAF: Kampanya Detayları
                  Expanded(
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Fırsat Rozeti
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFACC15).withOpacity(0.18),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'KAMPANYA',
                              style: TextStyle(
                                color: Color(0xFFFACC15),
                                fontSize: 9.5,
                                fontWeight: FontWeight.bold,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ),

                          // Başlık
                          Text(
                            campaign.title,
                            style: TextStyle(
                              color: isDark ? Colors.white : const Color(0xFF1F2937),
                              fontSize: 14.5,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),

                          // Açıklama
                          Text(
                            campaign.description,
                            style: TextStyle(
                              color: isDark ? const Color(0xFF9CA3AF) : const Color(0xFF6B7280),
                              fontSize: 11,
                              height: 1.25,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),

                          // Son Gün Tarihi
                          if (campaign.endDate != null && campaign.endDate!.isNotEmpty)
                            Row(
                              children: [
                                const Icon(
                                  Icons.calendar_today_outlined,
                                  size: 11,
                                  color: Color(0xFFFACC15),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  'Son gün: ${_formatCampaignDate(campaign.endDate)}',
                                  style: TextStyle(
                                    color: const Color(0xFFFACC15).withOpacity(0.9),
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ],
                            )
                          else
                            const SizedBox(height: 12),
                        ],
                      ),
                    ),
                  ),

                  // SAĞ TARAF: Bilet Koçanı (Ticket Stub)
                  Container(
                    width: 85,
                    height: double.infinity,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          const Color(0xFFFACC15).withOpacity(isDark ? 0.08 : 0.12),
                          Colors.transparent,
                        ],
                        begin: Alignment.centerRight,
                        end: Alignment.centerLeft,
                      ),
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        if (campaign.rewardType != 'discount') ...[
                          Icon(
                            campaign.rewardType == 'gift_cafe'
                                ? Icons.local_cafe_rounded
                                : Icons.shopping_bag_rounded,
                            color: const Color(0xFFFACC15),
                            size: 28,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'HEDİYE',
                            style: TextStyle(
                              color: isDark ? Colors.white : Colors.black87,
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 1.5,
                            ),
                          ),
                        ] else ...[
                          Text(
                            campaign.isPercentage
                                ? '%${campaign.discountValue.toInt()}'
                                : '₺${campaign.discountValue.toInt()}',
                            style: const TextStyle(
                              color: Color(0xFFFACC15),
                              fontSize: 27,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -0.5,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'İNDİRİM',
                            style: TextStyle(
                              color: isDark ? Colors.white : Colors.black87,
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 1.5,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Sağa sola kaydırırken 3D Cover Flow / Perspektif Dönüş Efekti Sunan Carousel
class Campaign3DCarousel extends StatefulWidget {
  final List<CampaignModel> campaigns;
  final Function(CampaignModel) onCampaignTap;

  const Campaign3DCarousel({
    super.key,
    required this.campaigns,
    required this.onCampaignTap,
  });

  @override
  State<Campaign3DCarousel> createState() => _Campaign3DCarouselState();
}

class _Campaign3DCarouselState extends State<Campaign3DCarousel> {
  late final PageController _pageController;
  double _currentPage = 0.0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(
      viewportFraction: 0.82,
      initialPage: 0,
    );
    _pageController.addListener(_onScroll);
  }

  void _onScroll() {
    if (mounted) {
      setState(() {
        _currentPage = _pageController.page ?? 0.0;
      });
    }
  }

  @override
  void dispose() {
    _pageController.removeListener(_onScroll);
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.campaigns.isEmpty) {
      return const SizedBox.shrink();
    }

    // Tek kampanya varsa ortalı sabit göster
    if (widget.campaigns.length == 1) {
      return Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: CampaignTicketCard(
          campaign: widget.campaigns.first,
          onTap: () => widget.onCampaignTap(widget.campaigns.first),
        ),
      );
    }

    return SizedBox(
      height: 142,
      child: PageView.builder(
        controller: _pageController,
        clipBehavior: Clip.none,
        itemCount: widget.campaigns.length,
        itemBuilder: (context, index) {
          final campaign = widget.campaigns[index];
          final diff = _currentPage - index;

          // Ekran ortasından uzaklık (0 = tam ortada aktif kart)
          final absDiff = diff.abs().clamp(0.0, 1.0);
          final scale = (1.0 - (absDiff * 0.14)).clamp(0.82, 1.0);
          final opacity = (1.0 - (absDiff * 0.35)).clamp(0.65, 1.0);

          // 3D Y ekseni dönüş açısı (Cover Flow perspektifi)
          // Sağa/sola kaydırırken kenardaki kartlar içe doğru 3D açıyla döner
          final rotationY = (-diff * 0.38).clamp(-0.45, 0.45);

          return Transform(
            alignment: Alignment.center,
            transform: Matrix4.identity()
              ..setEntry(3, 2, 0.0018) // Perspektif derinliği
              ..rotateY(rotationY)
              ..scale(scale),
            child: Opacity(
              opacity: opacity,
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 6),
                child: CampaignTicketCard(
                  campaign: campaign,
                  onTap: () => widget.onCampaignTap(campaign),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
