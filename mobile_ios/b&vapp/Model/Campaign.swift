//
//  Campaign.swift
//  b&vapp
//
//  Created by Mustafa KARA on 06.07.2026.
//

import Foundation

struct Campaign: Codable, Identifiable, Equatable {
    let id: String
    let title: String
    let description: String
    let discountType: String
    let discountValue: Double
    let startDate: String?
    let endDate: String?
    let isActive: Bool
}
