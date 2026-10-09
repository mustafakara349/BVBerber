//
//  CafeMenuView.swift
//  b&vapp
//
//  Cafe bölümü - gerçek verilerle menü listesi, kategori filtresi ve arama
//  Created by Mustafa KARA on 24.09.2026.
//

import SwiftUI

// MARK: - Ana Görünüm

struct CafeMenuView: View {

    @StateObject private var viewModel = CafeViewModel()
    @State private var showFilterSheet = false
    @State private var selectedProduct: CafeProduct?

    var initialProduct: CafeProduct? = nil

    var body: some View {
        VStack(spacing: 0) {

            // ── Başlık ──────────────────────────────────────────
            headerSection

            // ── Arama Çubuğu ────────────────────────────────────
            searchBarSection
                .padding(.horizontal)
                .padding(.top, 12)

            // ── Filtrele Butonu ──────────────────────────────────
            filterButtonSection
                .padding(.horizontal)
                .padding(.vertical, 10)

            // ── Ürün Listesi ────────────────────────────────────
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
            CafeProductDetailSheet(product: product)
        }
        .sheet(isPresented: $showFilterSheet) {
            CafeProductsFilterSheet(viewModel: viewModel)
        }
    }
}

// MARK: - Header & Components

private extension CafeMenuView {

    var headerSection: some View {
        HStack(alignment: .top) {
            VStack(alignment: .leading, spacing: 4) {
                Text("Kafe Menüsü")
                    .font(.largeTitle.bold())
                    .foregroundColor(.primary)
                Text("\(viewModel.filteredProducts.count) ürün listeleniyor")
                    .font(.subheadline)
                    .foregroundColor(.secondary)
            }
            Spacer()
        }
        .padding(.horizontal)
        .padding(.top, 10)
    }

    // ── Arama ──────────────────────────────────────────────────────
    var searchBarSection: some View {
        HStack(spacing: 8) {
            Image(systemName: "magnifyingglass")
                .foregroundColor(.secondary)
            TextField("Menüde ara...", text: $viewModel.searchText)
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

    // ── Ürün Listesi ─────────────────────────────────────────────────
    var productListSection: some View {
        ScrollView(showsIndicators: false) {
            if viewModel.isLoading {
                // Shimmer skeleton
                LazyVGrid(columns: [GridItem(.flexible(), spacing: 16), GridItem(.flexible(), spacing: 16)], spacing: 16) {
                    ForEach(0..<6, id: \.self) { _ in
                        ShimmerCafeCard()
                    }
                }
                .padding(.horizontal)
                .padding(.top, 4)
            } else if let err = viewModel.errorMessage {
                errorView(message: err)
            } else if viewModel.filteredProducts.isEmpty {
                emptyView
            } else {
                LazyVGrid(
                    columns: [GridItem(.flexible(), spacing: 16), GridItem(.flexible(), spacing: 16)],
                    spacing: 16
                ) {
                    ForEach(viewModel.filteredProducts) { product in
                        CafeProductCard(product: product)
                            .onTapGesture {
                                selectedProduct = product
                            }
                    }
                }
                .padding(.horizontal)
                .padding(.top, 4)
                .padding(.bottom, 24)
            }
        }
    }

    // ── Boş Durum ────────────────────────────────────────────────────
    var emptyView: some View {
        VStack(spacing: 14) {
            Text("☕")
                .font(.system(size: 56))
                .padding(.top, 48)
            Text("Ürün Bulunamadı")
                .font(.headline)
            Text("Arama kriterlerinize uygun cafe ürünü yok.")
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

// MARK: - Cafe Product Card (Grid)

struct CafeProductCard: View {
    let product: CafeProduct

    var body: some View {
        VStack(alignment: .leading, spacing: 0) {

            // Görsel
            ZStack(alignment: .topLeading) {
                Group {
                    if !product.imageUrl.isEmpty, let url = URL(string: product.imageUrl) {
                        CachedAsyncImage(url: url) { phase in
                            switch phase {
                            case .success(let img):
                                img.resizable().scaledToFill()
                            case .failure:
                                cafeImagePlaceholder
                            case .empty:
                                ShimmerCard(width: .infinity, height: 130, cornerRadius: 0)
                            @unknown default:
                                cafeImagePlaceholder
                            }
                        }
                    } else {
                        cafeImagePlaceholder
                    }
                }
                .frame(height: 130)
                .frame(maxWidth: .infinity)
                .clipped()
            }
            .clipShape(RoundedRectangle(cornerRadius: 16, style: .continuous))

            // İçerik
            VStack(alignment: .leading, spacing: 4) {
                // Kategori etiketi
                Text(product.categoryInfo.label)
                    .font(.system(size: 10, weight: .semibold))
                    .foregroundColor(.secondary)
                    .lineLimit(1)

                Text(product.name)
                    .font(.system(size: 14, weight: .bold))
                    .foregroundColor(.primary)
                    .lineLimit(2)
                    .fixedSize(horizontal: false, vertical: true)

                if !product.description.isEmpty {
                    Text(product.description)
                        .font(.system(size: 11))
                        .foregroundColor(.secondary)
                        .lineLimit(2)
                }

                Spacer(minLength: 4)

                Text(product.formattedPrice)
                    .font(.system(size: 15, weight: .bold))
                    .foregroundColor(.yellow)
            }
            .padding(.horizontal, 10)
            .padding(.vertical, 10)
        }
        .background(Color(.secondarySystemGroupedBackground))
        .cornerRadius(20)
        .shadow(color: .black.opacity(0.05), radius: 6, x: 0, y: 3)
    }

    private var cafeImagePlaceholder: some View {
        ZStack {
            LinearGradient(
                colors: [Color(.systemGray6), Color(.systemGray5)],
                startPoint: .topLeading, endPoint: .bottomTrailing
            )

        }
    }
}

// MARK: - Cafe Product Detail Sheet

struct CafeProductDetailSheet: View {
    let product: CafeProduct
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 20) {
                // Görsel Alanı
                ZStack(alignment: .topTrailing) {
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
                            case .failure, .empty:
                                placeholderImage
                            @unknown default:
                                placeholderImage
                            }
                        }
                        .frame(height: 300)
                        .frame(maxWidth: .infinity)
                        .clipped()
                    } else {
                        placeholderImage
                            .frame(height: 300)
                            .frame(maxWidth: .infinity)
                    }

                    // Kapatma butonu
                    Button {
                        dismiss()
                    } label: {
                        Image(systemName: "xmark.circle.fill")
                            .font(.system(size: 30))
                            .foregroundColor(.white)
                            .shadow(radius: 4)
                            .padding()
                    }
                }

                VStack(alignment: .leading, spacing: 16) {
                    HStack(alignment: .top) {
                        VStack(alignment: .leading, spacing: 6) {
                            Text(product.categoryInfo.label)
                                .font(.subheadline)
                                .foregroundColor(.secondary)
                                .textCase(.uppercase)
                            
                            Text(product.name)
                                .font(.title.bold())
                        }
                        
                        Spacer()
                        
                        Text(product.formattedPrice)
                            .font(.title2.bold())
                            .foregroundColor(.yellow)
                            .padding(.top, 4)
                    }

                    if !product.description.isEmpty {
                        Text("Açıklama")
                            .font(.headline)
                            .padding(.top, 8)
                        Text(product.description)
                            .font(.body)
                            .foregroundColor(.secondary)
                            .lineSpacing(4)
                    }

                    if !product.ingredients.isEmpty {
                        Text("İçindekiler")
                            .font(.headline)
                            .padding(.top, 8)
                        Text(product.ingredients)
                            .font(.body)
                            .foregroundColor(.secondary)
                            .lineSpacing(4)
                    }
                }
                .padding(24)
            }
        }
        .edgesIgnoringSafeArea(.top)
    }

    private var placeholderImage: some View {
        ZStack {
            LinearGradient(colors: [Color(.systemGray5), Color(.systemGray4)], startPoint: .top, endPoint: .bottom)

        }
    }
}

// MARK: - Shimmer Skeleton Card (Cafe)

struct ShimmerCafeCard: View {
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            ShimmerCard(width: .infinity, height: 130, cornerRadius: 16)
            ShimmerCard(width: 100, height: 10, cornerRadius: 6)
            ShimmerCard(width: .infinity, height: 14, cornerRadius: 6)
            ShimmerCard(width: 60, height: 14, cornerRadius: 6)
        }
        .padding(8)
        .background(Color(.secondarySystemGroupedBackground))
        .cornerRadius(20)
    }
}

#Preview {
    NavigationStack {
        CafeMenuView()
    }
}

// MARK: - Filter Sheet

struct CafeProductsFilterSheet: View {
    @Environment(\.dismiss) var dismiss
    @ObservedObject var viewModel: CafeViewModel
    
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
