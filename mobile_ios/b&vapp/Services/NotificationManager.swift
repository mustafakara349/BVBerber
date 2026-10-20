//
//  NotificationManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 06.05.2026.
//
//  Sorumluluklar:
//  - Bildirim izinlerini yönetme (UNUserNotificationCenter)
//  - FCM Token alma ve güvenli bir şekilde Laravel backend'e senkronize etme
//  - Token rotasyonunda otomatik güncelleme (MessagingDelegate)
//  - Uygulama ön plandayken ve arka plandayken bildirim karşılama ve yönlendirme
//

import Foundation
import FirebaseMessaging
import UserNotifications
import UIKit
import Combine

// MARK: - NotificationDestination

enum NotificationDestination: String {
    case notifications
    case campaigns
    case appointments
}

// MARK: - NotificationManager

final class NotificationManager: NSObject, ObservableObject {

    // MARK: Singleton

    static let shared = NotificationManager()

    // MARK: Published State

    /// Kullanıcının bildirim izni verip vermediği
    @Published var isPermissionGranted: Bool = false

    /// Geçerli FCM token (debug / izleme için)
    @Published var fcmToken: String?

    /// Uygulama kapalıyken tıklanan bildirimin hedef sayfası (Cold Start desteği)
    @Published var pendingDestination: NotificationDestination?

    // MARK: Private State (İdempotency ve Mükerrer İstek Koruması)

    private var lastSyncedToken: String?
    private var lastSyncedUserId: String?

    // MARK: Lifecycle

    private override init() {
        super.init()
    }

    /// Çıkış yapıldığında token senkronizasyon durumunu sıfırlar
    func resetSyncState() {
        lastSyncedToken = nil
        lastSyncedUserId = nil
        pendingDestination = nil
    }

    /// Uygulama simgesindeki kırmızı rozet sayısını sıfırlar
    @MainActor
    func clearBadge() {
        if #available(iOS 16.0, *) {
            UNUserNotificationCenter.current().setBadgeCount(0) { error in
                if let error = error {
                    print("[NotificationManager] Rozet sıfırlama hatası: \(error.localizedDescription)")
                }
            }
        } else {
            UIApplication.shared.applicationIconBadgeNumber = 0
        }
    }

    // MARK: - Setup (AppDelegate'den çağrılır)

    /// Firebase Messaging'i yapılandırır ve delegate'leri atar.
    /// `application(_:didFinishLaunchingWithOptions:)` içinde `FirebaseApp.configure()`'dan SONRA çağrılmalıdır.
    func configure() {
        Messaging.messaging().delegate = self
        UNUserNotificationCenter.current().delegate = self
    }

    // MARK: - Bildirim Aktivasyonu (Tek Giriş Noktası)

    /// Login sonrasında veya oturum doğrulandığında çağrılacak ana aktivasyon metodu.
    @MainActor
    func activateRemoteNotifications() async {
        let settings = await UNUserNotificationCenter.current().notificationSettings()

        switch settings.authorizationStatus {

        case .authorized, .provisional, .ephemeral:
            isPermissionGranted = true
            UIApplication.shared.registerForRemoteNotifications()
            print("[NotificationManager] İzin mevcut — APNs kaydı ve token senkronizasyonu başlatılıyor.")
            Task {
                await self.syncTokenWithBackend()
            }

        case .notDetermined:
            isPermissionGranted = false
            do {
                let granted = try await UNUserNotificationCenter.current()
                    .requestAuthorization(options: [.alert, .badge, .sound])
                isPermissionGranted = granted
                if granted {
                    UIApplication.shared.registerForRemoteNotifications()
                    print("[NotificationManager] İzin onaylandı — APNs kaydı başladı.")
                    Task {
                        await self.syncTokenWithBackend()
                    }
                } else {
                    print("[NotificationManager] Bildirim izni kullanıcı tarafından reddedildi.")
                }
            } catch {
                print("[NotificationManager] İzin isteme hatası: \(error.localizedDescription)")
            }

        case .denied:
            isPermissionGranted = false
            print("[NotificationManager] Bildirim izni reddedilmiş durumda. Kullanıcı Ayarlar'dan açabilir.")

        @unknown default:
            break
        }
    }

    /// Anlık izin durumunu sorgular.
    @MainActor
    func syncPermissionStatus() async {
        let settings = await UNUserNotificationCenter.current().notificationSettings()
        isPermissionGranted = (settings.authorizationStatus == .authorized)
    }

    // MARK: - Token Senkronizasyonu (FCM -> Laravel)

    /// Mevcut Firebase token'ını alır ve Laravel backend'e kaydeder.
    func syncTokenWithBackend() async {
        // 1. Önce hafızadaki token'ı dene
        if let current = fcmToken, !current.isEmpty {
            await sendTokenToBackend(token: current)
        }

        // 2. Firebase SDK'dan güncel token'ı güvenli şekilde çek
        if let fetchedToken = await fetchCurrentFCMToken(), !fetchedToken.isEmpty {
            await MainActor.run {
                self.fcmToken = fetchedToken
            }
            await sendTokenToBackend(token: fetchedToken)
        }
    }

    /// Firebase Messaging SDK üzerinden geçerli token'ı async olarak çeker.
    private func fetchCurrentFCMToken() async -> String? {
        await withCheckedContinuation { continuation in
            Messaging.messaging().token { token, error in
                if let error = error {
                    print("[NotificationManager] FCM token çekme uyarısı: \(error.localizedDescription)")
                    continuation.resume(returning: nil)
                } else {
                    continuation.resume(returning: token)
                }
            }
        }
    }

    /// FCM token'ını Laravel API'sine kaydeder.
    /// - Parameter token: Firebase tarafından üretilen cihaz token'ı.
    func sendTokenToBackend(token: String) async {
        guard let userId = AuthManager.shared.currentUserId, !userId.isEmpty else {
            print("[NotificationManager] Kullanıcı henüz oturum açmamış — token senkronizasyonu ertelendi.")
            return
        }

        guard let authToken = UserDefaults.standard.string(forKey: "auth_token"), !authToken.isEmpty else {
            print("[NotificationManager] Oturum belirteci (auth_token) bulunamadı.")
            return
        }

        // Mükerrer istekleri engelle: Aynı token ve aynı kullanıcı için zaten gönderildiyse çık
        if lastSyncedToken == token && lastSyncedUserId == userId {
            return
        }

        guard let url = URL(string: "\(AppConfig.apiBaseURL)/save-token") else {
            print("[NotificationManager] Hata: Geçersiz API adresi.")
            return
        }

        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        request.setValue("Bearer \(authToken)", forHTTPHeaderField: "Authorization")
        request.timeoutInterval = 15

        let body: [String: String] = [
            "fcmToken": token,
            "device_type": "ios"
        ]

        do {
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
            let (_, response) = try await URLSession.shared.data(for: request)

            if let httpResponse = response as? HTTPURLResponse, (200...299).contains(httpResponse.statusCode) {
                lastSyncedToken = token
                lastSyncedUserId = userId
                let maskedToken = token.count > 15 ? "\(token.prefix(10))...\(token.suffix(5))" : token
                print("[NotificationManager] iOS FCM token Laravel'e başarıyla kaydedildi: \(maskedToken)")
            } else {
                let statusCode = (response as? HTTPURLResponse)?.statusCode ?? -1
                print("[NotificationManager] Token kaydedilemedi — HTTP Durum Kodu: \(statusCode)")
            }
        } catch {
            print("[NotificationManager] Token kaydı ağ hatası: \(error.localizedDescription)")
        }
    }

    /// Geriye dönük uyumluluk için alias
    func saveTokenToFirestore(_ token: String) {
        Task {
            await sendTokenToBackend(token: token)
        }
    }

    // MARK: - APNs Token -> Firebase

    /// AppDelegate'in `didRegisterForRemoteNotificationsWithDeviceToken` metodundan çağrılır.
    func setAPNsToken(_ deviceToken: Data) {
        Messaging.messaging().apnsToken = deviceToken
        Task {
            await syncTokenWithBackend()
        }
    }

    /// AppDelegate'in `didReceiveRemoteNotification` metodundan çağrılır.
    func appDidReceiveRemoteNotification(_ userInfo: [AnyHashable: Any]) {
        Messaging.messaging().appDidReceiveMessage(userInfo)
    }
}

// MARK: - MessagingDelegate (FCM Token Lifecycle)

extension NotificationManager: MessagingDelegate {

    /// FCM token oluşturulduğunda veya yenilendiğinde otomatik tetiklenir.
    func messaging(_ messaging: Messaging, didReceiveRegistrationToken fcmToken: String?) {
        guard let token = fcmToken, !token.isEmpty else { return }
        DispatchQueue.main.async {
            self.fcmToken = token
        }
        print("[NotificationManager] FCM Token yenilendi: \(token.prefix(15))…")
        Task {
            await sendTokenToBackend(token: token)
        }
    }
}

// MARK: - UNUserNotificationCenterDelegate (Ön Plan & Tıklama Yönetimi)

extension NotificationManager: UNUserNotificationCenterDelegate {

    /// Uygulama ÖN PLANDAYKEN gelen bildirimi ses, banner ve rozet ile gösterir.
    func userNotificationCenter(
        _ center: UNUserNotificationCenter,
        willPresent notification: UNNotification,
        withCompletionHandler completionHandler: @escaping (UNNotificationPresentationOptions) -> Void
    ) {
        let userInfo = notification.request.content.userInfo
        let action = (userInfo["action"] as? String) ?? (userInfo["type"] as? String) ?? ""
        if action.contains("appointment") {
            DispatchQueue.main.async {
                NotificationCenter.default.post(name: .refreshAppointments, object: nil)
            }
        }
        completionHandler([.banner, .list, .sound, .badge])
    }

    /// Kullanıcı bildirime TIKLADIĞINDA tetiklenir ve ilgili sayfaya yönlendirir.
    func userNotificationCenter(
        _ center: UNUserNotificationCenter,
        didReceive response: UNNotificationResponse,
        withCompletionHandler completionHandler: @escaping () -> Void
    ) {
        let userInfo = response.notification.request.content.userInfo
        print("[NotificationManager] Bildirime tıklandı. Payload: \(userInfo)")

        DispatchQueue.main.async {
            // Eylemi oku (action veya type)
            let action = (userInfo["action"] as? String) ?? (userInfo["type"] as? String) ?? ""
            let destination: NotificationDestination

            switch action {
            case "open_campaign", "campaign":
                destination = .campaigns
                NotificationCenter.default.post(name: .navigateToCampaigns, object: nil)

            case "open_appointment", "appointment_cancelled", "appointment_confirmed",
                 "appointment_rejected", "appointment_reminder":
                destination = .appointments
                NotificationCenter.default.post(name: .refreshAppointments, object: nil)
                NotificationCenter.default.post(name: .navigateToAppointments, object: nil)

            default:
                destination = .notifications
                NotificationCenter.default.post(name: .navigateToNotifications, object: nil)
            }

            // Cold Start (uygulama kapalıyken açılma) senaryosu için hedefi sakla
            self.pendingDestination = destination
        }

        completionHandler()
    }
}

// MARK: - Notification.Name Extension

extension Notification.Name {
    static let navigateToNotifications = Notification.Name("navigateToNotifications")
    static let navigateToCampaigns = Notification.Name("navigateToCampaigns")
    static let navigateToAppointments = Notification.Name("navigateToAppointments")
    static let refreshAppointments = Notification.Name("refreshAppointments")
}

