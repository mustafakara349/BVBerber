//
//  CafeProduct.swift
//  b&vapp
//
//  Created by Mustafa KARA on 24.09.2026.
//

import Foundation

// MARK: - CafeProduct Model

struct CafeProduct: Identifiable, Codable, Equatable, Hashable {
    var id: String
    var name: String
    var categoryId: String?
    var categoryName: String?
    var description: String
    var ingredients: String
    var price: Double
    var imageUrl: String
    var isActive: Bool
    var isFeatured: Bool
    var sortOrder: Int
    
    enum CodingKeys: String, CodingKey {
        case id, name, description, ingredients, price, imageUrl, isActive, isFeatured, sortOrder
        case categoryId = "category_id"
        case categoryName = "category_name"
    }

    // MARK: - Hesaplanan Özellikler

    /// Formatlı fiyat: "45,00 ₺"
    var formattedPrice: String {
        let formatter = NumberFormatter()
        formatter.numberStyle = .decimal
        formatter.minimumFractionDigits = 2
        formatter.maximumFractionDigits = 2
        formatter.decimalSeparator = ","
        formatter.groupingSeparator = "."
        let priceStr = formatter.string(from: NSNumber(value: price)) ?? String(format: "%.2f", price)
        return "\(priceStr) ₺"
    }

    /// Kategori için görünen ad
    var categoryInfo: (label: String, dummy: Bool) {
        return (categoryName ?? "Diğer", true)
    }
}

// MARK: - Dynamic Cafe Category struct

struct DynamicCafeCategory: Identifiable, Equatable, Hashable {
    let id: String
    let label: String
}
