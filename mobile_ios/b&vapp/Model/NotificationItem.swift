//
//  NotificationItem.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

struct NotificationItem: Identifiable, Codable {
    
    var id: String?
    var icon: String
    var title: String
    var message: String
    var userId: String
    var createdAt: Date
    
    var timeFormatted: String {
        let calendar = Calendar.current
        let now = Date()
        let components = calendar.dateComponents([.minute, .hour, .day], from: createdAt, to: now)
        
        if let days = components.day, days > 0 {
            if days >= 30 {
                let formatter = DateFormatter()
                formatter.dateFormat = "d MMM yyyy HH:mm"
                formatter.locale = Locale(identifier: "tr_TR")
                return formatter.string(from: createdAt)
            }
            return "\(days) gün önce"
        }
        
        if let hours = components.hour, hours > 0 {
            return "\(hours) saat önce"
        }
        
        if let minutes = components.minute, minutes > 0 {
            return "\(minutes) dakika önce"
        }
        
        return "Şimdi"
    }
}
