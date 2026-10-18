//
//  PastAppointmentCardView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct PastAppointmentCardView: View {

    let appointment: Appointment

    /// Verilirse ve randevu değerlendirilebilir durumdaysa "Değerlendir" butonu gösterilir.
    var onReview: ((Appointment) -> Void)? = nil

    @State private var showReviewDetail: Bool = false

    var body: some View {

        VStack(spacing: 12) {

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
                if let originalPrice = appointment.originalPrice, originalPrice > appointment.price {
                    Text("₺\(originalPrice)")
                        .font(.caption)
                        .strikethrough()
                        .foregroundColor(.secondary)
                }
                Text("₺\(appointment.price)")
                    .fontWeight(.bold)
                    .foregroundColor(.yellow)

                statusBadge
            }
        }

        reviewFooter

        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6))
        )
        .sheet(isPresented: $showReviewDetail) {
            UserReviewDetailSheet(appointment: appointment)
        }
    }

    // MARK: - Değerlendirme Alanı

    @ViewBuilder
    private var reviewFooter: some View {
        if appointment.canReview, let onReview {
            Button {
                onReview(appointment)
            } label: {
                HStack(spacing: 6) {
                    Image(systemName: "star.fill")
                    Text("Değerlendir")
                        .fontWeight(.semibold)
                }
                .font(.subheadline)
                .foregroundColor(.black)
                .frame(maxWidth: .infinity)
                .padding(.vertical, 10)
                .background(
                    RoundedRectangle(cornerRadius: 12)
                        .fill(Color.yellow)
                )
            }
            .buttonStyle(.plain)
        } else if appointment.isReviewed == true, let rating = appointment.rating {
            Button {
                let generator = UIImpactFeedbackGenerator(style: .light)
                generator.impactOccurred()
                showReviewDetail = true
            } label: {
                HStack(spacing: 8) {
                    // 5 Yıldız üzerinden doluluk gösterimi
                    HStack(spacing: 3) {
                        ForEach(1...5, id: \.self) { i in
                            Image(systemName: i <= rating ? "star.fill" : "star")
                                .font(.system(size: 13))
                                .foregroundColor(i <= rating ? .yellow : .gray.opacity(0.35))
                        }
                    }

                    HStack(spacing: 3) {
                        Text("Yorumu Gör")
                            .font(.caption2)
                            .fontWeight(.medium)
                            .foregroundColor(.secondary)

                        Image(systemName: "chevron.right")
                            .font(.system(size: 9, weight: .bold))
                            .foregroundColor(.secondary.opacity(0.7))
                    }
                }
                .padding(.horizontal, 10)
                .padding(.vertical, 6)
                .background(
                    Capsule()
                        .fill(Color(UIColor.secondarySystemBackground))
                )
                .overlay(
                    Capsule()
                        .stroke(Color.gray.opacity(0.25), lineWidth: 1)
                )
            }
            .buttonStyle(.plain)
            .frame(maxWidth: .infinity, alignment: .trailing)
        }
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

// MARK: - Müşterinin Değerlendirme ve Yorum Detay Sayfası

struct UserReviewDetailSheet: View {

    let appointment: Appointment
    @Environment(\.dismiss) private var dismiss

    var body: some View {
        VStack(spacing: 0) {
            Capsule()
                .fill(Color.secondary.opacity(0.35))
                .frame(width: 40, height: 5)
                .padding(.top, 12)
                .padding(.bottom, 20)

            VStack(spacing: 20) {
                // Başlık & Randevu Bilgisi
                VStack(spacing: 6) {
                    Text("Değerlendirmeniz")
                        .font(.title3.bold())
                        .foregroundColor(.primary)

                    Text(appointment.serviceName)
                        .font(.subheadline)
                        .foregroundColor(.secondary)
                        .multilineTextAlignment(.center)

                    if let barber = appointment.barberName, !barber.isEmpty {
                        Text("\(barber) · \(appointment.shortDate)")
                            .font(.caption)
                            .foregroundColor(.secondary)
                    } else {
                        Text(appointment.shortDate)
                            .font(.caption)
                            .foregroundColor(.secondary)
                    }
                }

                // 5 Yıldız Gösterimi
                if let rating = appointment.rating {
                    VStack(spacing: 6) {
                        HStack(spacing: 8) {
                            ForEach(1...5, id: \.self) { i in
                                Image(systemName: i <= rating ? "star.fill" : "star")
                                    .font(.system(size: 32))
                                    .foregroundColor(i <= rating ? .yellow : .gray.opacity(0.3))
                            }
                        }

                        Text("\(rating) / 5 Yıldız")
                            .font(.footnote.bold())
                            .foregroundColor(.secondary)
                    }
                    .padding(.vertical, 4)
                }

                // Yorum Metni Alanı
                VStack(alignment: .leading, spacing: 8) {
                    Text("YAZDIĞINIZ YORUM")
                        .font(.caption2)
                        .fontWeight(.bold)
                        .foregroundColor(.secondary)

                    if let comment = appointment.reviewComment, !comment.trimmingCharacters(in: .whitespacesAndNewlines).isEmpty {
                        Text(comment)
                            .font(.subheadline)
                            .foregroundColor(.primary)
                            .lineSpacing(4)
                            .frame(maxWidth: .infinity, alignment: .leading)
                            .padding(16)
                            .background(
                                RoundedRectangle(cornerRadius: 14)
                                    .fill(Color(UIColor.secondarySystemBackground))
                            )
                            .overlay(
                                RoundedRectangle(cornerRadius: 14)
                                    .stroke(Color.gray.opacity(0.25), lineWidth: 1)
                            )
                    } else {
                        HStack(spacing: 8) {
                            Image(systemName: "text.bubble")
                                .foregroundColor(.secondary)
                            Text("Yalnızca yıldız puanı verildi, metinli bir yorum yazılmadı.")
                                .font(.footnote)
                                .foregroundColor(.secondary)
                                .italic()
                        }
                        .frame(maxWidth: .infinity, alignment: .leading)
                        .padding(14)
                        .background(
                            RoundedRectangle(cornerRadius: 14)
                                .fill(Color(UIColor.secondarySystemBackground))
                        )
                        .overlay(
                            RoundedRectangle(cornerRadius: 14)
                                .stroke(Color.gray.opacity(0.2), lineWidth: 1)
                        )
                    }
                }
                .frame(maxWidth: .infinity, alignment: .leading)

                Spacer(minLength: 0)

                Button {
                    dismiss()
                } label: {
                    Text("Kapat")
                        .fontWeight(.semibold)
                        .foregroundColor(.black)
                        .frame(maxWidth: .infinity)
                        .padding(.vertical, 14)
                        .background(
                            RoundedRectangle(cornerRadius: 14)
                                .fill(Color.yellow)
                        )
                }
            }
            .padding(.horizontal, 20)
            .padding(.bottom, 24)
        }
        .presentationDetents([.medium])
        .presentationDragIndicator(.hidden)
    }
}

