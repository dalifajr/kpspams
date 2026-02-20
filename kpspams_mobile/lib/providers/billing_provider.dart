import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../models/bill_model.dart';
import '../services/api_service.dart';

class BillingProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<BillModel> _bills = [];
  bool _isLoading = false;
  String? _errorMessage;
  int? _currentCustomerId;

  // Data customer singkat (metadata dari API)
  Map<String, dynamic>? _customerInfo;

  List<BillModel> get bills => _bills;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  Map<String, dynamic>? get customerInfo => _customerInfo;

  Future<void> fetchCustomerBills(int customerId) async {
    _isLoading = true;
    _errorMessage = null;
    _currentCustomerId = customerId;
    notifyListeners();

    try {
      final response = await _apiService.client.get(
        '/customers/$customerId/bills',
      );

      _customerInfo = response.data['customer'];
      final List data = response.data['bills'] ?? [];
      _bills = data.map((e) => BillModel.fromJson(e)).toList();
    } on DioException catch (e) {
      if (e.response?.statusCode == 403) {
        _errorMessage = "Anda tidak memiliki akses ke pelanggan ini.";
      } else {
        _errorMessage = e.message;
      }
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> payBill(
    int billId,
    int amount,
    String method,
    String? notes,
  ) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _apiService.client.post(
        '/bills/$billId/pay',
        data: {'amount': amount, 'method': method, 'notes': notes},
      );

      _isLoading = false;
      notifyListeners();

      // Refresh list tagihan setelah sukses bayar
      if (_currentCustomerId != null) {
        fetchCustomerBills(_currentCustomerId!);
      }
      return true;
    } on DioException catch (e) {
      _errorMessage = e.response?.data['message'] ?? e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
