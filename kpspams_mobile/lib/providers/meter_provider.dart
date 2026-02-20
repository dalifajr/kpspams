import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../models/meter_reading_model.dart';
import '../services/api_service.dart';

class MeterProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<MeterReadingModel> _readings = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<MeterReadingModel> get readings => _readings;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  // Asumsi ID periode aktif hardcoded ke 1 untuk demo, ini di produk nyata bisa query dari endpoint terpisah
  final int _activePeriodId = 1;

  Future<void> fetchMeterReadings({String status = 'unrecorded'}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.client.get(
        '/meter-periods/$_activePeriodId/readings',
        queryParameters: {'status': status, 'per_page': 100},
      );

      final List data = response.data['data'] ?? [];
      _readings = data.map((e) => MeterReadingModel.fromJson(e)).toList();
    } on DioException catch (e) {
      _errorMessage = e.message;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> submitReading(
    int readingId,
    String endReading,
    String notes,
  ) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _apiService.client.post(
        '/meter-periods/$_activePeriodId/readings/$readingId',
        data: {'end_reading': endReading, 'notes': notes},
      );
      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (e) {
      _errorMessage = e.response?.data['message'] ?? e.message;
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
