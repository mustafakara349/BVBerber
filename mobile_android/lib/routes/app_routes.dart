import 'package:flutter/material.dart';
import 'package:mobile_android/screens/auth/login_screen.dart';
import 'package:mobile_android/screens/auth/register_screen.dart';
import 'package:mobile_android/screens/auth/forgot_password_screen.dart';
import 'package:mobile_android/screens/auth/welcome_screen.dart';
import 'package:mobile_android/screens/main_screen.dart';
import 'package:mobile_android/screens/onboarding/onboarding_screen.dart';

/// Uygulama route tanımları
class AppRoutes {
  AppRoutes._();

  // Route adları
  static const String onboarding = '/onboarding';
  static const String welcome = '/welcome';
  static const String login = '/login';
  static const String register = '/register';
  static const String forgotPassword = '/forgot-password';
  static const String main = '/main';

  /// İlk route
  static const String initialRoute = onboarding;

  /// Route oluşturucu
  static Route<dynamic> onGenerateRoute(RouteSettings settings) {
    switch (settings.name) {
      case onboarding:
        return MaterialPageRoute(
          builder: (_) => const OnboardingScreen(),
        );
      case welcome:
        return MaterialPageRoute(
          builder: (_) => const WelcomeScreen(),
        );
      case login:
        return MaterialPageRoute(
          builder: (_) => const LoginScreen(),
        );
      case register:
        return MaterialPageRoute(
          builder: (_) => const RegisterScreen(),
        );
      case forgotPassword:
        return MaterialPageRoute(
          builder: (_) => const ForgotPasswordScreen(),
        );
      case main:
        return MaterialPageRoute(
          builder: (_) => const MainScreen(),
        );
      default:
        return MaterialPageRoute(
          builder: (_) => const Scaffold(
            body: Center(
              child: Text('Sayfa bulunamadı'),
            ),
          ),
        );
    }
  }
}
