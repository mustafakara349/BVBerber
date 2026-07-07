//
//  PastAppointmentCardView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct PastAppointmentCardView: View {

    let appointment: Appointment

    var body: some View {

        HStack(spacing: 14) {

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

            VStack(alignment: .leading, spacing: 4) {
                Text(appointment.serviceName)
                    .font(.headline)
                    .foregroundColor(.primary)

                if let barberName = appointment.barberName, !barberName.isEmpty {
                    Text(barberName)
                        .font(.caption)
                        .foregroundColor(.secondary)
                }

                Text(appointment.shortDate)
                    .font(.subheadline)
                    .foregroundColor(.secondary)
            }

            Spacer()

            VStack(alignment: .trailing, spacing: 6) {
                Text("₺\(appointment.price)")
                    .fontWeight(.bold)
                    .foregroundColor(.yellow)

                statusBadge
            }
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6))
        )
    }

    private var statusBadge: some View {
        Text(appointment.statusLabel)
            .font(.caption2)
            .padding(.horizontal, 8)
            .padding(.vertical, 3)
            .background(statusBadgeColor.opacity(0.12))
            .foregroundColor(statusBadgeColor)
            .cornerRadius(6)
    }

    private var fallbackIcon: some View {
        ZStack {
            RoundedRectangle(cornerRadius: 12)
                .fill(Color.yellow.opacity(0.12))
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
