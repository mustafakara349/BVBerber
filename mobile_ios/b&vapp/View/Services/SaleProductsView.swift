//
//  SaleProductsView.swift
//  b&vapp
//
//  Satış ürünleri - filtreleme, sıralama, arama destekli liste
//  Created by Mustafa KARA on 24.09.2026.
//

import SwiftUI

// MARK: - Ana Görünüm

struct SaleProductsView: View {

    @StateObject private var viewModel = SaleProductsViewModel()
    @State private var showFilterSheet = false
    @State private var selectedProduct: SaleProduct? = nil

    var initialProduct: SaleProduct? = nil

    var body: some View {
        VStack(spacing: 0) {

            // ── Başlık & Aksiyonlar ─────────────────────────────
            headerSection

            // ── Arama ───────────────────────────────────────────
            searchBarSection
                .padding(.horizontal)
                .padding(.top, 12)

            // ── Filtrele Butonu ──────────────────────────────────
            filterButtonSection
                .padding(.horizontal)
                .padding(.vertical, 10)

            // ── Ürün Listesi ─────────────────────────────────────
            productListSection
        }
        .navigationBarTitleDisplayMode(.inline)
        .toolbar(.hidden, for: .tabBar)
        .task {
            await viewModel.fetchProducts()
            if let p = initialProduct {
                selectedProduct = p
            }
        }
        .sheet(item: $selectedProduct) { product in
            SaleProductDetailSheet(product: product, allProducts: viewModel.filteredProducts)
        }
        .sheet(isPresented: $showFilterSheet) {
            SaleProductsFilterSheet(viewModel: viewModel)
        }
    }
}

// MARK: - Section Extensions

private extension SaleProductsView {

    // ── Header ────────────────────────────────────────────────────
    var headerSection: some View {
        HStack(alignment: .top) {
            VStack(alignment: .leading, spacing: 4) {
                Text("Ürünlerimiz")
                    .font(.largeTitle.bold())
                    .foregroundColor(.primary)
                Text("\(viewModel.filteredProducts.count) ürün")
                    .font(.subheadline)
                    .foregroundColor(.secondary)
            }
            Spacer()
        }
        .padding(.horizontal)
        .padding(.top, 10)
    }

    // ── Arama ─────────────────────────────────────────────────────
    var searchBarSection: some View {
        HStack(spacing: 8) {
            Image(systemName: "magnifyingglass")
                .foregroundColor(.secondary)
            TextField("Ürün ara...", text: $viewModel.searchText)
                .submitLabel(.search)
            if !viewModel.searchText.isEmpty {
                Button { viewModel.searchText = "" } label: {
                    Image(systemName: "xmark.circle.fill")
                        .foregroundColor(.secondary)
                }
            }
        }
        .padding(12)
        .background(Color(.systemGray6))
        .cornerRadius(14)
    }

    // ── Filtrele Butonu ──────────────────────────────────────────
    var filterButtonSection: some View {
        HStack {
            Button {
                showFilterSheet = true
            } label: {
                HStack(spacing: 8) {
                    Image(systemName: "line.3.horizontal.decrease.circle.fill")
                        .font(.system(size: 16))
                    Text("Filtrele & Sırala")
                        .font(.system(size: 14, weight: .bold))
                    if viewModel.activeFilterCount > 0 {
                        Text("(\(viewModel.activeFilterCount))")
                            .font(.system(size: 12, weight: .bold))
                            .padding(.horizontal, 6)
                            .padding(.vertical, 2)
                            .background(Color.yellow)
                            .foregroundColor(.black)
                            .clipShape(Capsule())
                    }
                }
                .foregroundColor(.white)
                .padding(.vertical, 12)
                .padding(.horizontal, 16)
                .background(Color.black)
                .cornerRadius(12)
            }
            
            Spacer()
            
            if viewModel.activeFilterCount > 0 {
                Button {
                    withAnimation { viewModel.resetFilters() }
                } label: {
                    Text("Temizle")
                        .font(.system(size: 14, weight: .bold))
                        .foregroundColor(.red)
                }
            }
        }
    }

    // ── Ürün Listesi ──────────────────────────────────────────────
    var productListSection: some View {
        ScrollView(showsIndicators: false) {
            if viewModel.isLoading {
                LazyVGrid(columns: [GridItem(.flexible(), spacing: 16), GridItem(.flexible(), spacing: 16)], spacing: 20) {
                    ForEach(0..<6, id: \.self) { _ in
                        ShimmerProductGridItem()
                    }
                }
                .padding(.horizontal)
                .padding(.top, 4)
            } else if let err = viewModel.errorMessage {
                errorView(message: err)
            } else if viewModel.filteredProducts.isEmpty {
                emptyView
            } else {
                LazyVGrid(columns: [GridItem(.flexible(), spacing: 16), GridItem(.flexible(), spacing: 16)], spacing: 20) {
                    ForEach(viewModel.filteredProducts) { product in
                        Button {
                            selectedProduct = product
                        } label: {
                            SaleProductGridItem(product: product)
                        }
                        .buttonStyle(.plain)
                    }
                }
                .padding(.horizontal)
                .padding(.top, 4)
                .padding(.bottom, 24)
            }
        }
    }

    // ── Boş Durum ──────────────────────────────────────────────────
    var emptyView: some View {
        VStack(spacing: 14) {
            Spacer().frame(height: 48)
            Text("Ürün Bulunamadı")
                .font(.headline)
            Text("Filtrelerinize uygun ürün bulunamadı.")
                .font(.subheadline)
                .foregroundColor(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 40)
            if viewModel.activeFilterCount > 0 {
                Button("Filtreleri Temizle") {
                    withAnimation { viewModel.resetFilters() }
                }
                .foregroundColor(.yellow)
                .fontWeight(.semibold)
            }
        }
        .frame(maxWidth: .infinity)
    }

    func errorView(message: String) -> some View {
        VStack(spacing: 12) {
            Image(systemName: "wifi.slash")
                .font(.system(size: 40))
                .foregroundColor(.secondary)
                .padding(.top, 48)
            Text("Bağlantı Hatası")
                .font(.headline)
            Text(message)
                .font(.caption)
                .foregroundColor(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 40)
            Button("Tekrar Dene") {
                Task { await viewModel.fetchProducts() }
            }
            .foregroundColor(.yellow)
            .fontWeight(.semibold)
        }
        .frame(maxWidth: .infinity)
    }
}

// MARK: - Sale Product Grid Item

struct SaleProductGridItem: View {
    let product: SaleProduct

    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            // Görsel Kartı
            ZStack(alignment: .topTrailing) {
                ZStack(alignment: .bottomLeading) {
                    Group {
                        if !product.imageUrl.isEmpty, let url = URL(string: product.imageUrl) {
                            CachedAsyncImage(url: url) { phase in
                                switch phase {
                                case .success(let img):
                                    img.resizable()
                                       .scaledToFill()
                                default:
                                    productPlaceholder
                                }
                            }
                        } else {
                            productPlaceholder
                        }
                    }
                    .frame(width: UIScreen.main.bounds.width / 2 - 24, height: UIScreen.main.bounds.width / 2 - 24) // Fixed square ratio responsive
                    .clipped()
                    .cornerRadius(16)

                    // Fiyat Etiketi
                    HStack(spacing: 4) {
                        Text(product.formattedPrice)
                            .foregroundColor(.yellow)
                            .fontWeight(.bold)
                            .font(.caption)
                    }
                    .padding(.horizontal, 8)
                    .padding(.vertical, 4)
                    .background(Color.black.opacity(0.6))
                    .cornerRadius(8)
                    .padding(8)
                }
            }

            // Başlık
            Text(product.name)
                .foregroundColor(.primary)
                .fontWeight(.semibold)
                .lineLimit(1)
        }
        .frame(width: UIScreen.main.bounds.width / 2 - 24)
    }

    private var productPlaceholder: some View {
        ZStack {
            LinearGradient(
                colors: [Color(.systemGray6), Color(.systemGray5)],
                startPoint: .topLeading, endPoint: .bottomTrailing
            )

        }
    }
}

// MARK: - Sale Product Detail Sheet

struct SaleProductDetailSheet: View {
    let product: SaleProduct
    let allProducts: [SaleProduct]
    @Environment(\.dismiss) private var dismiss

    var similarProducts: [SaleProduct] {
        allProducts.filter { $0.id != product.id && $0.categoryId == product.categoryId }.prefix(5).map { $0 }
    }

    var body: some View {
        NavigationStack {
            ScrollView {
                VStack(alignment: .leading, spacing: 24) {

                    // Görsel
                    Group {
                        if !product.imageUrl.isEmpty, let url = URL(string: product.imageUrl) {
                            CachedAsyncImage(url: url) { phase in
                                switch phase {
                                case .success(let img):
                                    img.resizable()
                                        .scaledToFit()
                                        .frame(maxWidth: .infinity, maxHeight: .infinity)
                                        .background(
                                            ZStack {
                                                Color(UIColor.systemBackground)
                                                img.resizable()
                                                    .scaledToFill()
                                                    .blur(radius: 20)
                                                    .opacity(0.8)
                                            }
                                        )
                                        .clipped()
                                default:
                                    detailPlaceholder
                                }
                            }
                        } else {
                            detailPlaceholder
                        }
                    }
                    .frame(maxWidth: .infinity)
                    .frame(height: 320)
                    .clipped()
                    .cornerRadius(24)
                    .shadow(color: .black.opacity(0.08), radius: 10, x: 0, y: 5)
                    .padding(.horizontal)

                    // Başlık & Fiyat
                    VStack(alignment: .leading, spacing: 10) {
                        if !product.categoryInfo.label.isEmpty {
                            Text(product.categoryInfo.label)
                                .font(.system(size: 13, weight: .bold))
                                .foregroundColor(.secondary)
                                .textCase(.uppercase)
                                .tracking(1.2)
                        }
                        Text(product.name)
                            .font(.system(size: 26, weight: .heavy))
                            .foregroundColor(.primary)
                        Text(product.formattedPrice)
                            .font(.system(size: 28, weight: .heavy))
                            .foregroundColor(.yellow)
                    }
                    .padding(.horizontal)

                    Divider().padding(.horizontal)

                    // Stok durumu & SKU
                    HStack {
                        stockStatusRow
                        Spacer()
                        if !product.sku.isEmpty {
                            VStack(alignment: .trailing, spacing: 2) {
                                Text("SKU")
                                    .font(.system(size: 10, weight: .semibold))
                                    .foregroundColor(.secondary)
                                    .textCase(.uppercase)
                                    .tracking(0.5)
                                Text(product.sku)
                                    .font(.system(size: 14, weight: .bold))
                            }
                        }
                    }
                    .padding(.horizontal)
                    .padding(.vertical, 8)
                    .background(Color(.secondarySystemGroupedBackground))
                    .cornerRadius(16)
                    .padding(.horizontal)

                    // Açıklama
                    if !product.description.isEmpty {
                        VStack(alignment: .leading, spacing: 10) {
                            Text("Ürün Hakkında")
                                .font(.system(size: 18, weight: .bold))
                            Text(product.description)
                                .font(.system(size: 15))
                                .foregroundColor(.secondary)
                                .lineSpacing(6)
                        }
                        .padding(.horizontal)
                    }

                    // Benzer Ürünler
                    if !similarProducts.isEmpty {
                        VStack(alignment: .leading, spacing: 16) {
                            Text("Benzer Ürünler")
                                .font(.system(size: 18, weight: .bold))
                                .padding(.horizontal)
                            
                            ScrollView(.horizontal, showsIndicators: false) {
                                HStack(spacing: 16) {
                                    ForEach(similarProducts) { similarProduct in
                                        NavigationLink(destination: SaleProductDetailSheet(product: similarProduct, allProducts: allProducts)) {
                                            SaleProductGridItem(product: similarProduct)
                                        }
                                        .buttonStyle(.plain)
                                    }
                                }
                                .padding(.horizontal)
                            }
                        }
                        .padding(.top, 10)
                    }
                }
                .padding(.vertical, 20)
            }
            .navigationTitle(product.name)
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .topBarTrailing) {
                    Button { dismiss() } label: {
                        Image(systemName: "xmark.circle.fill")
                            .font(.system(size: 24))
                            .foregroundColor(.secondary)
                    }
                }
            }
        }
    }

    private var detailPlaceholder: some View {
        ZStack {
            LinearGradient(
                colors: [Color(.systemGray6), Color(.systemGray5)],
                startPoint: .topLeading, endPoint: .bottomTrailing
            )

        }
    }

    @ViewBuilder
    private var stockStatusRow: some View {
        HStack(spacing: 8) {
            switch product.stockStatus {
            case .ok:
                Image(systemName: "checkmark.seal.fill")
                    .foregroundColor(.green)
                    .font(.system(size: 20))
                Text("Stokta Mevcut")
                    .font(.system(size: 15, weight: .bold))
                    .foregroundColor(.green)
            case .low:
                Image(systemName: "exclamationmark.triangle.fill")
                    .foregroundColor(.orange)
                    .font(.system(size: 20))
                Text("Az Kaldı")
                    .font(.system(size: 15, weight: .bold))
                    .foregroundColor(.orange)
            case .empty:
                Image(systemName: "xmark.seal.fill")
                    .foregroundColor(.red)
                    .font(.system(size: 20))
                Text("Şu An Stokta Yok")
                    .font(.system(size: 15, weight: .bold))
                    .foregroundColor(.red)
            }
        }
    }
}

// MARK: - Shimmer Product Grid Item

struct ShimmerProductGridItem: View {
    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            ShimmerCard(width: UIScreen.main.bounds.width / 2 - 24, height: UIScreen.main.bounds.width / 2 - 24, cornerRadius: 20)
            ShimmerCard(width: UIScreen.main.bounds.width / 2 - 40, height: 16, cornerRadius: 5)
        }
    }
}

#Preview {
    NavigationStack {
        SaleProductsView()
    }
}

// MARK: - Filter Sheet

struct SaleProductsFilterSheet: View {
    @Environment(\.dismiss) var dismiss
    @ObservedObject var viewModel: SaleProductsViewModel
    
    var body: some View {
        NavigationStack {
            Form {
                Section(header: Text("Sıralama")) {
                    Picker("Sırala", selection: $viewModel.sortOption) {
                        ForEach(ProductSortOption.allCases) { option in
                            Text(option.label).tag(option)
                        }
                    }
                    .pickerStyle(.menu)
                }
                
                Section(header: Text("Kategoriler")) {
                    ForEach(viewModel.availableCategories) { cat in
                        Button {
                            if viewModel.selectedCategoryIds.contains(cat.id) {
                                viewModel.selectedCategoryIds.remove(cat.id)
                            } else {
                                viewModel.selectedCategoryIds.insert(cat.id)
                            }
                        } label: {
                            HStack {

                                Text(cat.label)
                                    .foregroundColor(.primary)
                                Spacer()
                                if viewModel.selectedCategoryIds.contains(cat.id) {
                                    Image(systemName: "checkmark")
                                        .foregroundColor(.yellow)
                                        .fontWeight(.bold)
                                }
                            }
                        }
                    }
                }
                
                Section(header: Text("Stok Durumu")) {
                    Toggle("Yalnızca Stokta Olanlar", isOn: $viewModel.showInStockOnly)
                        .tint(.yellow)
                }
            }
            .navigationTitle("Filtrele & Sırala")
            .navigationBarTitleDisplayMode(.inline)
            .toolbar {
                ToolbarItem(placement: .navigationBarLeading) {
                    if viewModel.activeFilterCount > 0 {
                        Button("Temizle") {
                            viewModel.resetFilters()
                        }
                        .foregroundColor(.red)
                    }
                }
                ToolbarItem(placement: .navigationBarTrailing) {
                    Button("Tamamla") {
                        dismiss()
                    }
                    .foregroundColor(.yellow)
                    .fontWeight(.bold)
                }
            }
        }
    }
}
