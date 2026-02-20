import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'app_routes.dart';
import 'theme/app_theme.dart';
import 'providers/auth_provider.dart';
import 'providers/meter_provider.dart';
import 'providers/billing_provider.dart';
import 'providers/customer_provider.dart';
import 'providers/master_data_provider.dart';
import 'services/api_service.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/force_update_password_screen.dart';
import 'screens/dashboard/dashboard_screen.dart';
import 'screens/catat_meter/catat_meter_list_screen.dart';
import 'screens/billing/tagihan_screen.dart';
import 'screens/customer/customer_list_screen.dart';
import 'screens/profile/profile_screen.dart';
import 'screens/master_data/master_data_screen.dart';
import 'widgets/offline_banner.dart';

// Layar awal untuk mengecek status login dari cache
class InitScreen extends StatefulWidget {
  const InitScreen({super.key});

  @override
  State<InitScreen> createState() => _InitScreenState();
}

class _InitScreenState extends State<InitScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().checkStoredAuth();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    if (!auth.isInitChecked) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (auth.isAuthenticated) {
      if (auth.requiresPasswordUpdate) {
        return const ForceUpdatePasswordScreen();
      }
      return const DashboardScreen();
    }

    return const LoginScreen();
  }
}

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

void main() {
  ApiService.onUnauthorized = () {
    navigatorKey.currentState?.pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  };

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => MeterProvider()),
        ChangeNotifierProvider(create: (_) => BillingProvider()),
        ChangeNotifierProvider(create: (_) => CustomerProvider()),
        ChangeNotifierProvider(create: (_) => MasterDataProvider()),
      ],
      child: const KpspamsApp(),
    ),
  );
}

class KpspamsApp extends StatelessWidget {
  const KpspamsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'KPSPAMS Mobile',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      routes: {
        AppRoutes.login: (_) => const LoginScreen(),
        AppRoutes.dashboard: (_) => const DashboardScreen(),
        AppRoutes.catatMeter: (_) => const CatatMeterListScreen(),
        AppRoutes.billing: (_) => const TagihanScreen(),
        AppRoutes.customers: (_) => const CustomerListScreen(),
        AppRoutes.masterData: (_) => const MasterDataScreen(),
        AppRoutes.profile: (_) => const ProfileScreen(),
        AppRoutes.forceUpdatePassword: (_) => const ForceUpdatePasswordScreen(),
      },
      builder: (context, child) {
        return OfflineBanner(child: child!);
      },
      home: const InitScreen(),
    );
  }
}
