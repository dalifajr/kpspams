import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/billing_provider.dart';
import '../../models/bill_model.dart';
import '../../widgets/shimmer_loading.dart';

class TagihanScreen extends StatefulWidget {
  const TagihanScreen({super.key});

  @override
  State<TagihanScreen> createState() => _TagihanScreenState();
}

class _TagihanScreenState extends State<TagihanScreen> {
  final _customerIdController = TextEditingController();

  @override
  void dispose() {
    _customerIdController.dispose();
    super.dispose();
  }

  void _searchBills() {
    final cxId = int.tryParse(_customerIdController.text.trim());
    if (cxId != null) {
      context.read<BillingProvider>().fetchCustomerBills(cxId);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Masukkan ID Pelanggan berupa angka.')),
      );
    }
  }

  void _showPaymentForm(BuildContext context, BillModel bill) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => _PaymentInputForm(bill: bill),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final billingProvider = context.watch<BillingProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Tagihan & Pembayaran')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: colorScheme.tertiaryContainer,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Cari Tagihan Pelanggan',
                    style: theme.textTheme.titleMedium?.copyWith(
                      color: colorScheme.onTertiaryContainer,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Masukkan ID pelanggan untuk melihat tagihan aktif dan melakukan pembayaran.',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: colorScheme.onTertiaryContainer,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _customerIdController,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'ID Pelanggan',
                            hintText: 'Contoh: 1',
                            prefixIcon: Icon(Icons.badge_rounded),
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      ElevatedButton.icon(
                        onPressed: _searchBills,
                        icon: const Icon(Icons.search_rounded),
                        label: const Text('Cari'),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
          if (billingProvider.customerInfo != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
              child: Card(
                color: colorScheme.primaryContainer,
                child: ListTile(
                  leading: Icon(
                    Icons.person_rounded,
                    color: colorScheme.onPrimaryContainer,
                  ),
                  title: Text(
                    billingProvider.customerInfo!['name'],
                    style: TextStyle(
                      fontWeight: FontWeight.w700,
                      color: colorScheme.onPrimaryContainer,
                    ),
                  ),
                  subtitle: Text(
                    'ID: ${billingProvider.customerInfo!['customer_code']}',
                    style: TextStyle(color: colorScheme.onPrimaryContainer),
                  ),
                ),
              ),
            ),
          Expanded(
            child: billingProvider.isLoading
                ? const ShimmerListLoading(itemCount: 4)
                : billingProvider.errorMessage != null
                ? Center(child: Text(billingProvider.errorMessage!))
                : billingProvider.bills.isEmpty
                ? const Center(child: Text('Tidak ada tagihan aktif.'))
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 6, 16, 16),
                    itemCount: billingProvider.bills.length,
                    itemBuilder: (context, index) {
                      final bill = billingProvider.bills[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: Padding(
                          padding: const EdgeInsets.all(14),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'Periode ${bill.periodLabel}',
                                style: theme.textTheme.titleSmall?.copyWith(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Text('Pemakaian: ${bill.usageM3 ?? '-'} m³'),
                              Text('Total: Rp ${bill.totalAmount}'),
                              Text('Sisa bayar: Rp ${bill.remaining}'),
                              const SizedBox(height: 10),
                              bill.remaining > 0
                                  ? ElevatedButton.icon(
                                      onPressed: () => _showPaymentForm(context, bill),
                                      icon: const Icon(Icons.payments_rounded),
                                      label: const Text('Bayar'),
                                    )
                                  : Chip(
                                      avatar: const Icon(Icons.check_circle_rounded, size: 16),
                                      label: const Text('Lunas'),
                                      backgroundColor: colorScheme.secondaryContainer,
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

class _PaymentInputForm extends StatefulWidget {
  final BillModel bill;
  const _PaymentInputForm({required this.bill});

  @override
  State<_PaymentInputForm> createState() => _PaymentInputFormState();
}

class _PaymentInputFormState extends State<_PaymentInputForm> {
  late TextEditingController _amountController;
  final _noteController = TextEditingController();
  String _paymentMethod = 'cash';

  @override
  void initState() {
    super.initState();
    _amountController = TextEditingController(text: widget.bill.remaining.toString());
  }

  @override
  void dispose() {
    _amountController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final amount = int.tryParse(_amountController.text.trim()) ?? 0;
    final notes = _noteController.text;

    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Jumlah nominal tidak valid.')),
      );
      return;
    }

    final provider = context.read<BillingProvider>();
    final success = await provider.payBill(
      widget.bill.id,
      amount,
      _paymentMethod,
      null,
      notes,
    );

    if (!mounted) return;

    if (success) {
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pembayaran berhasil dicatat!')),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(provider.errorMessage ?? 'Gagal memproses pembayaran.')),
      );
    }
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
            'Konfirmasi Pembayaran',
            style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          Text('Sisa tagihan: Rp ${widget.bill.remaining}'),
          const SizedBox(height: 16),
          TextField(
            controller: _amountController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Nominal Bayar (Rp)'),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            initialValue: _paymentMethod,
            decoration: const InputDecoration(labelText: 'Metode Pembayaran'),
            items: const [
              DropdownMenuItem(value: 'cash', child: Text('Uang Tunai')),
              DropdownMenuItem(value: 'transfer', child: Text('Transfer Bank')),
              DropdownMenuItem(value: 'qris', child: Text('QRIS')),
            ],
            onChanged: (val) {
              if (val != null) setState(() => _paymentMethod = val);
            },
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _noteController,
            decoration: const InputDecoration(labelText: 'Catatan tambahan (opsional)'),
          ),
          const SizedBox(height: 20),
          provider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : ElevatedButton.icon(
                  onPressed: _submit,
                  icon: const Icon(Icons.check_circle_rounded),
                  label: const Text('Bayar Sekarang'),
                ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
