//
//  SaleProductsViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 24.09.2026.
//

import Foundation
import Combine

@MainActor
class SaleProductsViewModel: ObservableObject {

    // MARK: - Published State

    @Published var allProducts: [SaleProduct] = []
    @Published var isLoading: Bool = false
    @Published var errorMessage: String? = nil

    // MARK: - Filter & Sort State

    @Published var selectedCategoryIds: Set<String> = []
    @Published var searchText: String = ""
    @Published var sortOption: ProductSortOption = .nameAsc
    @Published var showInStockOnly: Bool = false

    // MARK: - Computed: Filtered & Sorted Products

    var filteredProducts: [SaleProduct] {
        var result = allProducts

        // 1. Stok filtresi
        if showInStockOnly {
            result = result.filter { $0.isInStock }
        }

        // 2. Kategori filtresi
        if !selectedCategoryIds.isEmpty {
            result = result.filter { selectedCategoryIds.contains($0.categoryId ?? "other") }
        }

        // 3. Arama
        if !searchText.isEmpty {
            result = result.filter {
                $0.name.localizedCaseInsensitiveContains(searchText)
                || $0.description.localizedCaseInsensitiveContains(searchText)
                || $0.sku.localizedCaseInsensitiveContains(searchText)
            }
        }

        // 4. Sıralama
        switch sortOption {
        case .nameAsc:   result.sort { $0.name < $1.name }
        case .nameDesc:  result.sort { $0.name > $1.name }
        case .priceAsc:  result.sort { $0.sellPrice < $1.sellPrice }
        case .priceDesc: result.sort { $0.sellPrice > $1.sellPrice }
        }

        return result
    }

    /// Mevcut ürünlerden dinamik olarak kategori listesi oluştur
    var availableCategories: [DynamicSaleCategory] {
        var uniqueCategories: [String: DynamicSaleCategory] = [:]
        
        for product in allProducts {
            guard let catId = product.categoryId,
                  let catName = product.categoryName else { continue }
            
            if uniqueCategories[catId] == nil {
                uniqueCategories[catId] = DynamicSaleCategory(id: catId, label: catName)
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
            allProducts = try await api.fetchPublicList("sale-products")
        } catch {
            errorMessage = "Ürünler yüklenemedi: \(error.localizedDescription)"
        }

        isLoading = false
    }

    // MARK: - Helpers

    func resetFilters() {
        selectedCategoryIds.removeAll()
        searchText = ""
        sortOption = .nameAsc
        showInStockOnly = false
    }

    var activeFilterCount: Int {
        var count = 0
        count += selectedCategoryIds.count
        if !searchText.isEmpty         { count += 1 }
        if showInStockOnly             { count += 1 }
        if sortOption != .nameAsc      { count += 1 }
        return count
    }
}
