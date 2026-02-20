import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiService {
  static void Function()? onUnauthorized;

  final Dio _dio = Dio();
  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  // URL API Production
  static String get baseUrl {
    return 'https://hammerhead-app-akzet.ondigitalocean.app/api/v1';
  }

  ApiService() {
    _dio.options.baseUrl = baseUrl;
    _dio.options.headers = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          // Sisipkan token authorization ke setiap http request
          final token = await _storage.read(key: 'auth_token');
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onError: (DioException e, handler) async {
          // Logika global jika token expired (401) bisa dihandle di sini
          if (e.response?.statusCode == 401) {
            await _storage.delete(key: 'auth_token');
            if (onUnauthorized != null) {
              onUnauthorized!();
            }
          }
          return handler.next(e);
        },
      ),
    );
  }

  Dio get client => _dio;

  FlutterSecureStorage get storage => _storage;

  Future<Response<dynamic>> getWithFallback(
    List<String> endpoints, {
    Map<String, dynamic>? queryParameters,
  }) async {
    DioException? lastError;

    for (final endpoint in endpoints) {
      try {
        return await _dio.get(endpoint, queryParameters: queryParameters);
      } on DioException catch (error) {
        lastError = error;
        final statusCode = error.response?.statusCode;

        if (statusCode != 404) {
          rethrow;
        }
      }
    }

    if (lastError != null) {
      throw lastError;
    }

    throw DioException(
      requestOptions: RequestOptions(path: endpoints.join(' | ')),
      message: 'Endpoint tidak ditemukan.',
    );
  }

  static String extractErrorMessage(DioException error) {
    final data = error.response?.data;

    if (data is Map<String, dynamic>) {
      final message = data['message'];
      if (message is String && message.trim().isNotEmpty) {
        return message;
      }

      final errors = data['errors'];
      if (errors is Map<String, dynamic>) {
        for (final value in errors.values) {
          if (value is List && value.isNotEmpty && value.first is String) {
            return value.first as String;
          }
          if (value is String && value.trim().isNotEmpty) {
            return value;
          }
        }
      }
    }

    return error.message ?? 'Terjadi kesalahan jaringan.';
  }
}
