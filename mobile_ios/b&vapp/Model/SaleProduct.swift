//
//  SaleProduct.swift
//  b&vapp
//
//  Created by Mustafa KARA on 24.09.2026.
//

import Foundation

// MARK: - Dynamic Category Model for Sale Products

struct DynamicSaleCategory: Identifiable, Equatable, Hashable {
    var id: String
    var label: String
}

// MARK: - SaleProduct Model

struct SaleProduct: Identifiable, Codable, Equatable, Hashable {
    var id: String
    var name: String
    var categoryId: String?
    var categoryName: String?
    var description: String
    var sku: String
    var barcode: String
    var sellPrice: Double
    var purchasePrice: Double
    var stockQuantity: Int
    var criticalStock: Int
    var imageUrl: String
    var isActive: Bool
    var isInStock: Bool
    
    enum CodingKeys: String, CodingKey {
        case id, name, description, sku, barcode, imageUrl
        case categoryId = "category_id"
        case categoryName = "category_name"
        case sellPrice = "sellPrice"
        case purchasePrice = "purchasePrice"
        case stockQuantity = "stockQuantity"
        case criticalStock = "criticalStock"
        case isActive = "isActive"
        case isInStock = "isInStock"
    }

    // MARK: - Hesaplanan Özellikler

    /// Formatlı satış fiyatı: "199,90 ₺"
    var formattedPrice: String {
        let formatter = NumberFormatter()
        formatter.numberStyle = .decimal
        formatter.minimumFractionDigits = 2
        formatter.maximumFractionDigits = 2
        formatter.decimalSeparator = ","
        formatter.groupingSeparator = "."
        let priceStr = formatter.string(from: NSNumber(value: sellPrice)) ?? String(format: "%.2f", sellPrice)
        return "\(priceStr) ₺"
    }

    /// Stok durumu
    var stockStatus: StockStatus {
        if stockQuantity <= 0 {
            return .empty
        } else if stockQuantity <= 5 {
            return .low
        } else {
            return .ok
        }
    }

    /// Kategori bilgileri
    var categoryInfo: DynamicSaleCategory {
        DynamicSaleCategory(
            id: categoryId ?? "other",
            label: categoryName ?? "Diğer"
        )
    }
}

// MARK: - StockStatus

enum StockStatus {
    case ok
    case low
    case empty

    var label: String {
        switch self {
        case .ok:    return "Stokta"
        case .low:   return "Az Kaldı"
        case .empty: return "Tükendi"
        }
    }

    var color: String {  // SwiftUI Color name
        switch self {
        case .ok:    return "green"
        case .low:   return "orange"
        case .empty: return "red"
        }
    }
}

// MARK: - SortOption

enum ProductSortOption: String, CaseIterable, Identifiable {
    case nameAsc    = "name_asc"
    case nameDesc   = "name_desc"
    case priceAsc   = "price_asc"
    case priceDesc  = "price_desc"

    var id: String { rawValue }

    var label: String {
        switch self {
        case .nameAsc:   return "A → Z"
        case .nameDesc:  return "Z → A"
        case .priceAsc:  return "Fiyat ↑"
        case .priceDesc: return "Fiyat ↓"
        }
    }
}
