import Foundation

struct SaleProduct: Identifiable, Codable, Equatable, Hashable {
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

let json = """
{
  "success": true,
  "message": "Success",
  "data": [
    {
      "id": "3",
      "name": "Deneme",
      "category_id": "1",
      "category_name": "Bakım Ürünleri",
      "category_emoji": "💆🏼",
      "description": "Deneme ürünüdür.",
      "sku": "",
      "barcode": "45678765456",
      "sellPrice": 200,
      "purchasePrice": 100,
      "stockQuantity": 5,
      "criticalStock": 5,
      "imageUrl": "",
      "isActive": true,
      "isInStock": true
    }
  ]
}
"""

struct APIResponse: Codable {
    let success: Bool
    let message: String
    let data: [SaleProduct]
}

do {
    let data = json.data(using: .utf8)!
    let result = try JSONDecoder().decode(APIResponse.self, from: data)
    print("Success: \(result.data.count) items decoded.")
} catch {
    print("Decoding error: \(error)")
}
