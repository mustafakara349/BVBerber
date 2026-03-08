import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/routes/app_routes.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  // TODO: Firebase.initializeApp() eklenecek
  runApp(const BVBarberApp());
}

class BVBarberApp extends StatelessWidget {
  const BVBarberApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BVBarber',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: AppRoutes.initialRoute,
      onGenerateRoute: AppRoutes.onGenerateRoute,
    );
  }
}