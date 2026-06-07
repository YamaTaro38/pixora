// lib/screens/main_screen.dart
// Shell utama dengan bottom navigation bar

import 'package:flutter/material.dart';
import 'home/home_screen.dart';
import 'packages/packages_screen.dart';
import 'chat/ai_chat_screen.dart';
import 'booking/calendar_screen.dart';
import 'profile/profile_screen.dart';
import '../widgets/bottom_nav_bar.dart';

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});
  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;
  final List<Widget> _screens = [
    const HomeScreen(),
    const PackagesScreen(),
    const AiChatScreen(),
    const CalendarScreen(),
    const ProfileScreen(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: _screens),
      bottomNavigationBar: PixoraBottomNavBar(
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
      ),
    );
  }
}
