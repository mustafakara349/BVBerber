//
//  NotificationViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import Combine

@MainActor
class NotificationViewModel: ObservableObject {
    
    @Published var notifications: [NotificationItem] = []
    @Published var isLoading = false
    
    private let firestoreManager = FirestoreManager.shared
    
    
    // MARK: - Fetch Notifications
    
    func fetchNotifications() async {
        
        guard let userId = AuthManager.shared.currentUserId else {
            loadMockData()
            return
        }
        
        if notifications.isEmpty {
            isLoading = true
        }
        
        do {
            notifications = try await firestoreManager.fetchCollection(
                "notifications",
                whereFields: [("userId", userId)]
            )
        } catch {
            print("Bildirimler yüklenemedi: \(error.localizedDescription)")
            loadMockData()
        }
        
        isLoading = false
    }
    
    
    // MARK: - Mock Data
    
    func loadMockData() {
        
        notifications = [
            NotificationItem(
                id: "1",
                icon: "calendar.badge.checkmark",
                title: "Randevunuz Onaylandı",
                message: "15 Ekim, Salı 14:00 tarihli randevunuz onaylanmıştır.",
                userId: "mock",
                createdAt: Date(timeIntervalSinceNow: -7200) // 2 saat önce
            ),
            NotificationItem(
                id: "2",
                icon: "scissors",
                title: "Yeni Hizmet Eklendi",
                message: "Cilt bakımı hizmetimiz artık mevcut. Hemen randevu alabilirsiniz.",
                userId: "mock",
                createdAt: Date(timeIntervalSinceNow: -86400) // 1 gün önce
            ),
            NotificationItem(
                id: "3",
                icon: "megaphone.fill",
                title: "Kampanya",
                message: "Bu hafta tüm saç kesimlerinde %20 indirim! Fırsatı kaçırmayın.",
                userId: "mock",
                createdAt: Date(timeIntervalSinceNow: -259200) // 3 gün önce
            )
        ]
    }
}
