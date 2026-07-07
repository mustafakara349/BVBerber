//
//  ImageCropView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 28.03.2026.
//

import SwiftUI

struct ImageCropView: View {
    
    let image: UIImage
    var onComplete: (UIImage) -> Void
    
    @Environment(\.dismiss) var dismiss
    
    @State private var scale: CGFloat = 1.0
    @State private var lastScale: CGFloat = 1.0
    @State private var offset: CGSize = .zero
    @State private var lastOffset: CGSize = .zero
    
    private let cropSize: CGFloat = 280
    
    var body: some View {
        
        NavigationStack {
            
            ZStack {
                
                Color.black.ignoresSafeArea()
                
                VStack(spacing: 0) {
                    
                    // HEADER
                    HStack {
                        Button("İptal") {
                            dismiss()
                        }
                        .foregroundColor(.white)
                        
                        Spacer()
                        
                        Text("Fotoğrafı Ayarla")
                            .font(.headline)
                            .foregroundColor(.white)
                        
                        Spacer()
                        
                        Button("Tamamla") {
                            let cropped = cropImage()
                            onComplete(cropped)
                        }
                        .foregroundColor(.yellow)
                        .fontWeight(.semibold)
                    }
                    .padding()
                    
                    Spacer()
                    
                    // CROP AREA
                    ZStack {
                        
                        Image(uiImage: image)
                            .resizable()
                            .scaledToFill()
                            .scaleEffect(scale)
                            .offset(offset)
                            .frame(width: cropSize, height: cropSize)
                            .clipShape(Circle())
                            .gesture(
                                DragGesture()
                                    .onChanged { value in
                                        offset = CGSize(
                                            width: lastOffset.width + value.translation.width,
                                            height: lastOffset.height + value.translation.height
                                        )
                                    }
                                    .onEnded { _ in
                                        lastOffset = offset
                                    }
                            )
                            .gesture(
                                MagnificationGesture()
                                    .onChanged { value in
                                        scale = lastScale * value
                                    }
                                    .onEnded { _ in
                                        scale = max(1.0, scale)
                                        lastScale = scale
                                    }
                            )
                        
                        Circle()
                            .stroke(Color.yellow.opacity(0.7), lineWidth: 3)
                            .frame(width: cropSize, height: cropSize)
                    }
                    
                    Spacer()
                    
                    // INSTRUCTIONS
                    VStack(spacing: 8) {
                        Text("Fotoğrafı kaydırın ve yakınlaştırın")
                            .font(.subheadline)
                            .foregroundColor(.gray)
                        
                        HStack(spacing: 20) {
                            
                            Label("Kaydır", systemImage: "hand.draw")
                                .font(.caption)
                                .foregroundColor(.gray)
                            
                            Label("Yakınlaştır", systemImage: "arrow.up.left.and.arrow.down.right")
                                .font(.caption)
                                .foregroundColor(.gray)
                        }
                    }
                    .padding(.bottom, 40)
                }
            }
            .navigationBarHidden(true)
        }
    }
    
    
    // MARK: - Crop Logic
    
    private func cropImage() -> UIImage {
        
        let imageSize = image.size
        let viewSize = cropSize
        
        let imageAspect = imageSize.width / imageSize.height
        let fitWidth: CGFloat
        let fitHeight: CGFloat
        
        if imageAspect > 1 {
            fitHeight = viewSize
            fitWidth = viewSize * imageAspect
        } else {
            fitWidth = viewSize
            fitHeight = viewSize / imageAspect
        }
        
        let scaledWidth = fitWidth * scale
        let scaledHeight = fitHeight * scale
        
        let cropRectInView = CGRect(
            x: (scaledWidth / 2) - offset.width - (viewSize / 2),
            y: (scaledHeight / 2) - offset.height - (viewSize / 2),
            width: viewSize,
            height: viewSize
        )
        
        let scaleX = imageSize.width / scaledWidth
        let scaleY = imageSize.height / scaledHeight
        
        let cropRectInImage = CGRect(
            x: cropRectInView.origin.x * scaleX,
            y: cropRectInView.origin.y * scaleY,
            width: cropRectInView.width * scaleX,
            height: cropRectInView.height * scaleY
        )
        
        guard let cgImage = image.cgImage?.cropping(to: cropRectInImage) else {
            return image
        }
        
        return UIImage(cgImage: cgImage)
    }
}
