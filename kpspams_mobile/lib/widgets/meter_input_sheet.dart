import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../models/meter_reading_model.dart';
import '../providers/meter_provider.dart';

class MeterInputSheet extends StatefulWidget {
  final int periodId;
  final MeterReadingModel reading;

  const MeterInputSheet({
    super.key,
    required this.periodId,
    required this.reading,
  });

  @override
  State<MeterInputSheet> createState() => _MeterInputSheetState();
}

class _MeterInputSheetState extends State<MeterInputSheet> {
  final _endReadingController = TextEditingController();
  final _noteController = TextEditingController();
  File? _selectedPhoto;

  @override
  void dispose() {
    _endReadingController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final image = await picker.pickImage(
      source: ImageSource.camera,
      imageQuality: 70,
    );

    if (image != null) {
      setState(() {
        _selectedPhoto = File(image.path);
      });
    }
  }

  Future<void> _submit() async {
    final endReading = _endReadingController.text.trim();
    final notes = _noteController.text.trim();

    if (endReading.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Stand akhir wajib diisi.')),
      );
      return;
    }

    final endValue = double.tryParse(endReading);
    final startValue = double.tryParse(widget.reading.startReading ?? '0') ?? 0;

    if (endValue == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Stand akhir harus berupa angka.')),
      );
      return;
    }

    if (endValue < startValue) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Stand akhir tidak boleh lebih kecil dari stand awal.'),
        ),
      );
      return;
    }

    final provider = context.read<MeterProvider>();
    final success = await provider.submitReading(
      widget.periodId,
      widget.reading.id,
      endReading,
      notes,
      photoPath: _selectedPhoto?.path,
    );

    if (!mounted) return;

    if (success) {
      Navigator.pop(context, true);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Pencatatan meter berhasil disimpan.')),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(provider.errorMessage ?? 'Gagal menyimpan data.')),
    );
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<MeterProvider>();

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
            'Input Meter - ${widget.reading.customer?.name ?? '-'}',
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          Text('ID Pelanggan: ${widget.reading.customer?.customerCode ?? '-'}'),
          const SizedBox(height: 4),
          Text('Stand Awal: ${widget.reading.startReading ?? '0'} m³'),
          const SizedBox(height: 16),
          TextField(
            controller: _endReadingController,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: const InputDecoration(
              labelText: 'Stand Akhir (m³)',
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
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _pickImage,
                  icon: const Icon(Icons.camera_alt),
                  label: const Text('Foto Bukti'),
                ),
              ),
              if (_selectedPhoto != null) ...[
                const SizedBox(width: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: Image.file(
                    _selectedPhoto!,
                    width: 56,
                    height: 56,
                    fit: BoxFit.cover,
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 20),
          provider.isLoading
              ? const Center(child: CircularProgressIndicator())
              : ElevatedButton(
                  onPressed: _submit,
                  child: const Text('Simpan Pencatatan'),
                ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}
