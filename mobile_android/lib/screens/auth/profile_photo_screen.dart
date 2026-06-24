import 'dart:io';
import 'dart:typed_data';
import 'dart:ui' as ui;
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/routes/app_routes.dart';

/// Profil fotoğrafı ekleme ekranı – Kayıt sonrası
class ProfilePhotoScreen extends StatefulWidget {
  const ProfilePhotoScreen({super.key});

  @override
  State<ProfilePhotoScreen> createState() => _ProfilePhotoScreenState();
}

class _ProfilePhotoScreenState extends State<ProfilePhotoScreen> {
  final ImagePicker _picker = ImagePicker();
  File? _selectedImage;
  File? _croppedImage;
  bool _isUploading = false;

  Future<void> _pickImage(ImageSource source) async {
    Navigator.pop(context); // Bottom sheet'i kapat
    try {
      final XFile? pickedFile = await _picker.pickImage(
        source: source,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 90,
      );

      if (pickedFile != null) {
        final file = File(pickedFile.path);
        if (!mounted) return;

        // Fotoğrafı Ayarla ekranına git
        final File? cropped = await Navigator.push<File?>(
          context,
          MaterialPageRoute(
            builder: (_) => _PhotoCropScreen(imageFile: file),
          ),
        );

        if (cropped != null && mounted) {
          setState(() {
            _selectedImage = file;
            _croppedImage = cropped;
          });
        }
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Fotoğraf seçilemedi: ${e.toString()}'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  void _showSourcePicker() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          margin: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.grey.shade300),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 20),
              const Text(
                'Fotoğraf Kaynağı',
                style: TextStyle(
                  color: Colors.black87,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 20),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: OutlinedButton(
                    onPressed: () => _pickImage(ImageSource.gallery),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.black87,
                      side: BorderSide(color: Colors.grey.shade300),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                    ),
                    child: const Text('Galeriden Seç'),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                child: SizedBox(
                  width: double.infinity,
                  height: 50,
                  child: OutlinedButton(
                    onPressed: () => _pickImage(ImageSource.camera),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.black87,
                      side: BorderSide(color: Colors.grey.shade300),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      textStyle: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                    ),
                    child: const Text('Kameradan Çek'),
                  ),
                ),
              ),
              const SizedBox(height: 24),
            ],
          ),
        );
      },
    );
  }

  Future<void> _uploadAndContinue() async {
    final imageToUpload = _croppedImage ?? _selectedImage;
    if (imageToUpload == null) return;

    setState(() => _isUploading = true);
    await Future.delayed(const Duration(milliseconds: 500));
    if (!mounted) return;
    Navigator.pushReplacementNamed(context, AppRoutes.main);
  }

  void _skipAndContinue() {
    Navigator.pushReplacementNamed(context, AppRoutes.main);
  }

  @override
  Widget build(BuildContext context) {
    // Fotoğraf seçildiyse farklı UI göster
    final bool hasPhoto = _croppedImage != null;

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 32),
          child: Column(
            children: [
              // Üst bar - sadece fotoğraf seçildiyse tik butonu göster
              if (hasPhoto)
                Align(
                  alignment: Alignment.topRight,
                  child: Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: GestureDetector(
                      onTap: _isUploading ? null : _uploadAndContinue,
                      child: Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppTheme.secondaryColor,
                          boxShadow: [
                            BoxShadow(
                              color: AppTheme.secondaryColor.withOpacity(0.3),
                              blurRadius: 12,
                              spreadRadius: 2,
                            ),
                          ],
                        ),
                        child: _isUploading
                            ? const Padding(
                                padding: EdgeInsets.all(10),
                                child: CircularProgressIndicator(
                                  color: Colors.black,
                                  strokeWidth: 2.5,
                                ),
                              )
                            : const Icon(Icons.check, color: Colors.black, size: 26),
                      ),
                    ),
                  ),
                )
              else
                const SizedBox(height: 52),

              const Spacer(flex: 2),

              // Profil fotoğrafı alanı
              Container(
                width: 160,
                height: 160,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: AppTheme.secondaryColor,
                    width: 3,
                  ),
                ),
                child: hasPhoto
                    ? ClipOval(
                        child: Image.file(
                          _croppedImage!,
                          fit: BoxFit.cover,
                          width: 160,
                          height: 160,
                        ),
                      )
                    : Container(
                        margin: const EdgeInsets.all(4),
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: AppTheme.secondaryColor.withOpacity(0.2),
                        ),
                        child: Icon(
                          Icons.person,
                          color: AppTheme.secondaryColor.withOpacity(0.7),
                          size: 75,
                        ),
                      ),
              ),
              const SizedBox(height: 32),

              // Başlık
              const Text(
                'Profil Fotoğrafı',
                style: TextStyle(
                  color: Colors.black87,
                  fontSize: 26,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),

              // Açıklama
              Text(
                hasPhoto
                    ? 'Fotoğrafı kaydetmek için sağ üstteki ✓\nbutonuna bas.'
                    : 'Profil fotoğrafı ekleyebilirsin.\nBunu daha sonra da yapabilirsin.',
                style: const TextStyle(
                  color: Colors.black54,
                  fontSize: 14,
                  height: 1.5,
                ),
                textAlign: TextAlign.center,
              ),

              const Spacer(flex: 3),

              // Ana buton
              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton.icon(
                  onPressed: _isUploading
                      ? null
                      : () {
                          if (hasPhoto) {
                            // Farklı fotoğraf seç
                            _showSourcePicker();
                          } else {
                            // İlk fotoğraf seçimi
                            _showSourcePicker();
                          }
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.secondaryColor,
                    foregroundColor: Colors.black,
                    disabledBackgroundColor: AppTheme.secondaryColor.withOpacity(0.5),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(30),
                    ),
                    elevation: 4,
                    textStyle: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  icon: const Icon(Icons.camera_alt_outlined, size: 22),
                  label: Text(hasPhoto ? 'Farklı Fotoğraf Seç' : 'Fotoğraf Ekle'),
                ),
              ),
              const SizedBox(height: 14),

              // Daha Sonra Ekle butonu
              SizedBox(
                width: double.infinity,
                height: 54,
                child: OutlinedButton(
                  onPressed: _isUploading ? null : _skipAndContinue,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.black54,
                    side: BorderSide(color: Colors.grey.shade300, width: 1.5),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(30),
                    ),
                    textStyle: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  child: const Text('Daha Sonra Ekle'),
                ),
              ),
              const SizedBox(height: 36),
            ],
          ),
        ),
      ),
    );
  }
}

// ─────────────────────────────────────────────────────────
// Fotoğrafı Ayarla Ekranı (Crop Screen)
// ─────────────────────────────────────────────────────────
class _PhotoCropScreen extends StatefulWidget {
  final File imageFile;

  const _PhotoCropScreen({required this.imageFile});

  @override
  State<_PhotoCropScreen> createState() => _PhotoCropScreenState();
}

class _PhotoCropScreenState extends State<_PhotoCropScreen> {
  final TransformationController _transformController = TransformationController();
  final GlobalKey _cropKey = GlobalKey();

  @override
  void dispose() {
    _transformController.dispose();
    super.dispose();
  }

  Future<void> _cropAndReturn() async {
    try {
      // RepaintBoundary'den crop edilmiş görüntüyü al
      final RenderRepaintBoundary boundary =
          _cropKey.currentContext!.findRenderObject() as RenderRepaintBoundary;
      final ui.Image image = await boundary.toImage(pixelRatio: 3.0);
      final ByteData? byteData = await image.toByteData(format: ui.ImageByteFormat.png);

      if (byteData == null) return;

      final Uint8List pngBytes = byteData.buffer.asUint8List();

      // Geçici dosyaya kaydet
      final tempDir = Directory.systemTemp;
      final tempFile = File('${tempDir.path}/cropped_profile_${DateTime.now().millisecondsSinceEpoch}.png');
      await tempFile.writeAsBytes(pngBytes);

      if (!mounted) return;
      Navigator.pop(context, tempFile);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Fotoğraf kırpılamadı: ${e.toString()}'),
          backgroundColor: AppTheme.errorColor,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final circleSize = screenWidth * 0.7;

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      body: SafeArea(
        child: Column(
          children: [
            // Üst bar: İptal - Fotoğrafı Ayarla - Tamamla
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  GestureDetector(
                    onTap: () => Navigator.pop(context, null),
                    child: const Text(
                      'İptal',
                      style: TextStyle(
                        color: Colors.black54,
                        fontSize: 16,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  const Text(
                    'Fotoğrafı Ayarla',
                    style: TextStyle(
                      color: Colors.black87,
                      fontSize: 17,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  GestureDetector(
                    onTap: _cropAndReturn,
                    child: const Text(
                      'Tamamla',
                      style: TextStyle(
                        color: AppTheme.secondaryColor,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // Crop alanı
            Expanded(
              child: Center(
                child: SizedBox(
                  width: circleSize,
                  height: circleSize,
                  child: RepaintBoundary(
                    key: _cropKey,
                    child: ClipOval(
                      child: Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: AppTheme.secondaryColor,
                            width: 3,
                          ),
                        ),
                        child: ClipOval(
                          child: InteractiveViewer(
                            transformationController: _transformController,
                            minScale: 0.5,
                            maxScale: 4.0,
                            panEnabled: true,
                            scaleEnabled: true,
                            child: Image.file(
                              widget.imageFile,
                              fit: BoxFit.cover,
                              width: circleSize,
                              height: circleSize,
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),

            // Alt bilgi
            Padding(
              padding: const EdgeInsets.only(bottom: 40),
              child: Column(
                children: [
                  const Text(
                    'Fotoğrafı kaydırın ve yakınlaştırın',
                    style: TextStyle(
                      color: Colors.black54,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.touch_app, color: Colors.black38, size: 18),
                      const SizedBox(width: 6),
                      const Text(
                        'Kaydır',
                        style: TextStyle(color: Colors.black38, fontSize: 13),
                      ),
                      const SizedBox(width: 20),
                      Icon(Icons.pinch, color: Colors.black38, size: 18),
                      const SizedBox(width: 6),
                      const Text(
                        'Yakınlaştır',
                        style: TextStyle(color: Colors.black38, fontSize: 13),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
