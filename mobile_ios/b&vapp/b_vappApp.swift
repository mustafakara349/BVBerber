//
//  b_vappApp.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI
import FirebaseCore
import Combine

// MARK: - AppDelegate

class AppDelegate: NSObject, UIApplicationDelegate {

    func application(
        _ application: UIApplication,
        didFinishLaunchingWithOptions launchOptions: [UIApplication.LaunchOptionsKey: Any]? = nil
    ) -> Bool {
        // 1) Firebase'i yapılandır
        FirebaseApp.configure()
        // 2) FCM + UNUserNotificationCenter delegate'lerini ata
        NotificationManager.shared.configure()
        return true
    }

    // MARK: APNs Token → Firebase

    func application(
        _ application: UIApplication,
        didRegisterForRemoteNotificationsWithDeviceToken deviceToken: Data
    ) {
        NotificationManager.shared.setAPNsToken(deviceToken)
    }

    func application(
        _ application: UIApplication,
        didFailToRegisterForRemoteNotificationsWithError error: Error
    ) {
        print("[AppDelegate] APNs kaydı başarısız: \(error.localizedDescription)")
    }
}

// MARK: - App Entry Point

@main
struct b_vappApp: App {

    @UIApplicationDelegateAdaptor(AppDelegate.self) var delegate
    @StateObject var authManager = AuthManager.shared
    @StateObject var notificationManager = NotificationManager.shared
    @AppStorage("hasCompletedOnboarding") var hasCompletedOnboarding: Bool = false

    var body: some Scene {
        WindowGroup {
            ZStack {
                if authManager.isCheckingAuth {
                    Color.black.ignoresSafeArea()
                        .transition(.opacity)

                } else if authManager.currentUserId != nil && authManager.isNewlyRegistered {
                    // Yeni kayıt → önce profil fotoğrafı onboarding ekranı
                    ProfilePhotoOnboardingView()
                        .transition(.opacity.animation(.easeIn(duration: 0.3)))

                } else if authManager.currentUserId != nil {
                    MainView()
                        .transition(
                            .asymmetric(
                                insertion: .opacity.animation(.easeIn(duration: 0.3)),
                                removal:   .opacity.animation(.easeOut(duration: 0.35))
                            )
                        )

                } else if !hasCompletedOnboarding {
                    OnboardingView()
                        .transition(.opacity)

                } else {
                    WelcomeView()
                        .transition(
                            .asymmetric(
                                insertion: .opacity.animation(.easeIn(duration: 0.35)),
                                removal:   .opacity.animation(.easeOut(duration: 0.25))
                            )
                        )
                }
            }
            .animation(.easeInOut(duration: 0.4), value: authManager.currentUserId)
            .animation(.easeInOut(duration: 0.3), value: authManager.isCheckingAuth)
            .animation(.easeInOut(duration: 0.35), value: authManager.isNewlyRegistered)
            // Kullanıcı giriş yaptığında veya yeni kayıt olduğunda:
            // izin durumuna bakılmaksızın APNs→FCM token zincirini başlat.
            .onChange(of: authManager.currentUserId) { _, newUserId in
                if newUserId != nil {
                    Task {
                        await notificationManager.activateRemoteNotifications()
                    }
                }
            }
        }
    }
}

