//
//  Appointment.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

struct Appointment: Identifiable, Codable {

    var id: String?

    var userId: String
    var barberId: String
    var barberName: String?     // snapshot – mevcut dokümanlarda olmayabilir
    var barberImageUrl: String?
    var serviceId: String
    var serviceName: String
    var date: String            // "YYYY-MM-DD"
    var time: String            // "HH:mm" (örn. "09:00", "14:30")
    var price: Int
    var status: String          // "active", "cancelled", "completed", "pending", "confirmed", "rejected", "no_show"

    var reminderSent: Bool?     // Cloud Function CRON tarafından true yapılır

    var createdAt: Date?
    var updatedAt: Date?

    // MARK: - Status Helpers

    var isActive: Bool     { status == "active" || status == "confirmed" || status == "pending" || status == "in_progress" }
    var isCancelled: Bool  { status == "cancelled" || status == "rejected" }
    var isCompleted: Bool  { status == "completed" || status == "no_show" }

    // MARK: - Upcoming Check
    /// Gün + saat + dakika bazında karşılaştırma yapar.
    /// Aynı gün içindeki saati gelmemiş randevular "Yaklaşan" olarak kalır.
    var isUpcoming: Bool {
        guard isActive else { return false }

        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        let todayStr = formatter.string(from: Date())

        if date > todayStr { return true }
        if date < todayStr { return false }

        // Aynı gün → dakika bazlı karşılaştırma
        // time "HH:mm" veya eski format "HH" olabilir — her ikisini destekle
        let parts = time.split(separator: ":").compactMap { Int($0) }
        let slotMinutes: Int
        if parts.count >= 2 {
            slotMinutes = parts[0] * 60 + parts[1]
        } else {
            // Eski "HH" formatı için fallback
            slotMinutes = (parts.first ?? 0) * 60
        }

        let now = Date()
        let cal = Calendar.current
        let nowMinutes = cal.component(.hour, from: now) * 60
                       + cal.component(.minute, from: now)

        return slotMinutes > nowMinutes
    }

    // MARK: - Display Helpers

    /// "22 Nisan, Çarşamba · 14:30"
    var formattedDate: String {
        let df = DateFormatter()
        df.dateFormat = "yyyy-MM-dd"
        df.locale = Locale(identifier: "tr_TR")
        guard let dateObj = df.date(from: date) else { return "\(date) \(displayTime)" }
        let display = DateFormatter()
        display.dateFormat = "d MMMM, EEEE"
        display.locale = Locale(identifier: "tr_TR")
        return "\(display.string(from: dateObj)) · \(displayTime)"
    }

    /// "22 Nisan, 2026"
    var shortDate: String {
        let df = DateFormatter()
        df.dateFormat = "yyyy-MM-dd"
        df.locale = Locale(identifier: "tr_TR")
        guard let dateObj = df.date(from: date) else { return date }
        let display = DateFormatter()
        display.dateFormat = "d MMMM, yyyy"
        display.locale = Locale(identifier: "tr_TR")
        return display.string(from: dateObj)
    }

    /// Saat gösterimi — "HH:mm" formatını garanti eder (eski "HH" veya "HH:mm:ss" gelse bile)
    var displayTime: String {
        // Zaten "HH:mm" formatındaysa direkt kullan
        let parts = time.split(separator: ":")
        if parts.count >= 2 {
            // "HH:mm:ss" → "HH:mm" (saniyeyi at)
            return "\(parts[0]):\(parts[1])"
        }
        // "HH" formatı → "HH:00"
        return "\(time):00"
    }

    /// Durum etiketi için Türkçe metin
    var statusLabel: String {
        switch status {
        case "active", "confirmed": return "Onaylandı"
        case "pending":             return "Beklemede"
        case "cancelled":           return "İptal Edildi"
        case "rejected":            return "Reddedildi"
        case "completed":           return "Tamamlandı"
        case "no_show":             return "Gelmedi"
        case "in_progress":         return "Devam Ediyor"
        default:                    return status.capitalized
        }
    }

    /// Berber isminin baş harfleri (profil fotoğrafı yoksa placeholder için)
    var barberInitials: String {
        guard let name = barberName, !name.isEmpty else { return "B" }
        let parts = name.split(separator: " ")
        return parts.prefix(2).compactMap { $0.first }.map(String.init).joined().uppercased()
    }
}

