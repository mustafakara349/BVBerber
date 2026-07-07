//
//  ProfilePhotoOnboardingView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 02.05.2026.
//
//  Kayıt sonrası kullanıcıyı profil fotoğrafı eklemeye yönlendiren onboarding ekranı.
//  Sağ üst köşedeki ✓ butonu → Firebase Storage yükleme → MainView
//  "Daha Sonra"    → isNewlyRegistered = false → MainView
//

import SwiftUI
import PhotosUI

struct ProfilePhotoOnboardingView: View {

    @StateObject private var viewModel = ProfileViewModel()

    // b_vappApp'taki @StateObject ile aynı nesneyi observe ediyoruz.
    // Böylece isNewlyRegistered = false anında root view değişir.
    @ObservedObject private var authManager = AuthManager.shared

    var body: some View {

        NavigationStack {
            ZStack {

                LinearGradient(
                    colors: [Color.black, Color(white: 0.08)],
                    startPoint: .top,
                    endPoint: .bottom
                )
                .ignoresSafeArea()

                VStack(spacing: 36) {

                    Spacer()

                    // Profil fotoğrafı önizleme
                    ZStack {
                        Circle()
                            .fill(Color.yellow.opacity(0.12))
                            .frame(width: 140, height: 140)
                            .overlay(Circle().stroke(Color.yellow.opacity(0.4), lineWidth: 2))

                        if let image = viewModel.profileImage {
                            Image(uiImage: image)
                                .resizable()
                                .scaledToFill()
                                .frame(width: 140, height: 140)
                                .clipShape(Circle())
                                .overlay(Circle().stroke(Color.yellow, lineWidth: 3))
                        } else {
                            Image(systemName: "person.fill")
                                .font(.system(size: 56))
                                .foregroundColor(.yellow.opacity(0.6))
                        }

                        // Yükleniyor overlay
                        if viewModel.isUploadingPhoto {
                            ZStack {
                                Circle().fill(Color.black.opacity(0.5))
                                    .frame(width: 140, height: 140)
                                ProgressView().tint(.yellow)
                            }
                        }
                    }

                    // Başlık + açıklama
                    VStack(spacing: 10) {
                        Text("Profil Fotoğrafı")
                            .font(.title.bold())
                            .foregroundColor(.white)

                        Text(viewModel.profileImage == nil
                             ? "Profil fotoğrafı ekleyebilirsin. \n Bunu daha sonra da yapabilirsin."
                             : "Fotoğrafı kaydetmek için sağ üstteki ✓ butonuna bas.")
                            .font(.subheadline)
                            .foregroundColor(.gray)
                            .multilineTextAlignment(.center)
                            .fixedSize(horizontal: false, vertical: true)
                            .padding(.horizontal, 20)
                            .animation(.easeInOut(duration: 0.2), value: viewModel.profileImage != nil)
                    }

                    Spacer()

                    VStack(spacing: 14) {

                        // FOTOĞRAF SEÇ / ÇEK
                        Button {
                            viewModel.showPhotoOptions = true
                        } label: {
                            HStack {
                                Image(systemName: "camera.fill")
                                Text(viewModel.profileImage == nil
                                     ? "Fotoğraf Ekle"
                                     : "Farklı Fotoğraf Seç")
                                    .fontWeight(.semibold)
                            }
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .foregroundColor(.black)
                            .cornerRadius(16)
                        }
                        .confirmationDialog(
                            "Fotoğraf Kaynağı",
                            isPresented: $viewModel.showPhotoOptions,
                            titleVisibility: .visible
                        ) {
                            Button("Galeriden Seç")  { viewModel.showPhotoPicker = true }
                            Button("Kameradan Çek") { viewModel.showCamera = true }
                            Button("İptal", role: .cancel) { }
                        }

                        // DAHA SONRA — authManager üzerinden flag'i false yapınca
                        // b_vappApp direkt MainView'e geçer
                        Button {
                            authManager.isNewlyRegistered = false
                        } label: {
                            Text("Daha Sonra Ekle")
                                .fontWeight(.medium)
                                .frame(maxWidth: .infinity)
                                .padding()
                                .background(Color.white.opacity(0.06))
                                .foregroundColor(.gray)
                                .cornerRadius(16)
                                .overlay(
                                    RoundedRectangle(cornerRadius: 16)
                                        .stroke(Color.white.opacity(0.1), lineWidth: 1)
                                )
                        }
                        .disabled(viewModel.isUploadingPhoto)
                    }
                    .padding(.horizontal, 28)
                    .padding(.bottom, 40)
                }
            }
            // MARK: Toolbar — Onay butonu (fotoğraf seçilince görünür)
            .toolbar {
                if viewModel.profileImage != nil {
                    ToolbarItem(placement: .navigationBarTrailing) {
                        Button {
                            // applyCroppedImage zaten upload başlatıyor.
                            // Upload bitince (veya bitmişse) ana sayfaya geç.
                            Task {
                                while viewModel.isUploadingPhoto {
                                    try? await Task.sleep(nanoseconds: 200_000_000)
                                }
                                authManager.isNewlyRegistered = false
                            }
                        } label: {
                            if viewModel.isUploadingPhoto {
                                ProgressView()
                                    .tint(.yellow)
                                    .scaleEffect(0.85)
                            } else {
                                Image(systemName: "checkmark.circle.fill")
                                    .font(.title)
                                    .foregroundColor(.yellow)
                            }
                        }
                        .disabled(viewModel.isUploadingPhoto)
                    }
                }
            }
            .toolbarBackground(.hidden, for: .navigationBar)
        }
        // Photo Picker
        .photosPicker(
            isPresented: $viewModel.showPhotoPicker,
            selection: $viewModel.photoPickerItem,
            matching: .images
        )
        .onChange(of: viewModel.photoPickerItem) { _ in
            Task { await viewModel.handlePhotoPickerItem() }
        }
        // Kamera
        .fullScreenCover(isPresented: $viewModel.showCamera) {
            CameraPickerView { image in
                viewModel.handleCameraResult(image)
            }
        }
        // Kırpma
        .fullScreenCover(isPresented: $viewModel.showCropView) {
            if let img = viewModel.selectedImage {
                ImageCropView(image: img) { cropped in
                    viewModel.applyCroppedImage(cropped)
                }
            }
        }
    }
}
