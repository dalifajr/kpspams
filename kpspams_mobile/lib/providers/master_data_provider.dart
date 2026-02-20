import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../models/master_data_model.dart';
import '../services/api_service.dart';

class MasterDataProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  List<AreaMasterModel> _areas = [];
  List<GolonganMasterModel> _golongans = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<AreaMasterModel> get areas => _areas;
  List<GolonganMasterModel> get golongans => _golongans;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> fetchMasterData() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final responses = await Future.wait([
        _apiService.getWithFallback(['/auth/areas', '/areas']),
        _apiService.getWithFallback(['/auth/golongans', '/golongans']),
      ]);

      final areaData = responses[0].data;
      final golonganData = responses[1].data;

      final List areaList = areaData is List ? areaData : (areaData['data'] ?? []);
      final List golonganList = golonganData['data'] ?? [];

      _areas = areaList.map((e) => AreaMasterModel.fromJson(e)).toList();
      _golongans = golonganList.map((e) => GolonganMasterModel.fromJson(e)).toList();
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
    } catch (_) {
      _errorMessage = 'Terjadi kesalahan saat memuat data master.';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
