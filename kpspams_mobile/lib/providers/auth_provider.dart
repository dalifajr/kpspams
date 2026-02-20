import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';

class AuthProvider extends ChangeNotifier {
  final ApiService _apiService = ApiService();

  UserModel? _user;
  bool _isLoading = false;
  String? _errorMessage;

  UserModel? get user => _user;
  bool _isInitChecked = false;

  bool get isAuthenticated => _user != null;
  bool get requiresPasswordUpdate => _user?.mustUpdatePassword ?? false;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isInitChecked => _isInitChecked;

  Future<void> checkStoredAuth() async {
    final token = await _apiService.storage.read(key: 'auth_token');
    if (token != null) {
      try {
        final response = await _apiService.client.get('/auth/me');
        _user = UserModel.fromJson(response.data['user']);
      } catch (e) {
        // Token invalid atau API down
        await logout();
      }
    }
    _isInitChecked = true;
    notifyListeners();
  }

  Future<bool> login(String phoneNumber, String password) async {
    _setLoading(true);
    _clearError();

    try {
      final response = await _apiService.client.post(
        '/auth/login',
        data: {
          'phone_number': phoneNumber,
          'password': password,
          'device_name': 'flutter-android',
        },
      );

      final data = response.data;
      final token = data['access_token'];

      // Simpan token ke secure storage
      await _apiService.storage.write(key: 'auth_token', value: token);

      // Simpan user ke state
      _user = UserModel.fromJson(data['user']);
      _setLoading(false);
      return true;
    } on DioException catch (e) {
      if (e.response != null && e.response!.statusCode == 422) {
        _errorMessage = ApiService.extractErrorMessage(e);
      } else if (e.response != null && e.response!.statusCode == 403) {
        _errorMessage = ApiService.extractErrorMessage(e);
      } else {
        _errorMessage = ApiService.extractErrorMessage(e);
      }
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'SysErr: $e';
      _setLoading(false);
      return false;
    }
  }

  Future<void> logout() async {
    try {
      // Hapus token di server
      await _apiService.client.post('/auth/logout');
    } catch (_) {
      // Abaikan error saat logout api, yang terpenting storage lokal dibersihkan
    }
    await _apiService.storage.delete(key: 'auth_token');
    _user = null;
    notifyListeners();
  }

  Future<bool> updateProfile(String name, String addressShort) async {
    _setLoading(true);
    _clearError();
    try {
      final response = await _apiService.client.put(
        '/auth/profile/update',
        data: {'name': name, 'address_short': addressShort},
      );
      _user = UserModel.fromJson(response.data['user']);
      _setLoading(false);
      return true;
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
      _setLoading(false);
      return false;
    }
  }

  Future<bool> forceUpdatePassword(
    String password,
    String passwordConfirmation,
  ) async {
    _setLoading(true);
    _clearError();
    try {
      final response = await _apiService.client.put(
        '/auth/password/force-update',
        data: {
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );

      _user = UserModel.fromJson(response.data['user']);
      _setLoading(false);
      return true;
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
      _setLoading(false);
      return false;
    }
  }

  Future<bool> uploadAvatar(String imagePath) async {
    _setLoading(true);
    _clearError();
    try {
      final formData = FormData.fromMap({
        'avatar': await MultipartFile.fromFile(imagePath),
      });
      await _apiService.client.post('/auth/profile/avatar', data: formData);

      // Update data user di dalam state
      final userResponse = await _apiService.client.get('/auth/me');
      _user = UserModel.fromJson(userResponse.data['user']);

      _setLoading(false);
      return true;
    } on DioException catch (e) {
      _errorMessage = ApiService.extractErrorMessage(e);
      _setLoading(false);
      return false;
    } catch (e) {
      _errorMessage = 'Terjadi kesalahan sistem.';
      _setLoading(false);
      return false;
    }
  }

  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  void _clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
