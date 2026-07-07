//
//  ShimmerView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 02.05.2026.
//
//  Harici kütüphane gerektirmeyen, saf SwiftUI shimmer (skeleton) efekti.
//  Kullanım: AnyView().shimmer(isActive: Bool)
//

import SwiftUI

// MARK: - Shimmer Modifier

struct ShimmerModifier: ViewModifier {

    var isActive: Bool

    @State private var phase: CGFloat = -1.0

    func body(content: Content) -> some View {
        if isActive {
            content
                .overlay(shimmerGradient)
                .mask(content)
                .onAppear {
                    withAnimation(
                        .linear(duration: 1.4)
                        .repeatForever(autoreverses: false)
                    ) {
                        phase = 1.0
                    }
                }
        } else {
            content
        }
    }

    private var shimmerGradient: some View {
        GeometryReader { geo in
            let width = geo.size.width * 3
            LinearGradient(
                stops: [
                    .init(color: Color(.systemGray5).opacity(0.6), location: 0),
                    .init(color: Color(.systemGray3).opacity(0.9), location: 0.4),
                    .init(color: Color(.systemGray5).opacity(0.6), location: 0.8),
                ],
                startPoint: .leading,
                endPoint: .trailing
            )
            .frame(width: width)
            .offset(x: width * phase)
        }
    }
}

extension View {
    /// Yükleme sırasında shimmer (yanıp sönen) efekti uygular.
    func shimmer(isActive: Bool = true) -> some View {
        modifier(ShimmerModifier(isActive: isActive))
    }
}


// MARK: - Hazır Skeleton Bileşenleri

/// Metin alanı için shimmer placeholder
struct ShimmerText: View {
    var width: CGFloat
    var height: CGFloat = 14
    var cornerRadius: CGFloat = 6

    var body: some View {
        RoundedRectangle(cornerRadius: cornerRadius)
            .fill(Color(.systemGray5))
            .frame(width: width, height: height)
            .shimmer()
    }
}

/// Daire profil fotoğrafı için shimmer placeholder
struct ShimmerCircle: View {
    var size: CGFloat

    var body: some View {
        Circle()
            .fill(Color(.systemGray5))
            .frame(width: size, height: size)
            .shimmer()
    }
}

/// Genel kart için shimmer placeholder
struct ShimmerCard: View {
    var width: CGFloat = .infinity
    var height: CGFloat
    var cornerRadius: CGFloat = 14

    var body: some View {
        RoundedRectangle(cornerRadius: cornerRadius)
            .fill(Color(.systemGray5))
            .frame(maxWidth: width == .infinity ? nil : width, maxHeight: height)
            .frame(height: height)
            .shimmer()
    }
}

/// Ana sayfa hizmet kartı skeleton (160×180 görsel + isim)
struct ShimmerServiceCard: View {
    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            ShimmerCard(width: 160, height: 180, cornerRadius: 16)
            ShimmerText(width: 100, height: 14)
        }
        .frame(width: 160)
    }
}

/// Header profil alanı skeleton (daire + 2 metin)
struct ShimmerHeaderSkeleton: View {
    var body: some View {
        HStack(spacing: 12) {
            ShimmerCircle(size: 50)
            VStack(alignment: .leading, spacing: 6) {
                ShimmerText(width: 60, height: 11)
                ShimmerText(width: 100, height: 16)
            }
            Spacer()
            // bildirim butonu placeholder
            ShimmerCircle(size: 42)
        }
    }
}

/// SelectAppointmentView saat grid skeleton
struct ShimmerTimeGrid: View {
    var body: some View {
        LazyVGrid(
            columns: Array(repeating: GridItem(.flexible()), count: 4),
            spacing: 14
        ) {
            ForEach(0..<12, id: \.self) { _ in
                ShimmerCard(width: .infinity, height: 44, cornerRadius: 10)
            }
        }
    }
}

/// ServicesView grid hizmet kartı skeleton
struct ShimmerGridServiceCard: View {
    var body: some View {
        VStack(alignment: .leading, spacing: 10) {
            ShimmerCard(width: .infinity, height: 150, cornerRadius: 20)
            ShimmerText(width: 100, height: 16)
            ShimmerText(width: 60, height: 12)
        }
    }
}
