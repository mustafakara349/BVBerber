import Foundation

struct Coupon: Identifiable, Codable {
    let id: String
    let title: String
    let description: String
    let code: String
    let discountType: String // "percentage" or "fixed"
    let discountValue: Double
    let expiresAt: String?
    let usageLimit: Int
    let usedCount: Int
    let perCustomerLimit: Int
    let remainingUsage: Int
    let isValid: Bool
    
    var isPercentage: Bool {
        discountType == "percentage"
    }
}
