//
//  DateManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

struct DateManager {

    // MARK: - Gün İsmi (Türkçe, büyük harf)
    static func dayString(from date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "EEE"
        formatter.locale = Locale(identifier: "tr_TR")
        return formatter.string(from: date).uppercased()
    }

    // MARK: - Gün Numarası
    static func dayNumber(from date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "d"
        return formatter.string(from: date)
    }

    // MARK: - Ay Yıl String'i
    static func monthYearString(from date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "LLLL yyyy"
        formatter.locale = Locale(identifier: "tr_TR")
        return formatter.string(from: date)
    }

    // MARK: - Date → "yyyy-MM-dd"
    static func toString(_ date: Date) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        return formatter.string(from: date)
    }

    // MARK: - "yyyy-MM-dd" → Date
    static func toDate(_ string: String) -> Date? {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        return formatter.date(from: string)
    }

    // MARK: - Weekday İsmi ("monday", "tuesday" ... "sunday")
    static func weekdayName(from date: Date) -> String {
        let names = ["sunday","monday","tuesday","wednesday","thursday","friday","saturday"]
        let index = Calendar.current.component(.weekday, from: date) - 1
        return names[index]
    }
}
