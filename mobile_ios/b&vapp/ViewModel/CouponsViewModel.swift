import Foundation
import Combine

@MainActor
class CouponsViewModel: ObservableObject {
    @Published var coupons: [Coupon] = []
    @Published var isLoading: Bool = false
    @Published var errorMessage: String? = nil
    
    private let db = FirestoreManager.shared
    
    func fetchCoupons() async {
        isLoading = true
        errorMessage = nil
        
        do {
            let all: [Coupon] = try await db.fetchCollection("coupons")
            self.coupons = all
        } catch {
            self.errorMessage = "Kuponlar yüklenemedi: \(error.localizedDescription)"
        }
        
        isLoading = false
    }
}
