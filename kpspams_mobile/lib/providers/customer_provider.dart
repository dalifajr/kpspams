import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../models/customer_detail_model.dart';
import '../services/api_service.dart';

class CustomerProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<CustomerDetailModel> _customers = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<CustomerDetailModel> get customers => _customers;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchCustomers({String query = ''}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.client.get(
        '/auth/customers',
        queryParameters: {
          'search': query,
          'per_page': 50, // Get up to 50 for mobile view simplicity
        },
      );

      final List data = response.data['data'] ?? [];
      _customers = data.map((e) => CustomerDetailModel.fromJson(e)).toList();
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem saat mengambil data pelanggan.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
