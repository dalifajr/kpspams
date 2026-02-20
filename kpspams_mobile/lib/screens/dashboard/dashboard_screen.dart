import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../app_routes.dart';
import '../../providers/auth_provider.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final user = context.watch<AuthProvider>().user;

    final menus = _menusForRole(user?.role);

    return Scaffold(
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
                child: Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: colorScheme.primaryContainer,
                    borderRadius: BorderRadius.circular(28),
                  ),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 28,
                        backgroundColor: colorScheme.onPrimaryContainer.withAlpha(28),
                        backgroundImage: user?.avatarUrl != null
                            ? NetworkImage(user!.avatarUrl!)
                            : null,
                        child: user?.avatarUrl == null
                            ? Icon(
                                Icons.person,
                                color: colorScheme.onPrimaryContainer,
                                size: 28,
                              )
                            : null,
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Halo, ${user?.name ?? 'Pengguna'}',
                              style: theme.textTheme.titleLarge?.copyWith(
                                color: colorScheme.onPrimaryContainer,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Peran: ${user?.role.toUpperCase() ?? '-'}',
                              style: theme.textTheme.bodyMedium?.copyWith(
                                color: colorScheme.onPrimaryContainer,
                              ),
                            ),
                          ],
                        ),
                      ),
                      IconButton(
                        tooltip: 'Logout',
                        onPressed: () => context.read<AuthProvider>().logout(),
                        icon: Icon(
                          Icons.logout_rounded,
                          color: colorScheme.onPrimaryContainer,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 8),
                child: Text(
                  'Menu Fitur',
                  style: theme.textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
              sliver: SliverGrid.builder(
                itemCount: menus.length,
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  mainAxisSpacing: 12,
                  crossAxisSpacing: 12,
                  childAspectRatio: 0.98,
                ),
                itemBuilder: (context, index) {
                  final item = menus[index];

                  return _MenuCard(
                    title: item.title,
                    subtitle: item.subtitle,
                    icon: item.icon,
                    onTap: () => Navigator.pushNamed(context, item.routeName),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  List<_DashboardMenuItem> _menusForRole(String? role) {
    final baseMenus = <_DashboardMenuItem>[
      const _DashboardMenuItem(
        title: 'Catat Meter',
        subtitle: 'Periode & pencatatan',
        icon: Icons.speed_rounded,
        routeName: AppRoutes.catatMeter,
      ),
      const _DashboardMenuItem(
        title: 'Tagihan',
        subtitle: 'Pembayaran pelanggan',
        icon: Icons.receipt_long_rounded,
        routeName: AppRoutes.billing,
      ),
      const _DashboardMenuItem(
        title: 'Pelanggan',
        subtitle: 'Data pelanggan',
        icon: Icons.groups_2_rounded,
        routeName: AppRoutes.customers,
      ),
      const _DashboardMenuItem(
        title: 'Data Master',
        subtitle: 'Area & golongan',
        icon: Icons.account_tree_rounded,
        routeName: AppRoutes.masterData,
      ),
      const _DashboardMenuItem(
        title: 'Profil',
        subtitle: 'Akun & avatar',
        icon: Icons.manage_accounts_rounded,
        routeName: AppRoutes.profile,
      ),
    ];

    if (role == 'user') {
      return baseMenus
          .where((item) =>
              item.routeName == AppRoutes.billing ||
              item.routeName == AppRoutes.profile)
          .toList();
    }

    return baseMenus;
  }
}

class _MenuCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final IconData icon;
  final VoidCallback onTap;

  const _MenuCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(24),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: colorScheme.secondaryContainer,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  icon,
                  color: colorScheme.onSecondaryContainer,
                  size: 22,
                ),
              ),
              const Spacer(),
              Text(
                title,
                style: theme.textTheme.titleSmall?.copyWith(
                  fontWeight: FontWeight.w700,
                ),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                style: theme.textTheme.bodySmall?.copyWith(
                  color: colorScheme.onSurfaceVariant,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DashboardMenuItem {
  final String title;
  final String subtitle;
  final IconData icon;
  final String routeName;

  const _DashboardMenuItem({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.routeName,
  });
}
