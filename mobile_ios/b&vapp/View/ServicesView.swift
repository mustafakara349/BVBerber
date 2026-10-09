//
//  ServicesView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct ServicesView: View {
    @EnvironmentObject var homeVM: HomeViewModel
    
    @State private var showBarber = false
    @State private var showCafe = false
    @State private var showProduct = false
    
    @State private var initialService: Service? = nil
    @State private var initialCafeProduct: CafeProduct? = nil
    @State private var initialSaleProduct: SaleProduct? = nil

    var body: some View {
        NavigationStack {
            ScrollView(showsIndicators: false) {
                VStack(alignment: .leading, spacing: 24) {
                    
                    HStack(alignment: .top) {
                        VStack(alignment: .leading, spacing: 8) {
                            Text("HİZMETLERİMİZ")
                                .font(.subheadline)
                                .fontWeight(.semibold)
                                .foregroundColor(.yellow)
                                .textCase(.uppercase)
                            
                            Text("Sizin İçin Neler\nYapabiliriz?")
                                .font(.system(size: 30, weight: .bold))
                                .foregroundColor(.primary)
                                .lineSpacing(4)
                        }
                        
                        Spacer()
                        
                        NavigationLink(destination: CampaignsView()) {
                            HStack(spacing: 6) {
                                Text("Kampanyalar")
                                    .font(.subheadline.bold())
                            }
                            .foregroundColor(.black)
                            .padding(.horizontal, 16)
                            .padding(.vertical, 10)
                            .background(Color.yellow)
                            .cornerRadius(20)
                            .shadow(color: Color.yellow.opacity(0.4), radius: 5, x: 0, y: 3)
                        }
                        .padding(.top, 8)
                    }
                    .padding(.horizontal)
                    .padding(.top, 16)
                    
                    VStack(spacing: 20) {
                        // 1. Berber & Bakım Kartı
                        NavigationLink(destination: BarberServicesView()) {
                            PremiumCategoryCard(
                                title: "Berber & Bakım",
                                subtitle: "Saç kesimi, sakal tıraşı ve cilt bakımı ile kendinizi yenileyin.",
                                imageUrl: "https://images.unsplash.com/photo-1599351431202-1e0f0137899a?q=80&w=600&auto=format&fit=crop",
                                iconName: "scissors"
                            )
                        }
                        
                        // 2. Kafe Kartı
                        NavigationLink(destination: CafeServicesView()) {
                            PremiumCategoryCard(
                                title: "Kafe",
                                subtitle: "Sıcak & soğuk içeceklerimiz ve özel atıştırmalıklarımızla mola verin.",
                                imageUrl: "https://images.unsplash.com/photo-1554118811-1e0d58224f24?q=80&w=600&auto=format&fit=crop",
                                iconName: "cup.and.saucer.fill"
                            )
                        }
                        
                        // 3. Ürünlerimiz Kartı
                        NavigationLink(destination: ProductsServicesView()) {
                            PremiumCategoryCard(
                                title: "Ürünlerimiz",
                                subtitle: "Kullandığımız premium saç ve sakal bakım ürünlerini keşfedin.",
                                imageUrl: "https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=600&auto=format&fit=crop",
                                iconName: "bag.fill"
                            )
                        }
                    }
                    .padding(.horizontal)
                    .padding(.bottom, 32)
                }
            }
            .navigationBarHidden(true)
            .navigationDestination(isPresented: $showBarber) {
                BarberServicesView(initialService: initialService)
            }
            .navigationDestination(isPresented: $showCafe) {
                CafeServicesView(initialProduct: initialCafeProduct)
            }
            .navigationDestination(isPresented: $showProduct) {
                ProductsServicesView(initialProduct: initialSaleProduct)
            }
        }
        .onReceive(homeVM.$pendingNavigationAction) { action in
            if let action = action {
                homeVM.pendingNavigationAction = nil
                DispatchQueue.main.asyncAfter(deadline: .now() + 0.1) {
                    switch action {
                    case .barber(let s):
                        self.initialService = s
                        self.showBarber = true
                    case .cafe(let c):
                        self.initialCafeProduct = c
                        self.showCafe = true
                    case .product(let p):
                        self.initialSaleProduct = p
                        self.showProduct = true
                    }
                }
            }
        }
    }
}

struct PremiumCategoryCard: View {
    let title: String
    let subtitle: String
    let imageUrl: String
    let iconName: String
    
    var body: some View {
        ZStack(alignment: .bottomLeading) {
            // Arka Plan Resmi
            AsyncImage(url: URL(string: imageUrl)) { phase in
                switch phase {
                case .empty:
                    Rectangle()
                        .fill(Color(.systemGray5))
                        .overlay(ProgressView())
                case .success(let image):
                    image
                        .resizable()
                        .aspectRatio(contentMode: .fill)
                case .failure:
                    Rectangle()
                        .fill(Color(.systemGray4))
                        .overlay(Image(systemName: "photo").foregroundColor(.gray))
                @unknown default:
                    EmptyView()
                }
            }
            .frame(height: 220)
            .frame(maxWidth: .infinity)
            .clipped()
            
            // Koyu Gradyan (Yazıların okunabilmesi için)
            LinearGradient(
                gradient: Gradient(colors: [Color.black.opacity(0.8), Color.black.opacity(0.1), Color.clear]),
                startPoint: .bottom,
                endPoint: .top
            )
            
            // İçerik
            VStack(alignment: .leading, spacing: 8) {
                HStack(spacing: 8) {
                    Image(systemName: iconName)
                        .font(.title3)
                        .foregroundColor(.yellow)
                    Text(title)
                        .font(.title2.bold())
                        .foregroundColor(.white)
                }
                
                Text(subtitle)
                    .font(.subheadline)
                    .foregroundColor(.white.opacity(0.9))
                    .lineLimit(2)
                    .multilineTextAlignment(.leading)
            }
            .padding(20)
        }
        .frame(height: 220)
        .cornerRadius(20)
        .shadow(color: Color.black.opacity(0.15), radius: 10, x: 0, y: 5)
    }
}

struct CafeServicesView: View {
    var initialProduct: CafeProduct? = nil
    var body: some View {
        CafeMenuView(initialProduct: initialProduct)
    }
}

struct ProductsServicesView: View {
    var initialProduct: SaleProduct? = nil
    var body: some View {
        SaleProductsView(initialProduct: initialProduct)
    }
}

// MARK: - Eski Hizmetler Sayfası
struct BarberServicesView: View {
    var initialService: Service? = nil
    
    @EnvironmentObject var viewModel: ServicesViewModel
    
    @State private var searchText = ""
    @State private var selectedGenderSegment = 0 // 0 = Erkek, 1 = Kadın
    @State private var selectedCategory = "Tümü"
    @State private var animateSearchBar = false
    
    private let columns = [
        GridItem(.flexible(), spacing: 16),
        GridItem(.flexible(), spacing: 16)
    ]
    
    var categories: [String] {
        let matchedServices = viewModel.services.filter { service in
            let serviceGender = service.genderType ?? "unisex"
            if selectedGenderSegment == 0 {
                return serviceGender == "male" || serviceGender == "unisex"
            } else {
                return serviceGender == "female" || serviceGender == "unisex"
            }
        }
        
        let uniqueCategories = Array(Set(matchedServices.map { $0.category }))
            .sorted()
            .filter { !$0.isEmpty }
            
        return ["Tümü"] + uniqueCategories
    }
    
    var filteredServices: [Service] {
        viewModel.services.filter { service in
            // 1. Gender Filter
            let serviceGender = service.genderType ?? "unisex"
            let genderMatch: Bool
            if selectedGenderSegment == 0 {
                genderMatch = serviceGender == "male" || serviceGender == "unisex"
            } else {
                genderMatch = serviceGender == "female" || serviceGender == "unisex"
            }
            
            guard genderMatch else { return false }
            
            // 2. Category Filter
            let categoryMatch = categoryMatches(serviceCategory: service.category, selectedCategory: selectedCategory)
            guard categoryMatch else { return false }
            
            // 3. Search Filter
            if !searchText.isEmpty {
                let nameMatch = service.name.localizedCaseInsensitiveContains(searchText)
                let descMatch = service.description.localizedCaseInsensitiveContains(searchText)
                return nameMatch || descMatch
            }
            
            return true
        }
    }
    
    
    var body: some View {
        VStack(spacing: 0) {
            // HEADER - (Optional, can rely on NavigationTitle, but keeping it for UI consistency)
            HStack {
                Text("Berber & Bakım")
                    .font(.largeTitle)
                    .fontWeight(.bold)
                    .foregroundColor(.primary)
                Spacer()
            }
            .padding(.horizontal)
            .padding(.top, 10)
                
                // SEARCH FIELD
                HStack {
                    Image(systemName: "magnifyingglass")
                        .foregroundColor(.secondary)
                    TextField("Hizmet ara...", text: $searchText)
                        .foregroundColor(.primary)
                        .submitLabel(.search)
                    if !searchText.isEmpty {
                        Button(action: {
                            searchText = ""
                        }) {
                            Image(systemName: "xmark.circle.fill")
                                .foregroundColor(.secondary)
                        }
                    }
                }
                .padding(12)
                .background(Color(.systemGray6))
                .cornerRadius(12)
                .overlay(
                    RoundedRectangle(cornerRadius: 12)
                        .stroke(Color.yellow, lineWidth: 3)
                        .scaleEffect(animateSearchBar ? 1.02 : 1)
                        .opacity(animateSearchBar ? 1 : 0)
                )
                .animation(.easeInOut(duration: 0.6).repeatCount(2, autoreverses: true), value: animateSearchBar)
                .padding(.horizontal)
                .padding(.top, 8)
                
                // CUSTOM SEGMENT PICKER
                HStack(spacing: 0) {
                    ForEach(0..<2) { index in
                        Text(index == 0 ? "Erkek Bölümü" : "Kadın Bölümü")
                            .font(.system(size: 14, weight: .bold))
                            .foregroundColor(selectedGenderSegment == index ? .black : .secondary)
                            .frame(maxWidth: .infinity)
                            .padding(.vertical, 10)
                            .contentShape(Rectangle())
                            .onTapGesture {
                                withAnimation(.spring(response: 0.3, dampingFraction: 0.75)) {
                                    selectedGenderSegment = index
                                    selectedCategory = "Tümü"
                                }
                            }
                    }
                }
                .background(
                    GeometryReader { geo in
                        RoundedRectangle(cornerRadius: 10)
                            .fill(Color.yellow)
                            .frame(width: geo.size.width / 2 - 4, height: geo.size.height - 8)
                            .padding(4)
                            .offset(x: selectedGenderSegment == 0 ? 0 : geo.size.width / 2)
                    }
                )
                .background(Color(.systemGray6))
                .cornerRadius(12)
                .padding(.horizontal)
                .padding(.top, 12)
                
                // HORIZONTAL CATEGORY CHIPS
                ScrollView(.horizontal, showsIndicators: false) {
                    HStack(spacing: 8) {
                        ForEach(categories, id: \.self) { category in
                            let isSelected = selectedCategory == category
                            Text(category)
                                .font(.system(size: 13, weight: .semibold))
                                .padding(.horizontal, 16)
                                .padding(.vertical, 8)
                                .foregroundColor(isSelected ? .black : .primary)
                                .background(isSelected ? Color.yellow : Color(.systemGray6))
                                .cornerRadius(20)
                                .onTapGesture {
                                    withAnimation(.easeOut(duration: 0.2)) {
                                        selectedCategory = category
                                    }
                                }
                        }
                    }
                    .padding(.horizontal)
                }
                .padding(.vertical, 12)
                
                // CONTENT
                ScrollView(showsIndicators: false) {
                    if viewModel.isLoading {
                        LazyVGrid(columns: columns, spacing: 16) {
                            ForEach(0..<6, id: \.self) { _ in
                                ShimmerGridServiceCard()
                            }
                        }
                        .padding(.horizontal)
                    } else if filteredServices.isEmpty {
                        VStack(spacing: 12) {
                            Image(systemName: "scissors.badge.ellipsis")
                                .font(.system(size: 48))
                                .foregroundColor(.secondary)
                                .padding(.top, 40)
                            Text("Hizmet Bulunmadı")
                                .font(.headline)
                                .foregroundColor(.primary)
                            Text("Arama kriterlerinize uygun aktif hizmet bulunmamaktadır.")
                                .font(.subheadline)
                                .foregroundColor(.secondary)
                                .multilineTextAlignment(.center)
                                .padding(.horizontal, 40)
                        }
                        .frame(maxWidth: .infinity)
                    } else {
                        LazyVGrid(columns: columns, spacing: 16) {
                            ForEach(filteredServices) { service in
                                ServiceCardView(service: service)
                            }
                        }
                        .padding(.horizontal)
                        .padding(.bottom, 20)
                    }
                }
        }
        .navigationBarTitleDisplayMode(.inline)
        .toolbar(.hidden, for: .tabBar)
        .task {
            await viewModel.fetchServices()
            if let service = initialService {
                searchText = service.name
                if let gender = service.genderType {
                    selectedGenderSegment = gender == "female" ? 1 : 0
                }
                
                // Animasyonu tetikle (arama barını vurgula)
                DispatchQueue.main.asyncAfter(deadline: .now() + 0.3) {
                    animateSearchBar = true
                    DispatchQueue.main.asyncAfter(deadline: .now() + 2.5) {
                        animateSearchBar = false
                    }
                }
            }
        }
    }
    
    // Normalize and map database categories to UI category chips
    private func categoryMatches(serviceCategory: String, selectedCategory: String) -> Bool {
        if selectedCategory == "Tümü" { return true }
        return serviceCategory.lowercased().trimmingCharacters(in: .whitespacesAndNewlines) == 
               selectedCategory.lowercased().trimmingCharacters(in: .whitespacesAndNewlines)
    }
}

#Preview {
    ServicesView()
}
