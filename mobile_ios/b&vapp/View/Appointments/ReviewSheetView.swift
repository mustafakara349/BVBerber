//
//  ReviewSheetView.swift
//  b&vapp
//
//  Randevu değerlendirme akışı (NPS funnel):
//  1-3 yıldız  → geri bildirim yöneticiye iletilir, Google'a yönlendirilmez.
//  4-5 yıldız  → (sunucu uygun görürse) Google Haritalar yorum daveti gösterilir.
//

import SwiftUI
import UIKit

struct ReviewSheetView: View {

    let appointment: Appointment
    /// Başarılı gönderimden sonra verilen puan ve yorumla çağrılır (kartı güncellemek için).
    var onSubmitted: (Int, String?) -> Void

    @Environment(\.dismiss) private var dismiss
    @Environment(\.openURL) private var openURL

    private enum Stage {
        case form
        case thankYou
        case googleInvite
    }

    @State private var stage: Stage = .form
    @State private var rating: Int = 0
    @State private var comment: String = ""
    @State private var isSubmitting = false
    @State private var errorMessage: String?
    @State private var result: ReviewSubmissionResult?

    private let maxCommentLength = 1000

    var body: some View {
        VStack(spacing: 0) {
            Capsule()
                .fill(Color.secondary.opacity(0.35))
                .frame(width: 40, height: 5)
                .padding(.top, 12)
                .padding(.bottom, 14)

            ScrollView(showsIndicators: false) {
                VStack(spacing: 0) {
                    switch stage {
                    case .form:         formView
                    case .thankYou:     thankYouView
                    case .googleInvite: googleInviteView
                    }
                }
                .padding(.horizontal, 20)
                .padding(.bottom, 24)
            }
        }
        .presentationDetents([.medium, .large])
        .presentationDragIndicator(.hidden)
        .interactiveDismissDisabled(isSubmitting)
        .animation(.easeInOut(duration: 0.25), value: stage)
    }

    // MARK: - 1. Form

    private var formView: some View {
        VStack(spacing: 18) {
            VStack(spacing: 6) {
                Text("Randevuyu Değerlendir")
                    .font(.title3.bold())
                    .foregroundColor(.primary)

                Text(appointmentSummary)
                    .font(.footnote)
                    .foregroundColor(.secondary)
                    .multilineTextAlignment(.center)
            }
            .padding(.top, 4)

            // Yıldız Seçimi
            HStack(spacing: 12) {
                ForEach(1...5, id: \.self) { value in
                    Button {
                        let generator = UIImpactFeedbackGenerator(style: .light)
                        generator.impactOccurred()
                        withAnimation(.spring(response: 0.25, dampingFraction: 0.6)) {
                            rating = value
                        }
                    } label: {
                        Image(systemName: value <= rating ? "star.fill" : "star")
                            .font(.system(size: 38))
                            .foregroundColor(value <= rating ? Color.yellow : Color.gray.opacity(0.3))
                            .scaleEffect(value == rating ? 1.15 : 1.0)
                    }
                    .buttonStyle(.plain)
                    .accessibilityLabel("\(value) yıldız")
                }
            }
            .padding(.vertical, 4)

            // Puan geri bildirim etiketi & Yorum alanı
            if rating > 0 {
                HStack(spacing: 6) {
                    Image(systemName: rating <= 3 ? "exclamationmark.bubble" : "hand.thumbsup.fill")
                        .font(.caption)
                    Text(ratingPrompt)
                        .font(.footnote)
                        .fontWeight(.medium)
                }
                .foregroundColor(rating <= 3 ? .orange : Color(UIColor.systemGreen))
                .multilineTextAlignment(.center)
                .padding(.horizontal, 14)
                .padding(.vertical, 8)
                .background(
                    Capsule()
                        .fill(rating <= 3 ? Color.orange.opacity(0.12) : Color.green.opacity(0.12))
                )
                .transition(.opacity.combined(with: .scale(scale: 0.95)))

                // Textbox (Yorum Kutusu)
                VStack(alignment: .trailing, spacing: 6) {
                    ZStack(alignment: .topLeading) {
                        if comment.isEmpty {
                            Text(rating <= 3 ? "Yaşadığınız sorunu kısaca anlatın (isteğe bağlı)..." : "Yorumunuz ve düşünceleriniz (isteğe bağlı)...")
                                .font(.subheadline)
                                .foregroundColor(Color(UIColor.placeholderText))
                                .padding(.horizontal, 14)
                                .padding(.vertical, 12)
                                .allowsHitTesting(false)
                        }

                        TextEditor(text: $comment)
                            .font(.subheadline)
                            .foregroundColor(.primary)
                            .scrollContentBackground(.hidden)
                            .padding(.horizontal, 8)
                            .padding(.vertical, 6)
                            .frame(height: 95)
                            .onChange(of: comment) { newValue in
                                if newValue.count > maxCommentLength {
                                    comment = String(newValue.prefix(maxCommentLength))
                                }
                            }
                    }
                    .background(
                        RoundedRectangle(cornerRadius: 14)
                            .fill(Color(UIColor.secondarySystemBackground))
                    )
                    .overlay(
                        RoundedRectangle(cornerRadius: 14)
                            .stroke(Color.gray.opacity(0.3), lineWidth: 1)
                    )

                    if !comment.isEmpty {
                        Text("\(comment.count)/\(maxCommentLength)")
                            .font(.caption2)
                            .foregroundColor(.secondary)
                            .padding(.trailing, 4)
                    }
                }
                .transition(.opacity)
            }

            if let errorMessage {
                Text(errorMessage)
                    .font(.footnote)
                    .foregroundColor(.red)
                    .multilineTextAlignment(.center)
            }

            // Gönder Butonu
            Button {
                Task { await submit() }
            } label: {
                HStack(spacing: 8) {
                    if isSubmitting {
                        ProgressView()
                            .tint(rating > 0 ? .black : .secondary)
                    }
                    Text(isSubmitting ? "Gönderiliyor..." : "Gönder")
                        .fontWeight(.bold)
                }
                .foregroundColor(rating > 0 ? .black : Color.secondary)
                .frame(maxWidth: .infinity)
                .padding(.vertical, 14)
                .background(
                    RoundedRectangle(cornerRadius: 14)
                        .fill(rating > 0 ? Color.yellow : Color(UIColor.systemGray5))
                )
            }
            .disabled(rating == 0 || isSubmitting)

            Button("Vazgeç") { dismiss() }
                .font(.subheadline)
                .foregroundColor(.secondary)
                .padding(.top, 2)
                .disabled(isSubmitting)
        }
    }

    // MARK: - 2a. Teşekkür (düşük puan / Google daveti yok)

    private var thankYouView: some View {
        VStack(spacing: 16) {
            Image(systemName: (result?.rating ?? rating) <= 3 ? "text.bubble.fill" : "checkmark.seal.fill")
                .font(.system(size: 54))
                .foregroundColor((result?.rating ?? rating) <= 3 ? .orange : .yellow)
                .padding(.top, 20)

            Text("Teşekkürler!")
                .font(.title2.bold())
                .foregroundColor(.primary)

            Text((result?.rating ?? rating) <= 3
                 ? "Geri bildiriminiz işletme yöneticimize iletildi. En kısa sürede değerlendireceğiz."
                 : "Değerlendirmeniz için teşekkür ederiz.")
                .font(.subheadline)
                .foregroundColor(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 10)

            closeButton
                .padding(.top, 8)
        }
    }

    // MARK: - 2b. Google daveti (4-5 yıldız)

    private var googleInviteView: some View {
        VStack(spacing: 16) {
            Image(systemName: "star.circle.fill")
                .font(.system(size: 58))
                .foregroundColor(.yellow)
                .padding(.top, 14)

            Text("Bizi Google'da da destekler misiniz? 🌟")
                .font(.title3.bold())
                .foregroundColor(.primary)
                .multilineTextAlignment(.center)

            Text("Memnun kalmanıza çok sevindik. Google Haritalar'da kısa bir yorum bırakmanız bize çok büyük destek olur.")
                .font(.subheadline)
                .foregroundColor(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 10)

            Button {
                openGoogleReview()
            } label: {
                HStack(spacing: 8) {
                    Image(systemName: "mappin.and.ellipse")
                    Text("Google'da Yorum Yap").fontWeight(.bold)
                    Image(systemName: "arrow.up.right")
                        .font(.footnote.bold())
                }
                .foregroundColor(.black)
                .frame(maxWidth: .infinity)
                .padding(.vertical, 14)
                .background(RoundedRectangle(cornerRadius: 14).fill(Color.yellow))
            }
            .padding(.top, 6)

            Button("Şimdilik Geç") { dismiss() }
                .font(.subheadline)
                .foregroundColor(.secondary)
                .padding(.top, 2)
        }
    }

    private var closeButton: some View {
        Button {
            dismiss()
        } label: {
            Text("Kapat")
                .fontWeight(.semibold)
                .foregroundColor(.black)
                .frame(maxWidth: .infinity)
                .padding(.vertical, 14)
                .background(RoundedRectangle(cornerRadius: 14).fill(Color.yellow))
        }
    }

    // MARK: - Helpers

    private var appointmentSummary: String {
        var parts: [String] = [appointment.serviceName]
        if let barber = appointment.barberName, !barber.isEmpty { parts.append(barber) }
        parts.append(appointment.shortDate)
        return parts.joined(separator: " · ")
    }

    private var ratingPrompt: String {
        rating <= 3
            ? "Görüşleriniz doğrudan yöneticimize iletilecektir"
            : "Hizmetimizden memnun kalmanıza sevindik!"
    }

    private func submit() async {
        guard rating > 0, !isSubmitting, let appointmentId = appointment.id else { return }

        isSubmitting = true
        errorMessage = nil

        do {
            let response = try await APIClient.shared.submitReview(
                appointmentId: appointmentId,
                rating: rating,
                comment: comment
            )
            result = response
            let trimmed = comment.trimmingCharacters(in: .whitespacesAndNewlines)
            onSubmitted(response.rating, trimmed.isEmpty ? nil : trimmed)

            stage = (response.shouldPromptGoogle && validGoogleURL(response.googleReviewUrl) != nil)
                ? .googleInvite
                : .thankYou
        } catch {
            errorMessage = error.localizedDescription
        }

        isSubmitting = false
    }

    /// Yalnızca https şemalı, sunucudan gelen linkler açılır.
    private func validGoogleURL(_ raw: String?) -> URL? {
        guard let raw,
              let url = URL(string: raw.trimmingCharacters(in: .whitespacesAndNewlines)),
              url.scheme?.lowercased() == "https",
              url.host != nil else { return nil }
        return url
    }

    private func openGoogleReview() {
        guard let url = validGoogleURL(result?.googleReviewUrl) else {
            dismiss()
            return
        }

        if let reviewId = result?.reviewId {
            Task { await APIClient.shared.trackGoogleReviewClick(reviewId: reviewId) }
        }

        openURL(url)
        dismiss()
    }
}
