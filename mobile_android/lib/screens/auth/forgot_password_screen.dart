import 'package:flutter/material.dart';

/// Şifre sıfırlama ekranı
/// 
/// Tasarım detayları proje ilerledikçe eklenecek.
class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Şifre Sıfırla'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // TODO: Tasarım eklenecek
              const Icon(
                Icons.lock_reset,
                size: 80,
                color: Colors.grey,
              ),
              const SizedBox(height: 24),
              Text(
                'Şifrenizi sıfırlamak için kayıtlı e-posta adresinizi girin.',
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyLarge,
              ),
              const SizedBox(height: 24),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(
                  labelText: 'E-posta',
                  prefixIcon: Icon(Icons.email_outlined),
                ),
              ),
              const SizedBox(height: 24),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    // TODO: Şifre sıfırlama işlemi
                  },
                  child: const Text('Sıfırlama Bağlantısı Gönder'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
