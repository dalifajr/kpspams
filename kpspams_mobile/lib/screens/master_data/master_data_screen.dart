import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/master_data_provider.dart';
import '../../widgets/shimmer_loading.dart';

class MasterDataScreen extends StatefulWidget {
  const MasterDataScreen({super.key});

  @override
  State<MasterDataScreen> createState() => _MasterDataScreenState();
}

class _MasterDataScreenState extends State<MasterDataScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MasterDataProvider>().fetchMasterData();
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final provider = context.watch<MasterDataProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Master'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () => context.read<MasterDataProvider>().fetchMasterData(),
          ),
        ],
      ),
      body: provider.isLoading
          ? const ShimmerListLoading(itemCount: 8)
          : provider.errorMessage != null
          ? Center(child: Text(provider.errorMessage!))
          : ListView(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: colorScheme.primaryContainer,
                    borderRadius: BorderRadius.circular(24),
                  ),
                  child: Text(
                    'Modul ini disusun berdasarkan tabel migrasi: areas, golongans, customers, tariffs, fees.',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: colorScheme.onPrimaryContainer,
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  'Area',
                  style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                if (provider.areas.isEmpty)
                  const Card(child: ListTile(title: Text('Tidak ada data area.')))
                else
                  ...provider.areas.map(
                    (area) => Card(
                      child: ListTile(
                        leading: const Icon(Icons.map_rounded),
                        title: Text(area.name),
                        subtitle: Text('Slug: ${area.slug}'),
                        trailing: Chip(label: Text('${area.customerCount} pelanggan')),
                      ),
                    ),
                  ),
                const SizedBox(height: 14),
                Text(
                  'Golongan',
                  style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 8),
                if (provider.golongans.isEmpty)
                  const Card(child: ListTile(title: Text('Tidak ada data golongan.')))
                else
                  ...provider.golongans.map(
                    (golongan) => Card(
                      child: ListTile(
                        leading: const Icon(Icons.category_rounded),
                        title: Text('${golongan.code} - ${golongan.name}'),
                        subtitle: Text(
                          'Tarif: ${golongan.tariffsCount} • Non-air fee: ${golongan.feesCount}',
                        ),
                        trailing: Chip(label: Text('${golongan.customersCount} pelanggan')),
                      ),
                    ),
                  ),
              ],
            ),
    );
  }
}
