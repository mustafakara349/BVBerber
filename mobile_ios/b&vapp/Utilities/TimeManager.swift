//
//  TimeManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

struct TimeManager {

    // MARK: - Time Slot Üretimi (30 dakikalık aralıklar, "HH:mm" formatı)
    /// 08:00'den 21:30'a kadar 30 dk aralıklı slotlar üretir.
    /// Örnek: openHour=8, closeHour=21, closeMinute=30
    /// → ["08:00","08:30","09:00",...,"21:00","21:30"]
    static func generateTimeSlots(
        openHour: Int = 8,
        openMinute: Int = 0,
        closeHour: Int = 21,
        closeMinute: Int = 30
    ) -> [String] {
        var slots: [String] = []
        var hour   = openHour
        var minute = openMinute

        let totalOpenMinutes  = openHour  * 60 + openMinute
        let totalCloseMinutes = closeHour * 60 + closeMinute

        var current = totalOpenMinutes
        while current <= totalCloseMinutes {
            hour   = current / 60
            minute = current % 60
            slots.append(String(format: "%02d:%02d", hour, minute))
            current += 30
        }
        return slots
    }

    // MARK: - Geçmiş Saat Kontrolü
    /// Seçili tarih bugünse ve saat+dakika geçmişte kalıyorsa `true` döner.
    /// `time` parametresi "HH:mm" formatındadır.
    static func isPastTime(selectedDate: Date, time: String) -> Bool {
        let calendar = Calendar.current
        guard calendar.isDateInToday(selectedDate) else { return false }

        let components = time.split(separator: ":").compactMap { Int($0) }
        guard components.count == 2 else { return false }

        let slotMinutes    = components[0] * 60 + components[1]
        let now            = Date()
        let currentHour    = calendar.component(.hour, from: now)
        let currentMinute  = calendar.component(.minute, from: now)
        let currentMinutes = currentHour * 60 + currentMinute

        return slotMinutes <= currentMinutes
    }

    // MARK: - Gösterim Formatı
    /// "HH:mm" → "HH:mm" (zaten hazır format, pass-through)
    static func displayTime(_ time: String) -> String {
        time
    }

    // MARK: - Mağaza Saatinden Slot Üretimi
    /// WorkingDay nesnesinden 30dk aralıklı saat dilimlerini üretir.
    /// WorkingDay.open/close "HH:mm" veya "HH" formatında gelebilir.
    static func generateSlots(for workingDay: WorkingDay?) -> [String] {
        guard let day = workingDay,
              day.closed != true,
              let openStr  = day.open,
              let closeStr = day.close else {
            return generateTimeSlots() // varsayılan 08:00–21:30
        }

        let openParts  = openStr.split(separator: ":").compactMap  { Int($0) }
        let closeParts = closeStr.split(separator: ":").compactMap { Int($0) }

        let openHour    = openParts.first  ?? 8
        let openMinute  = openParts.count  > 1 ? openParts[1]  : 0
        let closeHour   = closeParts.first ?? 21
        let closeMinute = closeParts.count > 1 ? closeParts[1] : 30

        return generateTimeSlots(
            openHour: openHour, openMinute: openMinute,
            closeHour: closeHour, closeMinute: closeMinute
        )
    }
}
