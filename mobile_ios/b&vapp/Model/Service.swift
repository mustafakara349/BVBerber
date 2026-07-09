//
//  Service.swift
//  b&vapp
//
//  Created by Mustafa KARA on 17.03.2026.
//

import Foundation

struct Service: Identifiable, Codable, Equatable, Hashable {

    var id: String?

    var name: String
    var description: String
    var category: String        // "hair", vb.
    var duration: Int           // dakika
    var price: Int
    var discountedPrice: Int?
    var imageUrl: String        // URL String
    var isActive: Bool
    var genderType: String?
    var isPopular: Bool?
    var isFeatured: Bool?

    var createdAt: Date?
    var updatedAt: Date?

    var effectivePrice: Int {
        if let discounted = discountedPrice, discounted < price {
            return discounted
        }
        return price
    }
}
