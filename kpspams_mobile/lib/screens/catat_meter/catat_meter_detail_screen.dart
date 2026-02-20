import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/bill_model.dart';
import '../../models/meter_reading_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/billing_provider.dart';
import '../../providers/meter_provider.dart';
import '../../widgets/meter_input_sheet.dart';
import '../../widgets/shimmer_loading.dart';
import 'catat_meter_pending_screen.dart';

class CatatMeterDetailScreen extends StatefulWidget {
  final int periodId;

  const CatatMeterDetailScreen({super.key, required this.periodId});

  @override
  State<CatatMeterDetailScreen> createState() => _CatatMeterDetailScreenState();
}

class _CatatMeterDetailScreenState extends State<CatatMeterDetailScreen> {
  final _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<MeterProvider>().fetchMeterReadings(periodId: widget.periodId);
    });
  }

  Future<void> _reload({String? status}) async {
    final provider = context.read<MeterProvider>();
    await provider.fetchMeterReadings(
      periodId: widget.periodId,
      status: status ?? provider.currentStatus,
      search: _searchController.text.trim(),
    );
  }

  Future<void> _openInput(MeterReadingModel reading) async {
    final result = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => MeterInputSheet(periodId: widget.periodId, reading: reading),
    );

    if (result == true && mounted) {
      await _reload();
    }
  }

  Future<void> _publishReading(MeterReadingModel reading) async {
    final billing = context.read<BillingProvider>();
    final success = await billing.publishBillForReading(reading.id);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tagihan berhasil diterbitkan.')),
      );
      await _reload();
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(billing.errorMessage ?? 'Gagal menerbitkan tagihan.')),
    );
  }

  Future<void> _unpublishReading(MeterReadingModel reading) async {
    final billing = context.read<BillingProvider>();
    final success = await billing.unpublishBillForReading(reading.id);

    if (!mounted) return;

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tagihan berhasil dibatalkan.')),
      );
      await _reload();
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(billing.errorMessage ?? 'Gagal membatalkan tagihan.')),
    );
  }

  Future<void> _openBillSelection(MeterReadingModel reading) async {
    final billing = context.read<BillingProvider>();
    final customerId = reading.customerId;

    await billing.fetchCustomerBills(customerId);

    if (!mounted) return;

    if (billing.errorMessage != null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(billing.errorMessage!)),
      );
      return;
    }

    final selectedBill = await showModalBottomSheet<BillModel>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _BillSelectionSheet(bills: billing.bills),
    );

    if (selectedBill == null || !mounted) {
      return;
    }

    final paid = await showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => _PaymentInputSheet(bill: selectedBill),
    );

    if (paid == true && mounted) {
      await _reload();
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final meterProvider = context.watch<MeterProvider>();
    final auth = context.watch<AuthProvider>();
    final period = meterProvider.selectedPeriod;
    final isAdmin = auth.user?.role == 'admin';

    return Scaffold(
      appBar: AppBar(
        title: Text('Periode ${period?.label ?? ''}'),
        actions: [
          IconButton(
            onPressed: () => _reload(),
            icon: const Icon(Icons.refresh_rounded),
          ),
        ],
      ),
      body: Column(
        children: [
          if (period != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: colorScheme.primaryContainer,
                  borderRadius: BorderRadius.circular(24),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Ringkasan Periode',
                      style: theme.textTheme.titleMedium?.copyWith(
                        color: colorScheme.onPrimaryContainer,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: [
                        _StatPill(label: 'Total', value: period.summary.total),
                        _StatPill(label: 'Tercatat', value: period.summary.recorded),
                        _StatPill(label: 'Pending', value: period.summary.pending),
                        _StatPill(label: 'Terbit', value: period.summary.published),
                        _StatPill(label: 'Lunas', value: period.summary.paid),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Align(
                      alignment: Alignment.centerRight,
                      child: OutlinedButton.icon(
                        onPressed: () async {
                          await Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => CatatMeterPendingScreen(
                                periodId: widget.periodId,
                                periodLabel: period.label,
                              ),
                            ),
                          );

                          if (!mounted) return;
                          await _reload();
                        },
                        icon: const Icon(Icons.pending_actions_rounded),
                        label: const Text('Pelanggan Belum Dicatat'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                labelText: 'Cari nama / kode pelanggan',
                prefixIcon: const Icon(Icons.search_rounded),
                suffixIcon: IconButton(
                  onPressed: () => _reload(),
                  icon: const Icon(Icons.arrow_forward_rounded),
                ),
              ),
              onSubmitted: (_) => _reload(),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Wrap(
              spacing: 8,
              children: [
                ChoiceChip(
                  label: const Text('Belum Dicatat'),
                  selected: meterProvider.currentStatus == 'unrecorded',
                  onSelected: (_) => _reload(status: 'unrecorded'),
                ),
                ChoiceChip(
                  label: const Text('Selesai'),
                  selected: meterProvider.currentStatus == 'recorded',
                  onSelected: (_) => _reload(status: 'recorded'),
                ),
                ChoiceChip(
                  label: const Text('Semua'),
                  selected: meterProvider.currentStatus == 'all',
                  onSelected: (_) => _reload(status: 'all'),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Expanded(
            child: meterProvider.isLoading
                ? const ShimmerListLoading(itemCount: 6)
                : meterProvider.errorMessage != null
                ? Center(child: Text(meterProvider.errorMessage!))
                : meterProvider.readings.isEmpty
                ? const Center(child: Text('Data pencatatan tidak ditemukan.'))
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 4, 16, 16),
                    itemCount: meterProvider.readings.length,
                    itemBuilder: (context, index) {
                      final reading = meterProvider.readings[index];
                      final isRecorded = reading.recordedAt != null;
                      final isPublished = reading.billPublishedAt != null;

                      return Card(
                        margin: const EdgeInsets.only(bottom: 10),
                        child: Padding(
                          padding: const EdgeInsets.all(14),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                reading.customer?.name ?? '-',
                                style: theme.textTheme.titleSmall?.copyWith(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text('Kode: ${reading.customer?.customerCode ?? '-'}'),
                              Text('Stand awal: ${reading.startReading ?? '0'} m³'),
                              if (isRecorded)
                                Text('Pemakaian: ${reading.usageM3 ?? '0'} m³'),
                              if (isPublished)
                                Padding(
                                  padding: const EdgeInsets.only(top: 8),
                                  child: Chip(
                                    avatar: const Icon(Icons.receipt_long_rounded, size: 16),
                                    label: const Text('Tagihan Terbit'),
                                    backgroundColor: colorScheme.secondaryContainer,
                                  ),
                                ),
                              const SizedBox(height: 10),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: [
                                  if (!isRecorded)
                                    ElevatedButton.icon(
                                      onPressed: () => _openInput(reading),
                                      icon: const Icon(Icons.edit_rounded),
                                      label: const Text('Catat'),
                                    ),
                                  if (isRecorded && !isPublished)
                                    ElevatedButton.icon(
                                      onPressed: () => _publishReading(reading),
                                      icon: const Icon(Icons.publish_rounded),
                                      label: const Text('Terbitkan'),
                                    ),
                                  if (isPublished)
                                    ElevatedButton.icon(
                                      onPressed: () => _openBillSelection(reading),
                                      icon: const Icon(Icons.payments_rounded),
                                      label: const Text('Bayar'),
                                    ),
                                  if (isPublished && isAdmin)
                                    OutlinedButton.icon(
                                      onPressed: () => _unpublishReading(reading),
                                      icon: const Icon(Icons.undo_rounded),
                                      label: const Text('Batal'),
                                    ),
                                ],
                              ),
                            ],
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

class _StatPill extends StatelessWidget {
  final String label;
  final int value;

  const _StatPill({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: colorScheme.surface.withAlpha(150),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text('$label: $value'),
    );
  }
}

class _BillSelectionSheet extends StatelessWidget {
  final List<BillModel> bills;

  const _BillSelectionSheet({required this.bills});

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Pilih Tagihan Aktif',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 12),
            if (bills.isEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 20),
                child: Text('Tidak ada tagihan aktif.'),
              )
            else
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  itemCount: bills.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 8),
                  itemBuilder: (context, index) {
                    final bill = bills[index];
                    return ListTile(
                      tileColor: Theme.of(context).colorScheme.surfaceContainerHighest,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                      title: Text('Periode ${bill.periodLabel}'),
                      subtitle: Text('Sisa bayar: Rp ${bill.remaining}'),
                      trailing: const Icon(Icons.chevron_right_rounded),
                      onTap: () => Navigator.pop(context, bill),
                    );
                  },
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _PaymentInputSheet extends StatefulWidget {
  final BillModel bill;

  const _PaymentInputSheet({required this.bill});

  @override
  State<_PaymentInputSheet> createState() => _PaymentInputSheetState();
}

class _PaymentInputSheetState extends State<_PaymentInputSheet> {
  late final TextEditingController _amountController;
  final _referenceController = TextEditingController();
  final _notesController = TextEditingController();
  String _method = 'cash';

  @override
  void initState() {
    super.initState();
    _amountController = TextEditingController(text: widget.bill.remaining.toString());
  }

  @override
  void dispose() {
    _amountController.dispose();
    _referenceController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final amount = int.tryParse(_amountController.text.trim()) ?? 0;

    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nominal bayar tidak valid.')),
      );
      return;
    }

    final provider = context.read<BillingProvider>();
    final success = await provider.payBill(
      widget.bill.id,
      amount,
      _method,
      _referenceController.text.trim().isEmpty
          ? null
          : _referenceController.text.trim(),
      _notesController.text.trim().isEmpty ? null : _notesController.text.trim(),
    );

    if (!mounted) return;

    if (success) {
      Navigator.pop(context, true);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pembayaran berhasil dicatat.')),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(provider.errorMessage ?? 'Gagal memproses pembayaran.')),
    );
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<BillingProvider>();
    final theme = Theme.of(context);

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
            'Pembayaran ${widget.bill.periodLabel}',
            style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          Text('Sisa tagihan: Rp ${widget.bill.remaining}'),
          const SizedBox(height: 16),
          TextField(
            controller: _amountController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Nominal Bayar'),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _method,
            decoration: const InputDecoration(labelText: 'Metode Pembayaran'),
            items: const [
              DropdownMenuItem(value: 'cash', child: Text('Cash')),
              DropdownMenuItem(value: 'transfer', child: Text('Transfer')),
              DropdownMenuItem(value: 'qris', child: Text('QRIS')),
            ],
            onChanged: (value) {
              if (value != null) {
                setState(() => _method = value);
              }
            },
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _referenceController,
            decoration: const InputDecoration(labelText: 'No Referensi (opsional)'),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _notesController,
            decoration: const InputDecoration(labelText: 'Catatan (opsional)'),
          ),
          const SizedBox(height: 20),
          provider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : ElevatedButton.icon(
                  onPressed: _submit,
                  icon: const Icon(Icons.check_circle_rounded),
                  label: const Text('Simpan Pembayaran'),
                ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
