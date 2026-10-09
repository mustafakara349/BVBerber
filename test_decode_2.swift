import Foundation

struct SaleProduct: Identifiable, Codable {
    var id: String
    var name: String
    var categoryId: String?
    var categoryName: String?
    var categoryEmoji: String?
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
        case categoryEmoji = "category_emoji"
        case sellPrice = "sellPrice"
        case purchasePrice = "purchasePrice"
        case stockQuantity = "stockQuantity"
        case criticalStock = "criticalStock"
        case isActive = "isActive"
        case isInStock = "isInStock"
    }
}

struct APIResponse: Codable {
    let success: Bool
    let message: String
    let data: [SaleProduct]
}

let data = try Data(contentsOf: URL(fileURLWithPath: "test_data.json"))
do {
    let res = try JSONDecoder().decode(APIResponse.self, from: data)
    print("Success count: \(res.data.count)")
} catch {
    print("Error during decoding: \(error)")
}
