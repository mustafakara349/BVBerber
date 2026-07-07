//
//  ServicesView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct ServicesView: View {
    
    @EnvironmentObject var viewModel: ServicesViewModel
    
    @State private var searchText = ""
    @State private var selectedGenderSegment = 0 // 0 = Erkek, 1 = Kadın
    @State private var selectedCategory = "Tümü"
    
    private let columns = [
        GridItem(.flexible(), spacing: 16),
        GridItem(.flexible(), spacing: 16)
    ]
    
    var categories: [String] {
        if selectedGenderSegment == 0 {
            return ["Tümü", "Saç Hizmetleri", "Tırnak Hizmetleri", "Makyaj Hizmetleri", "Cilt Bakımı Hizmetleri", "Lazer Epilasyon Hizmetleri", "Masaj Hizmetleri"]
        } else {
            return ["Tümü", "Tırnak Hizmetleri", "Makyaj Hizmetleri", "Cilt Bakımı Hizmetleri", "Lazer Epilasyon Hizmetleri", "Saç Hizmetleri", "Masaj Hizmetleri"]
        }
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
        NavigationStack {
            VStack(spacing: 0) {
                // HEADER
                HStack {
                    Text("Hizmetler")
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
            .toolbar(.visible, for: .tabBar)
        }
        .task {
            await viewModel.fetchServices()
        }
    }
    
    // Normalize and map database categories to UI category chips
    private func categoryMatches(serviceCategory: String, selectedCategory: String) -> Bool {
        if selectedCategory == "Tümü" { return true }
        
        let normalizedService = serviceCategory.lowercased().trimmingCharacters(in: .whitespacesAndNewlines)
        let normalizedSelected = selectedCategory.lowercased().trimmingCharacters(in: .whitespacesAndNewlines)
        
        if normalizedService == normalizedSelected { return true }
        
        switch normalizedSelected {
        case "saç hizmetleri":
            return normalizedService == "saç" || normalizedService == "sakal" || normalizedService == "vip paketler" || normalizedService == "saç hizmetleri"
        case "cilt bakımı hizmetleri":
            return normalizedService == "bakım" || normalizedService == "cilt bakımı hizmetleri"
        default:
            return normalizedService == normalizedSelected
        }
    }
}

#Preview {
    ServicesView()
}
