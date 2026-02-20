import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/billing_provider.dart';
import '../../models/bill_model.dart';

class TagihanScreen extends StatefulWidget {
  const TagihanScreen({super.key});

  @override
  State<TagihanScreen> createState() => _TagihanScreenState();
}

class _TagihanScreenState extends State<TagihanScreen> {
  final _customerIdController = TextEditingController();

  void _searchBills() {
    final cxId = int.tryParse(_customerIdController.text);
    if (cxId != null) {
      context.read<BillingProvider>().fetchCustomerBills(cxId);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Masukkan ID Pelanggan berupa angka')),
      );
    }
  }

  void _showPaymentForm(BuildContext context, BillModel bill) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => _PaymentInputForm(bill: bill),
    );
  }

  @override
  Widget build(BuildContext context) {
    final billingProvider = context.watch<BillingProvider>();

    return Scaffold(
      appBar: AppBar(title: const Text('Pembayaran Tagihan')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _customerIdController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'ID Pelanggan',
                      hintText: 'Contoh: 1',
                    ),
                  ),
                ),
                const SizedBox(width: 16),
                ElevatedButton(
                  onPressed: _searchBills,
                  child: const Text('Cari'),
                ),
              ],
            ),
          ),
          if (billingProvider.customerInfo != null)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Card(
                color: Theme.of(context).colorScheme.primaryContainer,
                child: ListTile(
                  title: Text(
                    billingProvider.customerInfo!['name'],
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  subtitle: Text(
                    'ID: ${billingProvider.customerInfo!['customer_code']}',
                  ),
                ),
              ),
            ),

          Expanded(
            child: billingProvider.isLoading
                ? const Center(child: CircularProgressIndicator())
                : billingProvider.errorMessage != null
                ? Center(child: Text(billingProvider.errorMessage!))
                : billingProvider.bills.isEmpty
                ? const Center(child: Text('Tidak ada tagihan aktif.'))
                : ListView.builder(
                    padding: const EdgeInsets.all(16),
                    itemCount: billingProvider.bills.length,
                    itemBuilder: (context, index) {
                      final bill = billingProvider.bills[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ListTile(
                          title: Text(
                            'Periode: ${bill.periodLabel}',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                          subtitle: Text(
                            'Pemakaian: ${bill.usageM3 ?? '-'} m³\n'
                            'Total: Rp ${bill.totalAmount}\n'
                            'Sisa Bayar: Rp ${bill.remaining}',
                          ),
                          isThreeLine: true,
                          trailing: bill.remaining > 0
                              ? ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 16,
                                    ),
                                  ),
                                  onPressed: () =>
                                      _showPaymentForm(context, bill),
                                  child: const Text('Bayar'),
                                )
                              : const Chip(
                                  label: Text('Lunas'),
                                  backgroundColor: Colors.green,
                                  labelStyle: TextStyle(color: Colors.white),
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
  String _paymentMethod = 'cash'; // default

  @override
  void initState() {
    super.initState();
    _amountController = TextEditingController(
      text: widget.bill.remaining.toString(),
    );
  }

  void _submit() async {
    final amount = int.tryParse(_amountController.text) ?? 0;
    final notes = _noteController.text;

    if (amount <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Jumlah nominal tidak valid')),
      );
      return;
    }

    final provider = context.read<BillingProvider>();
    final success = await provider.payBill(
      widget.bill.id,
      amount,
      _paymentMethod,
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
        SnackBar(content: Text(provider.errorMessage ?? 'Gagal memproses')),
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
          const Text(
            'Konfirmasi Pembayaran',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Text('Pembayaran Minimal: Rp ${widget.bill.remaining}'),
          const SizedBox(height: 16),
          TextField(
            controller: _amountController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              labelText: 'Nominal Bayar (Rp)',
              filled: true,
            ),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            value: _paymentMethod,
            decoration: const InputDecoration(
              labelText: 'Metode Pembayaran',
              filled: true,
            ),
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
            decoration: const InputDecoration(
              labelText: 'Catatan tambahan (opsional)',
              filled: true,
            ),
          ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: _submit,
            child: const Text('Bayar Sekarang'),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
