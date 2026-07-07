//
//  StoreModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation
import Combine

// MARK: - Store (tek doküman: store/main)

struct StoreModel: Codable {

    var name: String
    var description: String
    var phone: String
    var address: String
    var isActive: Bool

    var settings: StoreSettings?
    var workingHours: [String: WorkingDay]?

    var createdAt: Date?
    var updatedAt: Date?

    // GeoPoint lokasyon – sadece depolama, harita için koordinatlar hardcode kullanılıyor
    // location alanı Codable uyumsuzluğu yaratmasın diye CodingKeys'ten çıkarıldı
    private enum CodingKeys: String, CodingKey {
        case name, description, phone, address, isActive
        case settings, workingHours
        case createdAt, updatedAt
    }
}

// MARK: - Store Settings

struct StoreSettings: Codable {
    var allowCancellation: Bool
    var appointmentInterval: Int
    var autoApproveAppointments: Bool
    var bufferBetweenAppointments: Int
    var cancellationLimitHours: Int
    var maxBookingDaysAhead: Int
    var maxDailyAppointmentsPerBarber: Int
}

// MARK: - Working Day

/// Her gün için çalışma saati.
/// Normal günlerde `open` ve `close` ("08:00" formatı), kapalı günlerde `closed: true` gelir.
struct WorkingDay: Codable {
    var open: String?
    var close: String?
    var closed: Bool?
}
