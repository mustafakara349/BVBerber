import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/routes/app_routes.dart';

import 'package:mobile_android/providers/auth_provider.dart';
import 'package:mobile_android/providers/service_provider.dart';
import 'package:mobile_android/providers/appointment_provider.dart';
import 'package:provider/provider.dart';
import 'package:intl/date_symbol_data_local.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Türkçe tarih formatlaması için gerekli
  await initializeDateFormatting('tr_TR', null);
  
  final authProvider = AuthProvider();
  final isLoggedIn = await authProvider.tryAutoLogin();
  
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider.value(value: authProvider),
        ChangeNotifierProvider(create: (_) => ServiceProvider()),
        ChangeNotifierProvider(create: (_) => AppointmentProvider()),
      ],
      child: BVBarberApp(isLoggedIn: isLoggedIn),
    ),
  );
}

class BVBarberApp extends StatelessWidget {
  final bool isLoggedIn;
  const BVBarberApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BVBarber',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: isLoggedIn ? AppRoutes.main : AppRoutes.initialRoute,
      onGenerateRoute: AppRoutes.onGenerateRoute,
    );
  }
}