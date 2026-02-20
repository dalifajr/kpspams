import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/meter_reading_model.dart';
import '../../providers/meter_provider.dart';
import '../../widgets/meter_input_sheet.dart';
import '../../widgets/shimmer_loading.dart';

class CatatMeterPendingScreen extends StatefulWidget {
  final int periodId;
  final String periodLabel;

  const CatatMeterPendingScreen({
    super.key,
    required this.periodId,
    required this.periodLabel,
  });

  @override
  State<CatatMeterPendingScreen> createState() => _CatatMeterPendingScreenState();
}

class _CatatMeterPendingScreenState extends State<CatatMeterPendingScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MeterProvider>().fetchMeterReadings(
        periodId: widget.periodId,
        status: 'unrecorded',
      );
    });
  }

  Future<void> _openInput(MeterReadingModel reading) async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) => MeterInputSheet(periodId: widget.periodId, reading: reading),
    );

    if (result == true && mounted) {
      await context.read<MeterProvider>().fetchMeterReadings(
        periodId: widget.periodId,
        status: 'unrecorded',
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<MeterProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Text('Pending ${widget.periodLabel}'),
      ),
      body: provider.isLoading
          ? const ShimmerListLoading(itemCount: 6)
          : provider.errorMessage != null
          ? Center(child: Text(provider.errorMessage!))
          : provider.readings.isEmpty
          ? const Center(child: Text('Semua pelanggan sudah dicatat.'))
          : Column(
              children: [
                Padding(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                  child: Align(
                    alignment: Alignment.centerLeft,
                    child: Text(
                      'Total pending: ${provider.readings.length}',
                      style: Theme.of(context).textTheme.titleSmall,
                    ),
                  ),
                ),
                Expanded(
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    itemCount: provider.readings.length,
                    itemBuilder: (context, index) {
                      final reading = provider.readings[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 10),
                        child: ListTile(
                          title: Text(
                            reading.customer?.name ?? '-',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          subtitle: Text(
                            'Kode: ${reading.customer?.customerCode ?? '-'}\n'
                            'Stand awal: ${reading.startReading ?? '0'} m³',
                          ),
                          isThreeLine: true,
                          trailing: ElevatedButton(
                            onPressed: () => _openInput(reading),
                            child: const Text('Catat'),
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
