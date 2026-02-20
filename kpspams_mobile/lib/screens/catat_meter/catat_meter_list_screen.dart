import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/meter_provider.dart';

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
      context.read<MeterProvider>().fetchMeterReadings();
    });
  }

  void _showInputForm(BuildContext context, dynamic reading) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => _MeterInputForm(reading: reading),
    );
  }

  @override
  Widget build(BuildContext context) {
    final meterProvider = context.watch<MeterProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Daftar Catat Meter'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () => context.read<MeterProvider>().fetchMeterReadings(),
          ),
        ],
      ),
      body: meterProvider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : meterProvider.errorMessage != null
          ? Center(child: Text(meterProvider.errorMessage!))
          : meterProvider.readings.isEmpty
          ? const Center(child: Text('Tidak ada jadwal baca meter.'))
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: meterProvider.readings.length,
              itemBuilder: (context, index) {
                final reading = meterProvider.readings[index];
                return Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: Theme.of(
                        context,
                      ).colorScheme.primaryContainer,
                      child: Icon(
                        Icons.speed,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                    title: Text(
                      reading.customer?.name ?? 'Anonim',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    subtitle: Text(
                      'ID: ${reading.customer?.customerCode}\nStand Awal: ${reading.startReading ?? 0} m³',
                    ),
                    isThreeLine: true,
                    trailing: const Icon(Icons.chevron_right),
                    onTap: () => _showInputForm(context, reading),
                  ),
                );
              },
            ),
    );
  }
}

class _MeterInputForm extends StatefulWidget {
  final dynamic reading;
  const _MeterInputForm({required this.reading});

  @override
  State<_MeterInputForm> createState() => _MeterInputFormState();
}

class _MeterInputFormState extends State<_MeterInputForm> {
  final _endReadingController = TextEditingController();
  final _noteController = TextEditingController();

  void _submit() async {
    final endReading = _endReadingController.text;
    final notes = _noteController.text;

    if (endReading.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Stand akhir wajib diisi')));
      return;
    }

    final provider = context.read<MeterProvider>();
    final success = await provider.submitReading(
      widget.reading.id,
      endReading,
      notes,
    );

    if (!mounted) return;

    if (success) {
      Navigator.pop(context);
      provider.fetchMeterReadings();
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Berhasil mencatat meter!')));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Gagal menyimpan')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        bottom: MediaQuery.of(context).viewInsets.bottom,
        left: 20,
        right: 20,
        top: 20,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            'Catat Meter: ${widget.reading.customer?.name}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Text(
            'Stand Awal (Bulan Lalu): ${widget.reading.startReading ?? 0} m³',
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _endReadingController,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: const InputDecoration(
              labelText: 'Stand Akhir Saat Ini (m³)',
              filled: true,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _noteController,
            decoration: const InputDecoration(
              labelText: 'Catatan (opsional)',
              filled: true,
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton(onPressed: _submit, child: const Text('Simpan')),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
