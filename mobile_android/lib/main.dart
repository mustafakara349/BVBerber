import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/routes/app_routes.dart';

import 'package:mobile_android/providers/auth_provider.dart';
import 'package:mobile_android/providers/service_provider.dart';
import 'package:mobile_android/providers/appointment_provider.dart';
import 'package:provider/provider.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:mobile_android/firebase_options.dart';
import 'package:mobile_android/services/push_notification_service.dart';
import 'package:mobile_android/services/notification_service.dart';

import 'package:mobile_android/providers/cafe_provider.dart';
import 'package:mobile_android/providers/sale_products_provider.dart';
import 'package:mobile_android/providers/campaign_provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Türkçe tarih formatlaması için gerekli
  await initializeDateFormatting('tr_TR', null);

  // Firebase ve Arka Plan Push Bildirim Servisi (FCM)
  try {
    await Firebase.initializeApp(
      options: DefaultFirebaseOptions.currentPlatform,
    );
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
    
    final pushService = PushNotificationService();
    await pushService.initialize();
  } catch (e) {
    debugPrint('Firebase başlatma uyarısı: $e');
  }

  // Yerel randevu hatırlatıcı servisi
  final notificationService = NotificationService();
  await notificationService.initialize();
  await notificationService.requestPermission();
  
  final authProvider = AuthProvider();
  final isLoggedIn = await authProvider.tryAutoLogin();
  
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: authProvider),
        ChangeNotifierProvider(create: (_) => ServiceProvider()),
        ChangeNotifierProvider(create: (_) => AppointmentProvider()),
        ChangeNotifierProvider(create: (_) => CafeProvider()),
        ChangeNotifierProvider(create: (_) => SaleProductsProvider()),
        ChangeNotifierProvider(create: (_) => CampaignProvider()),
      ],
      child: BVBarberApp(isLoggedIn: isLoggedIn),
    ),
  );
}

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

class BVBarberApp extends StatelessWidget {
  final bool isLoggedIn;
  const BVBarberApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'BVBarber',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: ThemeMode.system,
      initialRoute: isLoggedIn ? AppRoutes.main : AppRoutes.initialRoute,
      onGenerateRoute: AppRoutes.onGenerateRoute,
    );
  }
}