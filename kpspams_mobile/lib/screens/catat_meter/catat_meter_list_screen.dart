import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/meter_provider.dart';
import '../../widgets/shimmer_loading.dart';
import 'catat_meter_detail_screen.dart';

class CatatMeterListScreen extends StatefulWidget {
  const CatatMeterListScreen({super.key});

  @override
  State<CatatMeterListScreen> createState() => _CatatMeterListScreenState();
}

class _CatatMeterListScreenState extends State<CatatMeterListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MeterProvider>().fetchMeterPeriods();
    });
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final provider = context.watch<MeterProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Catat Meter'),
        actions: [
          IconButton(
            onPressed: () => context.read<MeterProvider>().fetchMeterPeriods(),
            icon: const Icon(Icons.refresh_rounded),
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: colorScheme.secondaryContainer,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Periode Pencatatan',
                    style: theme.textTheme.titleMedium?.copyWith(
                      color: colorScheme.onSecondaryContainer,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Pilih periode untuk melihat detail pencatatan pelanggan.',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: colorScheme.onSecondaryContainer,
                    ),
                  ),
                ],
              ),
            ),
          ),
          Expanded(
            child: provider.isLoading
                ? const ShimmerListLoading(itemCount: 5)
                : provider.errorMessage != null
                ? Center(child: Text(provider.errorMessage!))
                : provider.periods.isEmpty
                ? const Center(child: Text('Belum ada periode catat meter.'))
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    itemCount: provider.periods.length,
                    itemBuilder: (context, index) {
                      final period = provider.periods[index];
                      final progress = period.summary.total == 0
                          ? 0.0
                          : (period.summary.recorded / period.summary.total)
                                .clamp(0.0, 1.0);

                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(24),
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => CatatMeterDetailScreen(
                                  periodId: period.id,
                                ),
                              ),
                            );
                          },
                          child: Padding(
                            padding: const EdgeInsets.all(14),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.all(8),
                                      decoration: BoxDecoration(
                                        color: colorScheme.primaryContainer,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(
                                        Icons.calendar_month_rounded,
                                        size: 18,
                                        color: colorScheme.onPrimaryContainer,
                                      ),
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(
                                        'Periode ${period.label}',
                                        style: theme.textTheme.titleSmall?.copyWith(
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                    ),
                                    const Icon(Icons.chevron_right_rounded),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  'Tercatat ${period.summary.recorded}/${period.summary.total} • Pending ${period.summary.pending}',
                                  style: theme.textTheme.bodyMedium,
                                ),
                                const SizedBox(height: 8),
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: LinearProgressIndicator(
                                    minHeight: 8,
                                    value: progress,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}
