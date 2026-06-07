// lib/main.dart
// Entry point aplikasi Pixora Mobile

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'config/theme.dart';
import 'config/routes.dart';
import 'providers/auth_provider.dart';
import 'providers/package_provider.dart';
import 'providers/booking_provider.dart';
import 'providers/calendar_provider.dart';
import 'screens/splash_screen.dart';
import 'screens/onboarding_screen.dart';
import 'screens/main_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/packages/packages_screen.dart';
import 'screens/packages/package_detail_screen.dart';
import 'screens/booking/calendar_screen.dart';
import 'screens/booking/booking_form_screen.dart';
import 'screens/booking/payment_screen.dart';
import 'screens/booking/booking_detail_screen.dart';
import 'screens/profile/profile_screen.dart';
import 'screens/profile/edit_profile_screen.dart';
import 'screens/chat/ai_chat_screen.dart';
import 'package:intl/date_symbol_data_local.dart';

void main() async{
  WidgetsFlutterBinding.ensureInitialized();
  await initializeDateFormatting('id_ID', null);
  SystemChrome.setPreferredOrientations([DeviceOrientation.portraitUp]);
  SystemChrome.setSystemUIOverlayStyle(const SystemUiOverlayStyle(
    statusBarColor: Colors.transparent,
    statusBarIconBrightness: Brightness.dark,
  ));
  runApp(const PixoraApp());
}

class PixoraApp extends StatelessWidget {
  const PixoraApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => PackageProvider()),
        ChangeNotifierProvider(create: (_) => BookingProvider()),
        ChangeNotifierProvider(create: (_) => CalendarProvider()),
      ],
      child: MaterialApp(
        title: 'Pixora Studio',
        debugShowCheckedModeBanner: false,
        theme: PixoraTheme.lightTheme,
        initialRoute: AppRoutes.splash,
        routes: {
          AppRoutes.splash: (_) => const SplashScreen(),
          AppRoutes.onboarding: (_) => const OnboardingScreen(),
          AppRoutes.main: (_) => const MainScreen(),
          AppRoutes.login: (_) => const LoginScreen(),
          AppRoutes.register: (_) => const RegisterScreen(),
          AppRoutes.packages: (_) => const PackagesScreen(),
          AppRoutes.packageDetail: (_) => const PackageDetailScreen(),
          AppRoutes.calendar: (_) => const CalendarScreen(),
          AppRoutes.bookingForm: (_) => const BookingFormScreen(),
          AppRoutes.payment: (_) => const PaymentScreen(),
          AppRoutes.bookingDetail: (_) => const BookingDetailScreen(),
          AppRoutes.profile: (_) => const ProfileScreen(),
          AppRoutes.editProfile: (_) => const EditProfileScreen(),
          AppRoutes.aiChat: (_) => const AiChatScreen(),
        },
      ),
    );
  }
}
