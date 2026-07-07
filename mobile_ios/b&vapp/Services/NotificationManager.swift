//
//  NotificationManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 06.05.2026.
//
//  Sorumluluklar:
//  - Bildirim izni isteme
//  - FCM token alma ve Firestore'a kaydetme
//  - Token yenilendiğinde otomatik güncelleme
//  - Uygulama ön plandayken bildirim gösterme (UNUserNotificationCenterDelegate)

import Foundation
import FirebaseMessaging
import UserNotifications
import UIKit
import Combine

// MARK: - NotificationManager

final class NotificationManager: NSObject, ObservableObject {

    // MARK: Singleton

    static let shared = NotificationManager()

    // MARK: Published State

    /// Kullanıcının bildirim izni verip vermediği
    @Published var isPermissionGranted: Bool = false

    /// Geçerli FCM token (debug / loglama için kullanışlı)
    @Published var fcmToken: String?

    // MARK: Private

    private override init() {
        super.init()
    }

    // MARK: - Setup (AppDelegate'den çağrılır)

    /// Firebase Messaging'i yapılandırır ve delegate'leri atar.
    /// `application(_:didFinishLaunchingWithOptions:)` içinde `FirebaseApp.configure()`'dan SONRA çağır.
    func configure() {
        Messaging.messaging().delegate = self
        UNUserNotificationCenter.current().delegate = self
    }

    // MARK: - Bildirim Aktivasyonu (Tek Giriş Noktası)

    /// Login veya kayıt sonrasında çağrılacak TEK metot.
    ///
    /// İzin durumuna göre:
    ///  - `.authorized` / `.provisional` → doğrudan `registerForRemoteNotifications()` çağırır.
    ///    Bu her zaman `didRegisterForRemoteNotificationsWithDeviceToken` → `setAPNsToken` →
    ///    `messaging(_:didReceiveRegistrationToken:)` → `saveTokenToFirestore` zincirini tetikler.
    ///  - `.notDetermined`               → izin isteği açar; kullanıcı onaylarsa aynı zincir döner.
    ///  - `.denied`                      → sessizce çıkar (kullanıcı Ayarlar'dan açmalı).
    @MainActor
    func activateRemoteNotifications() async {
        let settings = await UNUserNotificationCenter.current().notificationSettings()

        switch settings.authorizationStatus {

        case .authorized, .provisional, .ephemeral:
            // İzin zaten var → APNs kaydını yenile; bu her zaman token delegate'ini tetikler.
            isPermissionGranted = true
            UIApplication.shared.registerForRemoteNotifications()
            print("[NotificationManager] İzin mevcut — APNs kaydı yenileniyor.")

        case .notDetermined:
            // İlk kez soruluyor
            isPermissionGranted = false
            do {
                let granted = try await UNUserNotificationCenter.current()
                    .requestAuthorization(options: [.alert, .badge, .sound])
                isPermissionGranted = granted
                if granted {
                    UIApplication.shared.registerForRemoteNotifications()
                    print("[NotificationManager] İzin verildi — APNs kaydı başladı.")
                } else {
                    print("[NotificationManager] Bildirim izni reddedildi.")
                }
            } catch {
                print("[NotificationManager] İzin hatası: \(error.localizedDescription)")
            }

        case .denied:
            isPermissionGranted = false
            print("[NotificationManager] Bildirim izni reddedilmiş. Kullanıcı Ayarlar'dan açabilir.")

        @unknown default:
            break
        }
    }

    /// Anlık izin durumunu sorgular (UI gösterimi için kullanılabilir).
    @MainActor
    func syncPermissionStatus() async {
        let settings = await UNUserNotificationCenter.current().notificationSettings()
        isPermissionGranted = settings.authorizationStatus == .authorized
    }

    // MARK: - Token'ı Firestore'a Kaydet

    /// FCM token'ı Firestore'daki kullanıcı belgesine yazar.
    /// - Parameter token: Firebase Messaging tarafından sağlanan FCM registration token.
    func saveTokenToFirestore(_ token: String) {
        guard let userId = AuthManager.shared.currentUserId else {
            print("[NotificationManager] Kullanıcı oturumu yok — token kayıt bekleniyor.")
            return
        }

        Task {
            do {
                let url = URL(string: "\(AuthManager.shared.baseURL)/save-token")!
                var request = URLRequest(url: url)
                request.httpMethod = "POST"
                request.setValue("application/json", forHTTPHeaderField: "Content-Type")
                request.setValue("application/json", forHTTPHeaderField: "Accept")
                
                if let authToken = UserDefaults.standard.string(forKey: "auth_token") {
                    request.setValue("Bearer \(authToken)", forHTTPHeaderField: "Authorization")
                }
                
                let body: [String: String] = ["fcmToken": token]
                request.httpBody = try JSONSerialization.data(withJSONObject: body)
                
                let (_, response) = try await URLSession.shared.data(for: request)
                if let httpResponse = response as? HTTPURLResponse, (200...299).contains(httpResponse.statusCode) {
                    print("[NotificationManager] FCM token Laravel'e kaydedildi.")
                } else {
                    print("[NotificationManager] Token kaydedilemedi.")
                }
            } catch {
                print("[NotificationManager] Token kaydı hatası: \(error.localizedDescription)")
            }
        }
    }


    // MARK: - APNs Token → Firebase

    /// AppDelegate'in `didRegisterForRemoteNotificationsWithDeviceToken` metodundan çağrılır.
    func setAPNsToken(_ deviceToken: Data) {
        Messaging.messaging().apnsToken = deviceToken
    }
}

// MARK: - MessagingDelegate (FCM Token Lifecycle)

extension NotificationManager: MessagingDelegate {

    /// FCM token yenilendiğinde çağrılır — hem yeni kayıtta hem token rotasyonunda tetiklenir.
    func messaging(_ messaging: Messaging, didReceiveRegistrationToken fcmToken: String?) {
        guard let token = fcmToken else { return }
        DispatchQueue.main.async { self.fcmToken = token }
        saveTokenToFirestore(token)
        print("[NotificationManager] Token yenilendi: \(token.prefix(20))…")
    }
}

// MARK: - UNUserNotificationCenterDelegate (Ön Plan Bildirim Gösterme)

extension NotificationManager: UNUserNotificationCenterDelegate {

    /// Uygulama **ön plandayken** gelen bildirimlerin nasıl gösterileceğini belirler.
    /// `.banner + .list + .sound` ile tam bildirim banner'ı gösterilir.
    func userNotificationCenter(
        _ center: UNUserNotificationCenter,
        willPresent notification: UNNotification,
        withCompletionHandler completionHandler: @escaping (UNNotificationPresentationOptions) -> Void
    ) {
        completionHandler([.banner, .list, .sound, .badge])
    }

    /// Kullanıcı bildirime tıkladığında veya aksiyona bastığında çağrılır.
    func userNotificationCenter(
        _ center: UNUserNotificationCenter,
        didReceive response: UNNotificationResponse,
        withCompletionHandler completionHandler: @escaping () -> Void
    ) {
        let userInfo = response.notification.request.content.userInfo
        print("[NotificationManager] Bildirime tıklandı: \(userInfo)")
        
        // Bildirimler ekranına yönlendirmek için global Notification yayınla
        DispatchQueue.main.async {
            NotificationCenter.default.post(name: .navigateToNotifications, object: nil)
        }
        
        completionHandler()
    }
}

// MARK: - Notification.Name Extension

extension Notification.Name {
    static let navigateToNotifications = Notification.Name("navigateToNotifications")
}

