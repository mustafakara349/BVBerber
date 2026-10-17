import 'dart:io';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mobile_android/services/api_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FakeSecureStorage extends Fake implements FlutterSecureStorage {
  final Map<String, String> data = {};

  @override
  Future<String?> read({
    required String key,
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    AppleOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    return data[key];
  }

  @override
  Future<void> write({
    required String key,
    required String? value,
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    AppleOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    if (value != null) {
      data[key] = value;
    } else {
      data.remove(key);
    }
  }

  @override
  Future<void> delete({
    required String key,
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    AppleOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    data.remove(key);
  }

  @override
  Future<void> deleteAll({
    AppleOptions? iOptions,
    AndroidOptions? aOptions,
    LinuxOptions? lOptions,
    WebOptions? webOptions,
    AppleOptions? mOptions,
    WindowsOptions? wOptions,
  }) async {
    data.clear();
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  late FakeSecureStorage fakeSecureStorage;

  setUp(() {
    fakeSecureStorage = FakeSecureStorage();
    ApiService.secureStorage = fakeSecureStorage;
    SharedPreferences.setMockInitialValues({});
  });

  group('SEC-MO-2026-005: HTTPS Enforcement & API Base URL Security', () {
    test('Default base URL in release mode must enforce HTTPS', () {
      final releaseUrl = ApiService.resolveBaseUrl(isRelease: true);
      expect(releaseUrl.startsWith('https://'), isTrue);
      expect(releaseUrl, equals('https://api.bvbarber.com/api/v1'));
    });

    test('Custom HTTPS base URL in release mode is accepted', () {
      const customSecureUrl = 'https://custom-api.bvbarber.com/api/v1';
      final resolved = ApiService.resolveBaseUrl(
        isRelease: true,
        customUrl: customSecureUrl,
      );
      expect(resolved, equals(customSecureUrl));
    });

    test('HTTP base URL in release mode throws StateError and never falls back to HTTP', () {
      expect(
        () => ApiService.resolveBaseUrl(
          isRelease: true,
          customUrl: 'http://insecure-api.bvbarber.com/api/v1',
        ),
        throwsA(isA<StateError>().having(
          (e) => e.message,
          'message',
          contains('Insecure HTTP communication is blocked in production'),
        )),
      );
    });

    test('Debug mode allows local HTTP endpoint for development and emulator', () {
      final devUrl = ApiService.resolveBaseUrl(isRelease: false);
      expect(devUrl, equals('http://10.0.2.2:8000/api/v1'));

      final customDev = ApiService.resolveBaseUrl(
        isRelease: false,
        customUrl: 'http://192.168.1.50:8000/api/v1',
      );
      expect(customDev, equals('http://192.168.1.50:8000/api/v1'));
    });

    test('enforceHttps helper blocks cleartext HTTP in release mode', () {
      expect(
        () => ApiService.enforceHttps('http://api.bvbarber.com/test', isRelease: true),
        throwsA(isA<StateError>()),
      );

      expect(
        ApiService.enforceHttps('https://api.bvbarber.com/test', isRelease: true),
        equals('https://api.bvbarber.com/test'),
      );

      // In debug mode, HTTP is permitted for local dev
      expect(
        ApiService.enforceHttps('http://10.0.2.2:8000/test', isRelease: false),
        equals('http://10.0.2.2:8000/test'),
      );
    });
  });

  group('SEC-MO-2026-005: Android Keystore Token Storage & Migration', () {
    test('setAuthToken stores token in Keystore-backed secure storage and purges SharedPreferences', () async {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', 'stale_plain_token');

      await ApiService.setAuthToken('new_secure_jwt_token');

      // Secure storage must contain the token
      expect(fakeSecureStorage.data['auth_token'], equals('new_secure_jwt_token'));
      // SharedPreferences plaintext token must be deleted
      expect(prefs.containsKey('auth_token'), isFalse);
    });

    test('getAuthToken retrieves token from secure storage', () async {
      fakeSecureStorage.data['auth_token'] = 'existing_secure_token';

      final token = await ApiService.getAuthToken();
      expect(token, equals('existing_secure_token'));
    });

    test('Legacy migration: plaintext token in SharedPreferences is migrated to secure storage and purged', () async {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', 'legacy_unencrypted_token');

      // Secure storage is initially empty
      expect(fakeSecureStorage.data['auth_token'], isNull);

      final token = await ApiService.getAuthToken();

      // Returned token is the legacy token
      expect(token, equals('legacy_unencrypted_token'));
      // Now stored securely
      expect(fakeSecureStorage.data['auth_token'], equals('legacy_unencrypted_token'));
      // Plaintext copy purged from SharedPreferences
      expect(prefs.containsKey('auth_token'), isFalse);
    });

    test('clearAuthToken deletes token from both secure storage and SharedPreferences', () async {
      fakeSecureStorage.data['auth_token'] = 'token_to_delete';
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('auth_token', 'lingering_legacy_token');

      await ApiService.clearAuthToken();

      expect(fakeSecureStorage.data['auth_token'], isNull);
      expect(prefs.containsKey('auth_token'), isFalse);
      expect(await ApiService.getAuthToken(), isNull);
    });
  });

  group('SEC-MO-2026-005: Android Manifest Security Verification', () {
    test('Main AndroidManifest disables cleartext traffic and backups', () {
      final mainManifest = File('android/app/src/main/AndroidManifest.xml').readAsStringSync();
      expect(mainManifest, contains('android:usesCleartextTraffic="false"'));
      expect(mainManifest, contains('android:allowBackup="false"'));
      expect(mainManifest.contains('android:usesCleartextTraffic="true"'), isFalse);
    });

    test('Debug AndroidManifest isolates cleartext traffic to debug builds only', () {
      final debugManifest = File('android/app/src/debug/AndroidManifest.xml').readAsStringSync();
      expect(debugManifest, contains('android:usesCleartextTraffic="true"'));
    });
  });
}
