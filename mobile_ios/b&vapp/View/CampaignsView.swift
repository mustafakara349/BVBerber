//
//  CampaignsView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct CampaignsView: View {
    @EnvironmentObject var viewModel: HomeViewModel
    var initialSelectedCampaign: Campaign?
    
    var body: some View {
        ScrollView {
            ScrollViewReader { proxy in
                VStack(spacing: 20) {
                    if viewModel.campaigns.isEmpty {
                        Text("Şu an aktif bir kampanya bulunmamaktadır.")
                            .foregroundColor(.secondary)
                            .padding(.top, 50)
                    } else {
                        ForEach(viewModel.campaigns) { campaign in
                            DetailedCampaignCard(campaign: campaign)
                                .id(campaign.id)
                        }
                    }
                }
                .padding(.vertical)
                .onAppear {
                    if let selected = initialSelectedCampaign {
                        DispatchQueue.main.asyncAfter(deadline: .now() + 0.1) {
                            withAnimation {
                                proxy.scrollTo(selected.id, anchor: .top)
                            }
                        }
                    }
                }
            }
        }
        .background(Color(.systemBackground).ignoresSafeArea())
        .navigationTitle("Kampanyalar")
        .navigationBarTitleDisplayMode(.inline)
    }
}

struct DetailedCampaignCard: View {
    let campaign: Campaign
    
    var body: some View {
        VStack(alignment: .leading, spacing: 12) {
            
            HStack {
                Text("KAMPANYA")
                    .font(.caption2)
                    .fontWeight(.bold)
                    .foregroundColor(.yellow)
                    .padding(.horizontal, 10)
                    .padding(.vertical, 5)
                    .background(Color.yellow.opacity(0.15))
                    .cornerRadius(8)
                
                Spacer()
                
                if let endDate = campaign.endDate {
                    HStack(spacing: 4) {
                        Image(systemName: "calendar.badge.clock")
                            .font(.caption)
                            .foregroundColor(.yellow)
                        Text("Son gün: \(formatCampaignDate(endDate))")
                            .font(.caption)
                            .foregroundColor(.yellow.opacity(0.8))
                    }
                }
            }
            
            Text(campaign.title)
                .font(.title3)
                .fontWeight(.bold)
                .foregroundColor(.primary)
            
            Text(campaign.description)
                .font(.subheadline)
                .foregroundColor(.secondary)
            
            Divider()
                .background(Color.yellow.opacity(0.3))
            
            Text("Kampanya Şartları:")
                .font(.headline)
                .foregroundColor(.primary)
            
            VStack(alignment: .leading, spacing: 8) {
                ForEach(campaignConditions, id: \.self) { condition in
                    ConditionRow(text: condition)
                }
            }
            
            HStack {
                Spacer()
                
                VStack(spacing: 4) {
                    if campaign.discountType == "percentage" {
                        Text("%\(Int(campaign.discountValue))")
                            .font(.title)
                            .fontWeight(.black)
                            .foregroundColor(.yellow)
                    } else {
                        Text("₺\(Int(campaign.discountValue))")
                            .font(.title)
                            .fontWeight(.black)
                            .foregroundColor(.yellow)
                    }
                    Text("İNDİRİM")
                        .font(.caption2)
                        .fontWeight(.bold)
                        .foregroundColor(.primary)
                }
                .padding()
                .background(Color(.systemGray6))
                .cornerRadius(12)
            }
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6).opacity(0.5))
                .shadow(color: Color.black.opacity(0.1), radius: 5, x: 0, y: 2)
        )
        .overlay(
            RoundedRectangle(cornerRadius: 16)
                .stroke(Color.yellow.opacity(0.3), lineWidth: 1)
        )
        .padding(.horizontal)
    }
    
    var campaignConditions: [String] {
        var list: [String] = []
        
        // 1. Türü
        if let type = campaign.type {
            if type == "auto_apply" {
                list.append("Bu kampanya sepetinizde otomatik olarak uygulanır.")
            } else {
                list.append("Bu kampanyadan yararlanmak için kupon kodu girilmelidir.")
            }
        }
        
        // 2. Müşteri Kitlesi
        if let audience = campaign.targetAudience {
            switch audience {
            case "new_customers":
                list.append("Kampanya sadece yeni müşterilerimiz için geçerlidir.")
            case "loyalty_members":
                list.append("Kampanya sadece sadakat programı üyelerimiz için geçerlidir.")
            default:
                list.append("Kampanya tüm müşterilerimiz için geçerlidir.")
            }
        }
        
        // 3. Minimum Sipariş Tutarı
        if let minAmount = campaign.minOrderAmount, minAmount > 0 {
            list.append("Minimum sipariş tutarı ₺\(Int(minAmount)) olmalıdır.")
        }
        
        // 4. Maksimum İndirim
        if let maxAmount = campaign.maxDiscountAmount, maxAmount > 0 {
            list.append("Maksimum indirim tutarı ₺\(Int(maxAmount)) ile sınırlıdır.")
        }
        
        // 5. Hizmetler / Kategoriler
        if let cats = campaign.categories, !cats.isEmpty {
            let catNames = cats.joined(separator: ", ")
            list.append("Kampanya yalnızca şu kategorilerde geçerlidir: \(catNames).")
        } else {
            list.append("Kampanya tüm hizmet kategorilerinde geçerlidir.")
        }
        
        // 6. Kişi Başı Limit
        if let limit = campaign.perCustomerLimit, limit > 0 {
            list.append("Bu kampanya kişi başı en fazla \(limit) defa kullanılabilir.")
        }
        
        // Standart koşullar
        list.append("Bu kampanya diğer indirimlerle birleştirilemez.")
        list.append("Sadece belirtilen geçerlilik tarihleri arasında kullanılabilir.")
        
        return list
    }
    
    private func formatCampaignDate(_ dateStr: String) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        if let date = formatter.date(from: dateStr) {
            formatter.dateFormat = "d MMMM yyyy"
            formatter.locale = Locale(identifier: "tr_TR")
            return formatter.string(from: date)
        }
        return dateStr
    }
}

struct ConditionRow: View {
    let text: String
    var body: some View {
        HStack(alignment: .top, spacing: 8) {
            Circle()
                .fill(Color.yellow)
                .frame(width: 6, height: 6)
                .padding(.top, 6)
            Text(text)
                .font(.subheadline)
                .foregroundColor(.secondary)
        }
    }
}

#Preview {
    NavigationView {
        CampaignsView()
            .environmentObject(HomeViewModel())
    }
}
