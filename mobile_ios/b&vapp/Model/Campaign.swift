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
    let terms: String?
    let type: String?
    let triggerType: String?
    let rewardType: String?
    let rewardProductId: String?
    let rewardCafeProductId: String?
    let minOrderAmount: Double?
    let maxDiscountAmount: Double?
    let targetAudience: String?
    let imagePath: String?
    let priority: Int?
    let discountType: String?
    let discountValue: Double?
    let startDate: String?
    let endDate: String?
    let isActive: Bool
    let perCustomerLimit: Int?
    let categories: [String]?
    let services: [String]?
    
    // Yardımcı ürün modellerini tutmak için eklenebilir, fakat başlangıçta sadece ID'ler yeterli.
}
