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
                    if let rType = campaign.rewardType, rType != "discount" {
                        Image(systemName: rType == "gift_cafe" ? "cup.and.saucer.fill" : "bag.fill")
                            .font(.title)
                            .foregroundColor(.yellow)
                        Text("HEDİYE")
                            .font(.caption2)
                            .fontWeight(.bold)
                            .foregroundColor(.primary)
                    } else if let type = campaign.discountType, let val = campaign.discountValue {
                        if type == "percentage" {
                            Text("%\(Int(val))")
                                .font(.title)
                                .fontWeight(.black)
                                .foregroundColor(.yellow)
                        } else {
                            Text("₺\(Int(val))")
                                .font(.title)
                                .fontWeight(.black)
                                .foregroundColor(.yellow)
                        }
                        Text("İNDİRİM")
                            .font(.caption2)
                            .fontWeight(.bold)
                            .foregroundColor(.primary)
                    }
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
        if let terms = campaign.terms, !terms.isEmpty {
            return terms.components(separatedBy: "\n").map { $0.trimmingCharacters(in: .whitespacesAndNewlines) }.filter { !$0.isEmpty }
        }
        return []
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
