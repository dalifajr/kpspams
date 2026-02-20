import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../models/meter_reading_model.dart';
import '../models/meter_period_model.dart';
import '../services/api_service.dart';

class MeterProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<MeterPeriodModel> _periods = [];
  MeterPeriodModel? _selectedPeriod;
  List<MeterReadingModel> _readings = [];
  bool _isLoading = false;
  String? _errorMessage;
  String _currentStatus = 'unrecorded';
  String _currentSearch = '';

  List<MeterPeriodModel> get periods => _periods;
  MeterPeriodModel? get selectedPeriod => _selectedPeriod;
  List<MeterReadingModel> get readings => _readings;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String get currentStatus => _currentStatus;
  String get currentSearch => _currentSearch;

  Future<void> fetchMeterPeriods() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _apiService.getWithFallback(
        ['/auth/meter-periods', '/meter-periods'],
        queryParameters: {'per_page': 24},
      );

      final List data = response.data['data'] ?? [];
      _periods = data.map((e) => MeterPeriodModel.fromJson(e)).toList();

      if (_periods.isNotEmpty) {
        final activeResponse = await _apiService.getWithFallback(
          ['/auth/meter-periods/active', '/meter-periods/active'],
        );
        final activeData = activeResponse.data['data'] ?? {};
        final activeId = activeData['id'];

        _selectedPeriod = _periods.firstWhere(
          (period) => period.id == activeId,
          orElse: () => _periods.first,
        );
      } else {
        _selectedPeriod = null;
      }
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchPeriodDetail(int periodId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _loadPeriodDetail(periodId);
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchMeterReadings({
    required int periodId,
    String status = 'unrecorded',
    String search = '',
  }) async {
    _currentStatus = status;
    _currentSearch = search;

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _loadPeriodDetail(periodId);

      final response = await _apiService.getWithFallback(
        [
          '/auth/meter-periods/$periodId/readings',
          '/meter-periods/$periodId/readings',
        ],
        queryParameters: {
          'status': status,
          'search': search,
          'per_page': 100,
        },
      );

      final List data = response.data['data'] ?? [];
      _readings = data.map((e) => MeterReadingModel.fromJson(e)).toList();
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> _loadPeriodDetail(int periodId) async {
    final response = await _apiService.getWithFallback([
      '/auth/meter-periods/$periodId',
      '/meter-periods/$periodId',
    ]);
    _selectedPeriod = MeterPeriodModel.fromJson(response.data['data'] ?? {});
  }

  Future<bool> submitReading(
    int periodId,
    int readingId,
    String endReading,
    String notes, {
    String? photoPath,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      Map<String, dynamic> dataMap = {
        'end_reading': endReading,
        'notes': notes,
      };

      if (photoPath != null) {
        dataMap['photo'] = await MultipartFile.fromFile(photoPath);
      }

      final formData = FormData.fromMap(dataMap);

      try {
        await _apiService.client.post(
          '/auth/meter-periods/$periodId/readings/$readingId',
          data: formData,
        );
      } on DioException catch (error) {
        if (error.response?.statusCode != 404) {
          rethrow;
        }

        await _apiService.client.post(
          '/meter-periods/$periodId/readings/$readingId',
          data: formData,
        );
      }
      _isLoading = false;
      notifyListeners();
      return true;
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }
}
