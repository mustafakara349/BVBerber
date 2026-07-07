//
//  BarberAvailability.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

/// barberAvailability/{id} koleksiyonu için model.
/// - `fullDayOff = true`  → o gün hiç randevu alınamaz
/// - `blockedSlots`       → engellenen saat dilimleri, format: ["HH"] (örn. "09", "14")
struct BarberAvailability: Identifiable, Codable {

    var id: String?

    var barberId: String
    var date: String            // "YYYY-MM-DD"
    var fullDayOff: Bool
    var blockedSlots: [String]? // ["HH"], nil ise boş dizi kabul edilir
    var reason: String?

    var createdAt: Date?
    var updatedAt: Date?

    // MARK: - Helpers
    var effectiveBlockedSlots: [String] {
        blockedSlots ?? []
    }
}
