//
//  AvailableSlot.swift
//  b&vapp
//
//  Created by Antigravity on 12.10.2026.
//

import Foundation

struct AvailableSlot: Codable, Identifiable, Equatable {
    var id: String { time }
    let time: String
    let isAvailable: Bool
    let reason: String?

    enum CodingKeys: String, CodingKey {
        case time
        case isAvailable = "is_available"
        case reason
    }
}
