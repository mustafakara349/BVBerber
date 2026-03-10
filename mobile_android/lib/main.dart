import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:mobile_android/firebase_options.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/routes/app_routes.dart';

import 'package:mobile_android/providers/auth_provider.dart';
import 'package:provider/provider.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp(
    options: DefaultFirebaseOptions.currentPlatform,
  );
  
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        // İleride buraya başka provider'lar eklenebilir (Service vb.)
      ],
      child: const BVBarberApp(),
    ),
  );
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