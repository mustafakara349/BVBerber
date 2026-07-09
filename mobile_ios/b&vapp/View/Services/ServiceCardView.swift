//
//  ServiceCardView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct ServiceCardView: View {

    let service: Service

    var body: some View {
        VStack(alignment: .leading, spacing: 8) {
            ZStack(alignment: .topTrailing) {
                CachedAsyncImage(url: URL(string: service.imageUrl)) { phase in
                    switch phase {
                    case .success(let image):
                        image
                            .resizable()
                            .scaledToFill()
                            .frame(minWidth: 0, maxWidth: .infinity)
                            .frame(height: 130)
                            .clipped()
                    case .failure:
                        placeholderImage
                    case .empty:
                        // Shimmer placeholder while loading
                        ShimmerCard(width: .infinity, height: 130, cornerRadius: 16)
                    @unknown default:
                        placeholderImage
                    }
                }
                .frame(height: 130)
                .frame(maxWidth: .infinity)
                .clipped()
                .clipShape(RoundedRectangle(cornerRadius: 16))
                
                // Discount Badge on top-right of image
                if let discounted = service.discountedPrice, discounted < service.price {
                    let discountPercent = Int(round(Double(service.price - discounted) / Double(service.price) * 100))
                    Text("%\(discountPercent) İNDİRİM")
                        .font(.system(size: 9, weight: .bold))
                        .foregroundColor(.black)
                        .padding(.horizontal, 8)
                        .padding(.vertical, 4)
                        .background(Color.yellow)
                        .clipShape(Capsule())
                        .padding(8)
                }
            }

            VStack(alignment: .leading, spacing: 4) {
                Text(service.name)
                    .font(.system(size: 14, weight: .bold))
                    .foregroundColor(.primary)
                    .lineLimit(1)

                HStack {
                    durationView
                    Spacer()
                    priceView
                }
            }
            .padding(.horizontal, 4)
            .padding(.bottom, 4)
        }
        .padding(8)
        .background(Color(.secondarySystemGroupedBackground))
        .cornerRadius(20)
        .shadow(color: Color.black.opacity(0.04), radius: 6, x: 0, y: 2)
    }
}

private extension ServiceCardView {

    var placeholderImage: some View {
        ZStack {
            Color.gray.opacity(0.1)
            Image(systemName: "scissors")
                .font(.title)
                .foregroundColor(.gray)
        }
        .frame(height: 130)
        .frame(maxWidth: .infinity)
    }

    var priceView: some View {
        HStack(spacing: 4) {
            if let discounted = service.discountedPrice, discounted < service.price {
                Text("₺\(service.price)")
                    .font(.system(size: 11))
                    .strikethrough()
                    .foregroundColor(.secondary)
                Text("₺\(discounted)")
                    .font(.system(size: 13, weight: .bold))
                    .foregroundColor(.yellow)
            } else {
                Text("₺\(service.price)")
                    .font(.system(size: 13, weight: .bold))
                    .foregroundColor(.primary)
            }
        }
    }

    var durationView: some View {
        HStack(spacing: 4) {
            Image(systemName: "clock")
                .font(.system(size: 11))
            Text("\(service.duration) dk")
                .font(.system(size: 11))
        }
        .foregroundColor(.secondary)
    }
}
