import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/providers/auth_provider.dart' as app_auth;
import 'package:mobile_android/routes/app_routes.dart';
import 'package:mobile_android/screens/auth/profile_photo_screen.dart';

/// Profil ekranı
class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? _userData;
  bool _isLoading = true;
  bool _isUploadingPhoto = false;
  bool _isPhotoEnlarged = false;
  final GlobalKey _cameraKey = GlobalKey();
  OverlayEntry? _popoverOverlay;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    try {
      final authProvider = Provider.of<app_auth.AuthProvider>(context, listen: false);
      if (authProvider.currentUser == null) {
        await authProvider.tryAutoLogin();
      }
      final user = authProvider.currentUser;
      if (user != null) {
        _userData = user.toMap();
      }
    } catch (e) {
      debugPrint('Kullanıcı verisi yüklenemedi: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  void dispose() {
    _hideCameraPopover();
    super.dispose();
  }

  void _showCameraPopover() {
    _hideCameraPopover();

    final renderBox = _cameraKey.currentContext?.findRenderObject() as RenderBox?;
    if (renderBox == null) return;

    final targetOffset = renderBox.localToGlobal(Offset.zero);
    final targetSize = renderBox.size;

    _popoverOverlay = OverlayEntry(
      builder: (context) {
        final screenWidth = MediaQuery.of(context).size.width;
        const popoverWidth = 270.0;

        // Kamera butonunun X merkezi ve alt Y konumu
        final cameraCenterX = targetOffset.dx + targetSize.width / 2;
        final cameraBottomY = targetOffset.dy + targetSize.height;

        // Popover kartının sol konumu
        double left = cameraCenterX - popoverWidth / 2;
        if (left + popoverWidth > screenWidth - 16) {
          left = screenWidth - 16 - popoverWidth;
        }
        if (left < 16) {
          left = 16;
        }

        // Okun kart içindeki göreli X konumu
        final arrowX = (cameraCenterX - left).clamp(24.0, popoverWidth - 24.0);

        return Stack(
          children: [
            // Arka planı hafifçe karartan ve tıklandığında kapatan bariyer
            Positioned.fill(
              child: GestureDetector(
                behavior: HitTestBehavior.opaque,
                onTap: _hideCameraPopover,
                child: Container(
                  color: Colors.black.withOpacity(0.5),
                ),
              ),
            ),
            // Popover kartı + yukarı bakan üçgen ok
            Positioned(
              left: left,
              top: cameraBottomY + 4,
              child: Material(
                color: Colors.transparent,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Yukarı bakan üçgen ok
                    Padding(
                      padding: EdgeInsets.only(left: arrowX - 10),
                      child: const CustomPaint(
                        size: Size(20, 10),
                        painter: _PopoverArrowPainter(
                          color: Color(0xFF262629),
                          borderColor: Color(0x33FFFFFF),
                        ),
                      ),
                    ),
                    // Ana kart
                    Container(
                      width: popoverWidth,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                      decoration: BoxDecoration(
                        color: const Color(0xFF262629),
                        borderRadius: BorderRadius.circular(22),
                        border: Border.all(
                          color: const Color(0x33FFFFFF),
                          width: 1,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.5),
                            blurRadius: 20,
                            offset: const Offset(0, 10),
                          ),
                        ],
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text(
                            'Profil Fotoğrafı',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 14),
                          _buildPopoverButton(
                            title: 'Fotoğraf Arşivinden Seç',
                            onTap: () {
                              _hideCameraPopover();
                              _pickAndUploadPhoto(ImageSource.gallery);
                            },
                          ),
                          const SizedBox(height: 10),
                          _buildPopoverButton(
                            title: 'Kameradan Çek',
                            onTap: () {
                              _hideCameraPopover();
                              _pickAndUploadPhoto(ImageSource.camera);
                            },
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        );
      },
    );

    Overlay.of(context).insert(_popoverOverlay!);
  }

  void _hideCameraPopover() {
    _popoverOverlay?.remove();
    _popoverOverlay = null;
  }

  Widget _buildPopoverButton({required String title, required VoidCallback onTap}) {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: Material(
        color: const Color(0xFF3A3A3D),
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: onTap,
          child: Center(
            child: Text(
              title,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 15,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _pickAndUploadPhoto(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final XFile? pickedFile = await picker.pickImage(
        source: source,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );

      if (pickedFile == null || !mounted) return;

      // Fotoğrafı kırpma ekranına yönlendir
      final File? cropped = await Navigator.push<File?>(
        context,
        MaterialPageRoute(
          builder: (_) => PhotoCropScreen(imageFile: File(pickedFile.path)),
        ),
      );

      if (cropped == null || !mounted) return;

      setState(() => _isUploadingPhoto = true);

      final authProvider = Provider.of<app_auth.AuthProvider>(context, listen: false);
      final newUrl = await authProvider.updateProfilePhoto(cropped);

      if (!mounted) return;
      setState(() {
        _isUploadingPhoto = false;
        if (_userData != null) {
          _userData!['profileImageUrl'] = newUrl;
        }
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Profil fotoğrafınız başarıyla güncellendi.'),
          backgroundColor: AppTheme.successColor,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _isUploadingPhoto = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Fotoğraf güncellenemedi: ${e.toString().replaceAll('Exception: ', '')}'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  Widget _buildAvatarPlaceholder(bool isDark, {double size = 50}) {
    return Container(
      color: isDark ? const Color(0xFF242428) : AppTheme.backgroundColor,
      child: Center(
        child: Icon(
          Icons.person,
          color: isDark ? Colors.white38 : Colors.black26,
          size: size,
        ),
      ),
    );
  }

  Future<void> _handleSignOut() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) {
        return AlertDialog(
          backgroundColor: Colors.white,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text(
            'Oturumu Kapat',
            style: TextStyle(color: Colors.black87, fontWeight: FontWeight.bold),
          ),
          content: const Text(
            'Çıkış yapmaya emin misiniz?',
            style: TextStyle(color: Colors.black54),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('İptal', style: TextStyle(color: Colors.black54)),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text(
                'Çıkış Yap',
                style: TextStyle(color: AppTheme.errorColor, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        );
      },
    );

    if (confirmed != true) return;

    if (!mounted) return;
    final authProvider = Provider.of<app_auth.AuthProvider>(context, listen: false);
    await authProvider.signOut();
    if (!mounted) return;
    Navigator.pushNamedAndRemoveUntil(context, AppRoutes.welcome, (route) => false);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: AppTheme.backgroundColor,
        body: Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor)),
      );
    }

    final isDark = AppTheme.isDark(context);
    final userName = '${_userData?['name'] ?? ''} ${_userData?['surname'] ?? ''}'.trim();
    final displayName = userName.isNotEmpty ? userName : 'Kullanıcı';
    final email = _userData?['email'] ?? '';
    final phone = _userData?['phone'] ?? '';
    final profileUrl = _userData?['profileImageUrl'] as String?;

    return Stack(
      children: [
        Scaffold(
          backgroundColor: AppTheme.bg(context),
          body: SafeArea(
            child: SingleChildScrollView(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                children: [
                  const SizedBox(height: 40),
                  // Profil fotoğrafı + Kamera butonu
                  Stack(
                    children: [
                      GestureDetector(
                        onLongPressStart: (_) {
                          HapticFeedback.mediumImpact();
                          setState(() => _isPhotoEnlarged = true);
                        },
                        onLongPressEnd: (_) {
                          setState(() => _isPhotoEnlarged = false);
                        },
                        onLongPressCancel: () {
                          setState(() => _isPhotoEnlarged = false);
                        },
                        onTap: () {
                          setState(() => _isPhotoEnlarged = true);
                        },
                        child: Container(
                          width: 120,
                          height: 120,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(color: AppTheme.secondaryColor, width: 3),
                          ),
                          child: _isUploadingPhoto
                              ? const Center(
                                  child: CircularProgressIndicator(color: AppTheme.secondaryColor),
                                )
                              : ClipOval(
                                  child: profileUrl != null && profileUrl.isNotEmpty
                                      ? Image.network(
                                          profileUrl,
                                          fit: BoxFit.cover,
                                          width: 120,
                                          height: 120,
                                          errorBuilder: (_, __, ___) => _buildAvatarPlaceholder(isDark, size: 50),
                                        )
                                      : _buildAvatarPlaceholder(isDark, size: 50),
                                ),
                        ),
                      ),
                      // Kamera ikonu
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: GestureDetector(
                          key: _cameraKey,
                          onTap: _showCameraPopover,
                          child: Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: AppTheme.secondaryColor,
                              border: Border.all(color: isDark ? const Color(0xFF1E1E20) : Colors.white, width: 3),
                            ),
                            child: const Icon(Icons.camera_alt, color: Colors.black, size: 18),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  // İsim
                  Text(
                    displayName,
                    style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
              const SizedBox(height: 8),
              // Email
              Text(
                email,
                style: TextStyle(color: isDark ? Colors.white60 : Colors.black54, fontSize: 14),
              ),
              const SizedBox(height: 6),
              // Telefon
              if (phone.isNotEmpty)
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.phone, color: isDark ? Colors.white60 : Colors.black54, size: 15),
                    const SizedBox(width: 6),
                    Text(
                      phone,
                      style: TextStyle(color: isDark ? Colors.white60 : Colors.black54, fontSize: 14),
                    ),
                  ],
                ),
              const SizedBox(height: 40),

              // Kuponlarım (iOS eşdeğeri)
              _buildMenuItem(
                icon: Icons.confirmation_number_rounded,
                iconColor: AppTheme.secondaryColor,
                iconBgColor: AppTheme.secondaryColor.withOpacity(0.15),
                title: 'Kuponlarım',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.coupons);
                },
              ),
              const SizedBox(height: 12),

              // Güvenlik ve Giriş
              _buildMenuItem(
                icon: Icons.lock,
                iconColor: AppTheme.secondaryColor,
                iconBgColor: AppTheme.secondaryColor.withOpacity(0.15),
                title: 'Güvenlik ve Giriş',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.security);
                },
              ),
              const SizedBox(height: 12),

              // Destek ve Yardım
              _buildMenuItem(
                icon: Icons.help_outline,
                iconColor: AppTheme.secondaryColor,
                iconBgColor: AppTheme.secondaryColor.withOpacity(0.15),
                title: 'Destek ve Yardım',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.support);
                },
              ),

              const SizedBox(height: 60),

              // Oturumu Kapat butonu
              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton(
                  onPressed: _handleSignOut,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFFFEAEA),
                    foregroundColor: const Color(0xFFE53935),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(30),
                    ),
                    elevation: 0,
                    textStyle: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  child: const Text('OTURUMU KAPAT'),
                ),
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    ),
    // Basılı tutulduğunda büyüyen profil fotoğrafı overlay'i (Görsel 1)
    if (_isPhotoEnlarged)
      Positioned.fill(
        child: GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: () => setState(() => _isPhotoEnlarged = false),
          child: Container(
            color: Colors.black.withOpacity(0.85),
            alignment: Alignment.center,
            child: TweenAnimationBuilder<double>(
              tween: Tween(begin: 0.85, end: 1.0),
              duration: const Duration(milliseconds: 180),
              curve: Curves.easeOutBack,
              builder: (context, scale, child) {
                return Transform.scale(
                  scale: scale,
                  child: child,
                );
              },
              child: Container(
                width: MediaQuery.of(context).size.width * 0.78,
                height: MediaQuery.of(context).size.width * 0.78,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppTheme.secondaryColor, width: 3.5),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.6),
                      blurRadius: 30,
                      spreadRadius: 8,
                    ),
                  ],
                ),
                child: ClipOval(
                  child: profileUrl != null && profileUrl.isNotEmpty
                      ? Image.network(
                          profileUrl,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => _buildAvatarPlaceholder(isDark, size: 100),
                        )
                      : _buildAvatarPlaceholder(isDark, size: 100),
                ),
              ),
            ),
          ),
        ),
      ),
  ],
);
}

  Widget _buildMenuItem({
    required IconData icon,
    required Color iconColor,
    required Color iconBgColor,
    required String title,
    required VoidCallback onTap,
  }) {
    final isDark = AppTheme.isDark(context);

    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
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
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: iconBgColor,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: iconColor, size: 20),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Text(
                title,
                style: TextStyle(
                  color: isDark ? Colors.white : Colors.black87,
                  fontSize: 16,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            Icon(Icons.chevron_right, color: isDark ? Colors.white38 : Colors.black38, size: 24),
          ],
        ),
      ),
    );
  }
}

/// Popover balonu için yukarı bakan ok (beak) çizici
class _PopoverArrowPainter extends CustomPainter {
  final Color color;
  final Color borderColor;

  const _PopoverArrowPainter({required this.color, required this.borderColor});

  @override
  void paint(Canvas canvas, Size size) {
    final fillPaint = Paint()
      ..color = color
      ..style = PaintingStyle.fill;

    final borderPaint = Paint()
      ..color = borderColor
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.0;

    final fillPath = Path()
      ..moveTo(0, size.height + 1)
      ..lineTo(size.width / 2, 0)
      ..lineTo(size.width, size.height + 1)
      ..close();

    final borderPath = Path()
      ..moveTo(0, size.height)
      ..lineTo(size.width / 2, 0)
      ..lineTo(size.width, size.height);

    canvas.drawPath(fillPath, fillPaint);
    canvas.drawPath(borderPath, borderPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
