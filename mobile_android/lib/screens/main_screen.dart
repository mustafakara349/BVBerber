import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/screens/home/home_screen.dart';
import 'package:mobile_android/screens/appointments/appointments_screen.dart';
import 'package:mobile_android/screens/services/services_screen.dart';
import 'package:mobile_android/screens/profile/profile_screen.dart';

/// Ana navigasyon shell ekranı
///
/// BottomNavigationBar ile 4 ana ekranı birleştirir:
/// Home, Randevular, Hizmetler, Profil
class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;

  List<Widget> get _screens => [
    HomeScreen(
      onNavigateToServices: () {
        setState(() {
          _currentIndex = 2; // Hizmetler sekmesi index'i
        });
      },
      onNavigateToProfile: () {
        setState(() {
          _currentIndex = 3; // Profil sekmesi index'i
        });
      },
    ),
    const AppointmentsScreen(),
    const ServicesScreen(),
    const ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF1A1A1A),
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: Color(0xFF1A1A1A),
          border: Border(
            top: BorderSide(color: Color(0xFF2A2A2A), width: 1),
          ),
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
          },
          backgroundColor: const Color(0xFF1A1A1A),
          type: BottomNavigationBarType.fixed,
          selectedItemColor: AppTheme.secondaryColor,
          unselectedItemColor: Colors.white38,
          selectedFontSize: 12,
          unselectedFontSize: 12,
          elevation: 0,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home),
              label: 'Ana Sayfa',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.calendar_today_outlined),
              activeIcon: Icon(Icons.calendar_today),
              label: 'Randevular',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.content_cut_outlined),
              activeIcon: Icon(Icons.content_cut),
              label: 'Hizmetler',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person_outlined),
              activeIcon: Icon(Icons.person),
              label: 'Profil',
            ),
          ],
        ),
      ),
    );
  }
}
