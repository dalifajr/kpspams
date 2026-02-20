import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/customer_provider.dart';
import '../../models/customer_detail_model.dart';
import '../../widgets/shimmer_loading.dart';

class CustomerListScreen extends StatefulWidget {
  const CustomerListScreen({super.key});

  @override
  State<CustomerListScreen> createState() => _CustomerListScreenState();
}

class _CustomerListScreenState extends State<CustomerListScreen> {
  final _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<CustomerProvider>().fetchCustomers();
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _onSearch() {
    context.read<CustomerProvider>().fetchCustomers(query: _searchController.text);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final colorScheme = theme.colorScheme;
    final provider = context.watch<CustomerProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Data Pelanggan'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            onPressed: () {
              _searchController.clear();
              context.read<CustomerProvider>().fetchCustomers();
            },
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
                    'Cari Pelanggan',
                    style: theme.textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                      color: colorScheme.onSecondaryContainer,
                    ),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: _searchController,
                    decoration: const InputDecoration(
                      labelText: 'Nama / Kode Pelanggan',
                      prefixIcon: Icon(Icons.search_rounded),
                    ),
                    onSubmitted: (_) => _onSearch(),
                  ),
                ],
              ),
            ),
          ),
          Expanded(
            child: provider.isLoading
                ? const ShimmerListLoading(itemCount: 6)
                : provider.errorMessage != null
                ? Center(child: Text(provider.errorMessage!))
                : provider.customers.isEmpty
                ? const Center(child: Text('Data pelanggan tidak ditemukan.'))
                : ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    itemCount: provider.customers.length,
                    itemBuilder: (context, index) {
                      final CustomerDetailModel customer = provider.customers[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 12),
                        child: ExpansionTile(
                          leading: CircleAvatar(
                            backgroundColor: colorScheme.primaryContainer,
                            child: Icon(
                              Icons.person_rounded,
                              color: colorScheme.onPrimaryContainer,
                            ),
                          ),
                          title: Text(
                            customer.name,
                            style: theme.textTheme.titleSmall?.copyWith(
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          subtitle: Text('Kode: ${customer.customerCode}'),
                          childrenPadding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
                          children: [
                            _DetailRow(label: 'Telepon', value: customer.phone ?? '-'),
                            const SizedBox(height: 8),
                            _DetailRow(label: 'Area', value: customer.areaName ?? '-'),
                            const SizedBox(height: 8),
                            _DetailRow(label: 'Golongan', value: customer.golonganName ?? '-'),
                            const SizedBox(height: 8),
                            _DetailRow(label: 'Alamat', value: customer.address ?? '-'),
                          ],
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _onSearch,
        icon: const Icon(Icons.search_rounded),
        label: const Text('Cari'),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;

  const _DetailRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    final colorScheme = Theme.of(context).colorScheme;

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 80,
          child: Text(
            label,
            style: TextStyle(
              color: colorScheme.onSurfaceVariant,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
        const Text(': '),
        Expanded(child: Text(value)),
      ],
    );
  }
}
