//
//  CafeViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 24.09.2026.
//

import Foundation
import Combine

@MainActor
class CafeViewModel: ObservableObject {

    // MARK: - Published State

    @Published var allProducts: [CafeProduct] = []
    @Published var isLoading: Bool = false
    @Published var errorMessage: String? = nil

    // MARK: - Filter State

    @Published var selectedCategoryIds: Set<String> = []
    @Published var searchText: String = ""
    @Published var sortOption: ProductSortOption = .nameAsc

    // MARK: - Computed: Filtered Products

    var filteredProducts: [CafeProduct] {
        var result = allProducts

        // 1. Kategori filtresi
        if !selectedCategoryIds.isEmpty {
            result = result.filter { selectedCategoryIds.contains($0.categoryId ?? "other") }
        }

        // 2. Arama
        if !searchText.isEmpty {
            result = result.filter {
                $0.name.localizedCaseInsensitiveContains(searchText)
                || $0.description.localizedCaseInsensitiveContains(searchText)
                || $0.ingredients.localizedCaseInsensitiveContains(searchText)
            }
        }
        
        // 3. Sıralama
        switch sortOption {
        case .nameAsc:   result.sort { $0.name < $1.name }
        case .nameDesc:  result.sort { $0.name > $1.name }
        case .priceAsc:  result.sort { $0.price < $1.price }
        case .priceDesc: result.sort { $0.price > $1.price }
        }

        return result
    }

    /// Mevcut ürünlerden dinamik olarak kategori listesi oluştur
    var availableCategories: [DynamicCafeCategory] {
        var uniqueCategories: [String: DynamicCafeCategory] = [:]
        
        for product in allProducts {
            guard let catId = product.categoryId,
                  let catName = product.categoryName else { continue }
            
            if uniqueCategories[catId] == nil {
                uniqueCategories[catId] = DynamicCafeCategory(id: catId, label: catName)
            }
        }
        
        return uniqueCategories.values.sorted { $0.label < $1.label }
    }

    // MARK: - Dependencies

    private let api = APIClient.shared

    // MARK: - Fetch

    func fetchProducts() async {
        if allProducts.isEmpty { isLoading = true }
        errorMessage = nil

        do {
            allProducts = try await api.fetchPublicList("cafe-products")
        } catch {
            errorMessage = "Cafe ürünleri yüklenemedi: \(error.localizedDescription)"
        }

        isLoading = false
    }

    // MARK: - Helpers

    func resetFilters() {
        selectedCategoryIds.removeAll()
        searchText = ""
        sortOption = .nameAsc
    }
    
    var activeFilterCount: Int {
        var count = 0
        count += selectedCategoryIds.count
        if !searchText.isEmpty         { count += 1 }
        if sortOption != .nameAsc      { count += 1 }
        return count
    }
}
