import 'package:flutter/material.dart';
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

  final List<Widget> _screens = const [
    HomeScreen(),
    AppointmentsScreen(),
    ServicesScreen(),
    ProfileScreen(),
  ];

  final List<String> _titles = const [
    'Ana Sayfa',
    'Randevular',
    'Hizmetler',
    'Profil',
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_titles[_currentIndex]),
      ),
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
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
    );
  }
}
