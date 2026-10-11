import 'package:flutter/material.dart';
import 'package:mobile_android/screens/auth/login_screen.dart';
import 'package:mobile_android/screens/auth/register_screen.dart';
import 'package:mobile_android/screens/auth/forgot_password_screen.dart';
import 'package:mobile_android/screens/auth/otp_verification_screen.dart';
import 'package:mobile_android/screens/auth/reset_password_screen.dart';
import 'package:mobile_android/screens/auth/welcome_screen.dart';
import 'package:mobile_android/screens/auth/profile_photo_screen.dart';
import 'package:mobile_android/screens/profile/security_screen.dart';
import 'package:mobile_android/screens/profile/support_screen.dart';
import 'package:mobile_android/screens/profile/coupons_screen.dart';
import 'package:mobile_android/screens/main_screen.dart';
import 'package:mobile_android/screens/onboarding/onboarding_screen.dart';
import 'package:mobile_android/screens/appointments/create_appointment_screen.dart';
import 'package:mobile_android/screens/notifications/notifications_screen.dart';
import 'package:mobile_android/screens/services/barber_services_screen.dart';
import 'package:mobile_android/screens/services/cafe_menu_screen.dart';
import 'package:mobile_android/screens/services/sale_products_screen.dart';
import 'package:mobile_android/screens/services/campaigns_screen.dart';

/// Uygulama route tanımları
class AppRoutes {
  AppRoutes._();

  // Route adları
  static const String onboarding = '/onboarding';
  static const String welcome = '/welcome';
  static const String login = '/login';
  static const String register = '/register';
  static const String forgotPassword = '/forgot-password';
  static const String otpVerification = '/otp-verification';
  static const String resetPassword = '/reset-password';
  static const String profilePhoto = '/profile-photo';
  static const String security = '/security';
  static const String support = '/support';
  static const String coupons = '/coupons';
  static const String createAppointment = '/create-appointment';
  static const String notifications = '/notifications';
  static const String barberServices = '/barber-services';
  static const String cafeMenu = '/cafe-menu';
  static const String saleProducts = '/sale-products';
  static const String campaigns = '/campaigns';
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
      case otpVerification:
        final args = settings.arguments as Map<String, dynamic>? ?? {};
        final email = args['email'] as String? ?? '';
        return MaterialPageRoute(
          builder: (_) => OtpVerificationScreen(email: email),
        );
      case resetPassword:
        final args = settings.arguments as Map<String, dynamic>? ?? {};
        final email = args['email'] as String? ?? '';
        final otp = args['otp'] as String? ?? '';
        return MaterialPageRoute(
          builder: (_) => ResetPasswordScreen(email: email, otp: otp),
        );
      case profilePhoto:
        return MaterialPageRoute(
          builder: (_) => const ProfilePhotoScreen(),
        );
      case security:
        return MaterialPageRoute(
          builder: (_) => const SecurityScreen(),
        );
      case support:
        return MaterialPageRoute(
          builder: (_) => const SupportScreen(),
        );
      case coupons:
        return MaterialPageRoute(
          builder: (_) => const CouponsScreen(),
        );
      case createAppointment:
        return MaterialPageRoute(
          builder: (_) => const CreateAppointmentScreen(),
        );
      case notifications:
        return MaterialPageRoute(
          builder: (_) => const NotificationsScreen(),
        );
      case barberServices:
        return MaterialPageRoute(
          builder: (_) => const BarberServicesScreen(),
        );
      case cafeMenu:
        return MaterialPageRoute(
          builder: (_) => const CafeMenuScreen(),
        );
      case saleProducts:
        return MaterialPageRoute(
          builder: (_) => const SaleProductsScreen(),
        );
      case campaigns:
        return MaterialPageRoute(
          builder: (_) => const CampaignsScreen(),
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
