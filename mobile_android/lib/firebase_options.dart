// File generated for BVBarber Firebase configuration
import 'package:firebase_core/firebase_core.dart' show FirebaseOptions;
import 'package:flutter/foundation.dart'
    show defaultTargetPlatform, kIsWeb, TargetPlatform;

/// Varsayılan Firebase yapılandırma seçenekleri
class DefaultFirebaseOptions {
  static FirebaseOptions get currentPlatform {
    if (kIsWeb) {
      return web;
    }
    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return android;
      case TargetPlatform.iOS:
        return ios;
      default:
        return android;
    }
  }

  static const FirebaseOptions android = FirebaseOptions(
    apiKey: 'AIzaSyDxrnhVW5Dvi38IEG1b8r68ti2NjLM_M_s',
    appId: '1:530830581359:android:560e41222eca714346379a',
    messagingSenderId: '530830581359',
    projectId: 'bvbarber-ae2cd',
    storageBucket: 'bvbarber-ae2cd.firebasestorage.app',
  );

  static const FirebaseOptions ios = FirebaseOptions(
    apiKey: 'AIzaSyDxrnhVW5Dvi38IEG1b8r68ti2NjLM_M_s',
    appId: '1:530830581359:ios:560e41222eca714346379a',
    messagingSenderId: '530830581359',
    projectId: 'bvbarber-ae2cd',
    storageBucket: 'bvbarber-ae2cd.firebasestorage.app',
    iosBundleId: 'com.bvbarber.mobile_android',
  );

  static const FirebaseOptions web = FirebaseOptions(
    apiKey: 'AIzaSyDxrnhVW5Dvi38IEG1b8r68ti2NjLM_M_s',
    appId: '1:530830581359:web:560e41222eca714346379a',
    messagingSenderId: '530830581359',
    projectId: 'bvbarber-ae2cd',
    storageBucket: 'bvbarber-ae2cd.firebasestorage.app',
  );
}
