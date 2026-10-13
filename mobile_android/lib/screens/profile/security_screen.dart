import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/providers/auth_provider.dart';

/// Güvenlik ve Giriş ekranı – Şifre değiştirme
class SecurityScreen extends StatefulWidget {
  const SecurityScreen({super.key});

  @override
  State<SecurityScreen> createState() => _SecurityScreenState();
}

class _SecurityScreenState extends State<SecurityScreen> {
  final _formKey = GlobalKey<FormState>();
  final _currentPasswordController = TextEditingController();
  final _newPasswordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscureCurrent = true;
  bool _obscureNew = true;
  bool _obscureConfirm = true;
  bool _isLoading = false;

  // Şifre gereksinimleri durumu
  bool get _isMinLength => _newPasswordController.text.length >= 6;
  bool get _isPasswordsMatch =>
      _newPasswordController.text.isNotEmpty &&
      _newPasswordController.text == _confirmPasswordController.text;

  @override
  void initState() {
    super.initState();
    // Şifre alanları değiştiğinde gereksinimleri güncelle
    _newPasswordController.addListener(() => setState(() {}));
    _confirmPasswordController.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  InputDecoration _inputDecoration({
    required String hint,
    required bool obscure,
    required VoidCallback toggleObscure,
    required bool isDark,
  }) {
    return InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(color: isDark ? Colors.white24 : Colors.black38, fontSize: 15),
      filled: true,
      fillColor: isDark ? const Color(0xFF222226) : Colors.grey.shade100,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide(color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade300),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppTheme.secondaryColor, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppTheme.errorColor),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: AppTheme.errorColor, width: 1.5),
      ),
      suffixIcon: IconButton(
        icon: Icon(
          obscure ? Icons.visibility_off_outlined : Icons.visibility_outlined,
          color: isDark ? Colors.white38 : Colors.black38,
          size: 20,
        ),
        onPressed: toggleObscure,
      ),
    );
  }

  Future<void> _handleChangePassword() async {
    if (!_formKey.currentState!.validate()) return;

    if (!_isMinLength) {
      _showError('Yeni şifre en az 6 karakter olmalıdır.');
      return;
    }
    if (!_isPasswordsMatch) {
      _showError('Yeni şifreler eşleşmiyor.');
      return;
    }

    setState(() => _isLoading = true);
    try {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.updatePassword(
        currentPassword: _currentPasswordController.text,
        newPassword: _newPasswordController.text,
      );

      if (mounted) {
        _currentPasswordController.clear();
        _newPasswordController.clear();
        _confirmPasswordController.clear();

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Şifreniz başarıyla güncellendi!'),
            backgroundColor: AppTheme.successColor,
          ),
        );
        Navigator.pop(context);
      }
    } catch (e) {
      if (mounted) {
        _showError(e.toString().replaceAll('Exception: ', ''));
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showError(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppTheme.errorColor,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);

    return Scaffold(
      backgroundColor: AppTheme.bg(context),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back, color: isDark ? Colors.white : Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        centerTitle: true,
        title: Text(
          'Güvenlik ve Giriş',
          style: TextStyle(
            color: isDark ? Colors.white : Colors.black87,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 24),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const SizedBox(height: 16),
              // Şifre Değiştir header kartı
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200),
                ),
                child: Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: AppTheme.secondaryColor.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.lock, color: AppTheme.secondaryColor, size: 22),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Şifre Değiştir',
                            style: TextStyle(
                              color: isDark ? Colors.white : Colors.black87,
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Hesabınızı güvende tutmak için şifrenizi düzenli olarak değiştirin.',
                            style: TextStyle(
                              color: isDark ? Colors.white54 : Colors.black54,
                              fontSize: 12,
                              height: 1.4,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // Mevcut Şifre
              Text('Mevcut Şifre', style: TextStyle(color: isDark ? Colors.white70 : Colors.black87, fontSize: 13, fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _currentPasswordController,
                obscureText: _obscureCurrent,
                style: TextStyle(color: isDark ? Colors.white : Colors.black87),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Mevcut şifrenizi girin';
                  }
                  return null;
                },
                decoration: _inputDecoration(
                  hint: 'Mevcut şifrenizi girin',
                  obscure: _obscureCurrent,
                  toggleObscure: () => setState(() => _obscureCurrent = !_obscureCurrent),
                  isDark: isDark,
                ),
              ),
              const SizedBox(height: 24),

              // Yeni Şifre
              Text('Yeni Şifre', style: TextStyle(color: isDark ? Colors.white70 : Colors.black87, fontSize: 13, fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _newPasswordController,
                obscureText: _obscureNew,
                style: TextStyle(color: isDark ? Colors.white : Colors.black87),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Yeni şifrenizi girin';
                  }
                  if (value.length < 6) {
                    return 'Şifre en az 6 karakter olmalı';
                  }
                  return null;
                },
                decoration: _inputDecoration(
                  hint: 'Yeni şifrenizi girin',
                  obscure: _obscureNew,
                  toggleObscure: () => setState(() => _obscureNew = !_obscureNew),
                  isDark: isDark,
                ),
              ),
              const SizedBox(height: 24),

              // Yeni Şifre Tekrar
              Text('Yeni Şifre Tekrar', style: TextStyle(color: isDark ? Colors.white70 : Colors.black87, fontSize: 13, fontWeight: FontWeight.w600)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _confirmPasswordController,
                obscureText: _obscureConfirm,
                style: TextStyle(color: isDark ? Colors.white : Colors.black87),
                validator: (value) {
                  if (value == null || value.isEmpty) {
                    return 'Yeni şifrenizi tekrar girin';
                  }
                  if (value != _newPasswordController.text) {
                    return 'Şifreler eşleşmiyor';
                  }
                  return null;
                },
                decoration: _inputDecoration(
                  hint: 'Yeni şifrenizi tekrar girin',
                  obscure: _obscureConfirm,
                  toggleObscure: () => setState(() => _obscureConfirm = !_obscureConfirm),
                  isDark: isDark,
                ),
              ),
              const SizedBox(height: 24),

              // Şifre Gereksinimleri
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Şifre Gereksinimleri',
                      style: TextStyle(
                        color: isDark ? Colors.white : Colors.black87,
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    _buildRequirement('En az 6 karakter', _isMinLength, isDark),
                    const SizedBox(height: 8),
                    _buildRequirement('Şifreler eşleşiyor', _isPasswordsMatch, isDark),
                  ],
                ),
              ),
              const SizedBox(height: 36),

              // Şifreyi Güncelle Butonu
              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _handleChangePassword,
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
                  child: _isLoading
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(color: Colors.black, strokeWidth: 2.5),
                        )
                      : const Text('Şifreyi Güncelle'),
                ),
              ),
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRequirement(String text, bool isMet, bool isDark) {
    return Row(
      children: [
        Container(
          width: 18,
          height: 18,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(
              color: isMet ? AppTheme.successColor : (isDark ? Colors.white24 : Colors.grey.shade400),
              width: 1.5,
            ),
            color: isMet ? AppTheme.successColor.withOpacity(0.1) : Colors.transparent,
          ),
          child: isMet
              ? const Icon(Icons.check, color: AppTheme.successColor, size: 12)
              : null,
        ),
        const SizedBox(width: 10),
        Text(
          text,
          style: TextStyle(
            color: isMet ? AppTheme.successColor : (isDark ? Colors.white38 : Colors.black38),
            fontSize: 13,
          ),
        ),
      ],
    );
  }
}
