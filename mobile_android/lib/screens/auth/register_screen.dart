import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/utils/validators.dart';

/// Kayıt ekranı – Yeni Hesap Oluştur
class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();
  bool _obscurePassword = true;
  bool _obscureConfirmPassword = true;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  InputDecoration _inputDecoration({
    required String label,
    required String hint,
    required IconData icon,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      labelText: label,
      labelStyle: const TextStyle(color: Colors.white38, fontSize: 12),
      hintText: hint,
      hintStyle: const TextStyle(color: Colors.white24),
      prefixIcon: Icon(icon, color: Colors.white38, size: 20),
      suffixIcon: suffixIcon,
      filled: true,
      fillColor: const Color(0xFF2A2A2A),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF3A3A3A)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppTheme.secondaryColor, width: 1.5),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A1A1A),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Form(
            key: _formKey,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const SizedBox(height: 8),
                // Geri butonu
                IconButton(
                  onPressed: () => Navigator.pop(context),
                  icon: const Icon(Icons.arrow_back, color: Colors.white),
                  padding: EdgeInsets.zero,
                  constraints: const BoxConstraints(),
                ),
                const SizedBox(height: 16),
                // Logo
                Center(
                  child: Container(
                    width: 64,
                    height: 64,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: AppTheme.secondaryColor, width: 2),
                    ),
                    child: const Icon(
                      Icons.content_cut,
                      color: AppTheme.secondaryColor,
                      size: 30,
                    ),
                  ),
                ),
                const SizedBox(height: 24),
                // Başlık
                const Center(
                  child: Text(
                    'Yeni Hesap Oluştur',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(height: 32),
                // Ad Soyad
                const Text('Ad Soyad', style: TextStyle(color: Colors.white54, fontSize: 13)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _nameController,
                  style: const TextStyle(color: Colors.white),
                  validator: Validators.name,
                  decoration: _inputDecoration(
                    label: '',
                    hint: 'Adınızı ve soyadınızı girin',
                    icon: Icons.person_outlined,
                  ),
                ),
                const SizedBox(height: 20),
                // E-posta
                const Text('E-posta', style: TextStyle(color: Colors.white54, fontSize: 13)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  style: const TextStyle(color: Colors.white),
                  validator: Validators.email,
                  decoration: _inputDecoration(
                    label: '',
                    hint: 'eposta@adresiniz.com',
                    icon: Icons.email_outlined,
                  ),
                ),
                const SizedBox(height: 20),
                // Telefon Numarası
                const Text('Telefon Numarası', style: TextStyle(color: Colors.white54, fontSize: 13)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  style: const TextStyle(color: Colors.white),
                  validator: Validators.phone,
                  decoration: _inputDecoration(
                    label: '',
                    hint: '05XX XXX XX XX',
                    icon: Icons.phone_outlined,
                  ),
                ),
                const SizedBox(height: 20),
                // Şifre
                const Text('Şifre', style: TextStyle(color: Colors.white54, fontSize: 13)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _passwordController,
                  obscureText: _obscurePassword,
                  style: const TextStyle(color: Colors.white),
                  validator: Validators.password,
                  decoration: _inputDecoration(
                    label: '',
                    hint: '••••••••',
                    icon: Icons.lock_outlined,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscurePassword ? Icons.visibility_off : Icons.visibility,
                        color: Colors.white38,
                        size: 20,
                      ),
                      onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                // Şifre Tekrar
                const Text('Şifre Tekrar', style: TextStyle(color: Colors.white54, fontSize: 13)),
                const SizedBox(height: 8),
                TextFormField(
                  controller: _confirmPasswordController,
                  obscureText: _obscureConfirmPassword,
                  style: const TextStyle(color: Colors.white),
                  validator: (value) => Validators.confirmPassword(value, _passwordController.text),
                  decoration: _inputDecoration(
                    label: '',
                    hint: '••••••••',
                    icon: Icons.lock_outlined,
                    suffixIcon: IconButton(
                      icon: Icon(
                        _obscureConfirmPassword ? Icons.visibility_off : Icons.visibility,
                        color: Colors.white38,
                        size: 20,
                      ),
                      onPressed: () => setState(() => _obscureConfirmPassword = !_obscureConfirmPassword),
                    ),
                  ),
                ),
                const SizedBox(height: 32),
                // Kayıt Ol Butonu
                SizedBox(
                  width: double.infinity,
                  height: 52,
                  child: ElevatedButton(
                    onPressed: () {
                      if (_formKey.currentState!.validate()) {
                        // TODO: Firebase kayıt işlemi
                      }
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.secondaryColor,
                      foregroundColor: Colors.black,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      elevation: 4,
                      textStyle: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    child: const Text('Kayıt Ol'),
                  ),
                ),
                const SizedBox(height: 24),
                // Zaten hesabınız var mı?
                Center(
                  child: RichText(
                    text: TextSpan(
                      style: const TextStyle(color: Colors.white54, fontSize: 14),
                      children: [
                        const TextSpan(text: 'Zaten hesabınız var mı? '),
                        TextSpan(
                          text: 'Giriş Yap',
                          style: const TextStyle(
                            color: AppTheme.secondaryColor,
                            fontWeight: FontWeight.bold,
                          ),
                          recognizer: TapGestureRecognizer()
                            ..onTap = () {
                              Navigator.pop(context);
                            },
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                // Footer
                const Center(
                  child: Text(
                    '© 2026 B&V Coffe Barber. Tüm hakları saklıdır.',
                    style: TextStyle(color: Colors.white24, fontSize: 11),
                  ),
                ),
                const SizedBox(height: 24),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
