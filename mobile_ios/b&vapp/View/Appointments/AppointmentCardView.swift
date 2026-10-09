//
//  AppointmentCardView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct AppointmentCardView: View {

    let appointment: Appointment
    var onCancel: (() -> Void)? = nil
    var onDirections: (() -> Void)? = nil

    var body: some View {
        VStack(alignment: .leading, spacing: 14) {

            // MARK: Üst Kısım
            HStack(spacing: 12) {

                // Berber Fotoğrafı veya Hizmet ikonu fallback
                Group {
                    if let urlStr = appointment.barberImageUrl,
                       !urlStr.isEmpty,
                       let url = URL(string: urlStr) {
                        CachedAsyncImage(url: url) { phase in
                            switch phase {
                            case .success(let image):
                                image
                                    .resizable()
                                    .scaledToFill()
                                    .frame(width: 50, height: 50)
                                    .clipShape(RoundedRectangle(cornerRadius: 12))
                            default:
                                fallbackIcon
                            }
                        }
                    } else {
                        fallbackIcon
                    }
                }

                VStack(alignment: .leading, spacing: 3) {
                    Text(appointment.serviceName)
                        .font(.headline)
                        .foregroundColor(.primary)

                    if let barberName = appointment.barberName, !barberName.isEmpty {
                        Text(barberName)
                            .font(.subheadline)
                            .foregroundColor(.secondary)
                    }
                }

                Spacer()

                VStack(alignment: .trailing, spacing: 4) {
                    if let originalPrice = appointment.originalPrice, originalPrice > appointment.price {
                        Text("₺\(originalPrice)")
                            .font(.caption)
                            .strikethrough()
                            .foregroundColor(.secondary)
                    }
                    Text("₺\(appointment.price)")
                        .fontWeight(.bold)
                        .foregroundColor(.yellow)

                    Text(appointment.statusLabel)
                        .font(.caption2)
                        .padding(.horizontal, 8)
                        .padding(.vertical, 3)
                        .background(statusBadgeColor.opacity(0.15))
                        .foregroundColor(statusBadgeColor)
                        .cornerRadius(6)
                }
            }

            Divider()

            // MARK: Tarih & Saat
            HStack(spacing: 6) {
                Image(systemName: "clock")
                Text(appointment.formattedDate)
                    .font(.subheadline)
            }
            .foregroundColor(.secondary)

            // MARK: Konum
            HStack(spacing: 6) {
                Image(systemName: "location")
                Text("B&V Coffee Barber – Tarsus/Mersin")
                    .font(.subheadline)
            }
            .foregroundColor(.secondary)
            
            // MARK: Kampanya Hediyesi
            if let rType = appointment.rewardType {
                HStack(spacing: 12) {
                    ZStack {
                        Circle()
                            .fill(Color.yellow.opacity(0.15))
                            .frame(width: 36, height: 36)
                        Image(systemName: rType == "gift_cafe" ? "cup.and.saucer.fill" : "bag.fill")
                            .foregroundColor(.yellow)
                            .font(.system(size: 16))
                    }
                    
                    VStack(alignment: .leading, spacing: 4) {
                        Text("Kampanya Hediyesi")
                            .font(.caption)
                            .foregroundColor(.secondary)
                        
                        Text(appointment.rewardName ?? "Sürpriz Hediye")
                            .font(.subheadline)
                            .fontWeight(.bold)
                            .foregroundColor(.primary)
                            
                        Text("Randevunuz esnasında hediyenizi personelimizden talep ediniz.")
                            .font(.system(size: 9))
                            .foregroundColor(.secondary)
                    }
                    Spacer()
                }
                .padding(.horizontal, 12)
                .padding(.vertical, 8)
                .background(
                    RoundedRectangle(cornerRadius: 12)
                        .stroke(Color.yellow.opacity(0.3), lineWidth: 1)
                        .background(Color.yellow.opacity(0.05).cornerRadius(12))
                )
            }

            // MARK: Butonlar
            HStack(spacing: 12) {
                Button { onCancel?() } label: {
                    Text("İptal Et")
                        .fontWeight(.semibold)
                        .foregroundColor(.red)
                        .frame(maxWidth: .infinity)
                        .padding(12)
                        .overlay(
                            RoundedRectangle(cornerRadius: 10)
                                .stroke(Color.red.opacity(0.6), lineWidth: 1)
                        )
                }

                Button { onDirections?() } label: {
                    Text("Yol Tarifi")
                        .fontWeight(.semibold)
                        .foregroundColor(.black)
                        .frame(maxWidth: .infinity)
                        .padding(12)
                        .background(Color.yellow)
                        .cornerRadius(10)
                }
            }
        }
        .padding(16)
        .background(
            RoundedRectangle(cornerRadius: 18)
                .fill(Color(.systemGray6))
        )
    }

    private var fallbackIcon: some View {
        ZStack {
            RoundedRectangle(cornerRadius: 12)
                .fill(Color.yellow.opacity(0.15))
                .frame(width: 50, height: 50)
            Text(appointment.barberInitials)
                .font(.headline)
                .fontWeight(.bold)
                .foregroundColor(.yellow)
        }
    }

    private var statusBadgeColor: Color {
        switch appointment.status {
        case "pending":
            return .orange
        case "cancelled", "rejected":
            return .red
        case "completed":
            return .green
        case "no_show":
            return .gray
        case "in_progress":
            return .blue
        default:
            return .green
        }
    }

    private var serviceIcon: String {
        let name = appointment.serviceName.lowercased()
        if name.contains("sakal") { return "mustache" }
        if name.contains("cilt") || name.contains("bakım") { return "sparkles" }
        if name.contains("boya") { return "paintbrush" }
        return "scissors"
    }
}
