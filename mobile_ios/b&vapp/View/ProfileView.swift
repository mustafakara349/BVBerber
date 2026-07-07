//
//  ProfileView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI
import PhotosUI

struct ProfileView: View {

    @EnvironmentObject var viewModel: ProfileViewModel
    @State private var showLogoutAlert = false

    var body: some View {

        NavigationStack {

            VStack(spacing: 30) {

                Spacer().frame(height: 20)

                profileHeader

                Spacer()

                menuSection

                Spacer()

                logoutButton
            }
            .padding()
            .sheet(isPresented: $viewModel.showCropView) {
                if let image = viewModel.selectedImage {
                    ImageCropView(image: image) { croppedImage in
                        viewModel.applyCroppedImage(croppedImage)
                    }
                }
            }
            .fullScreenCover(isPresented: $viewModel.showCamera) {
                CameraPickerView { image in
                    viewModel.handleCameraResult(image)
                }
            }
            // confirmationDialog ProfileHeader içindeki kamera butonuna taşındı
            .photosPicker(isPresented: $viewModel.showPhotoPicker,
                          selection: $viewModel.photoPickerItem,
                          matching: .images)
            .onChange(of: viewModel.photoPickerItem) { _, _ in
                Task { await viewModel.handlePhotoPickerItem() }
            }
            .toolbar(.visible, for: .tabBar)
        }
        .task {
            await viewModel.fetchProfile()
        }
    }
}


// MARK: - Profile Header

extension ProfileView {

    var profileHeader: some View {

        VStack(spacing: 14) {

            ZStack(alignment: .bottomTrailing) {

                Group {
                    if let localImage = viewModel.profileImage {
                        // Kırpılmış lokal fotoğraf
                        Image(uiImage: localImage)
                            .resizable()
                            .scaledToFill()

                    } else if let url = URL(string: viewModel.profileImageUrl),
                              !viewModel.profileImageUrl.isEmpty {
                        // Firestore'daki profileImageUrl — önbellekli yükleme
                        CachedAsyncImage(url: url) { phase in
                            switch phase {
                            case .success(let image):
                                image.resizable().scaledToFill()
                            case .empty:
                                // Yüklenirken shimmer daire
                                ShimmerCircle(size: 120)
                            default:
                                defaultAvatar
                            }
                        }

                    } else {
                        defaultAvatar
                    }
                }
                .frame(width: 120, height: 120)
                .clipShape(Circle())
                .overlay(Circle().stroke(Color.yellow.opacity(0.7), lineWidth: 4))

                // Yükleniyor overlay
                if viewModel.isUploadingPhoto {
                    ZStack {
                        Circle()
                            .fill(Color.black.opacity(0.4))
                            .frame(width: 120, height: 120)
                        ProgressView()
                            .tint(.yellow)
                    }
                }

                Button {
                    viewModel.showPhotoOptions = true
                } label: {
                    Image(systemName: "camera.fill")
                        .foregroundColor(.black)
                        .padding(8)
                        .background(Color.yellow)
                        .clipShape(Circle())
                }
                // confirmationDialog butona attach: iPhone'da butonun yakınında açılır
                .confirmationDialog("Profil Fotoğrafı", isPresented: $viewModel.showPhotoOptions, titleVisibility: .visible) {
                    Button("Fotoğraf Arşivinden Seç") { viewModel.showPhotoPicker = true }
                    Button("Kameradan Çek")            { viewModel.showCamera = true }
                    Button("İptal", role: .cancel) { }
                }
                .offset(x: 5, y: 5)
            }

            // Ad Soyad — yüklenene kadar shimmer
            if viewModel.user == nil {
                ShimmerText(width: 160, height: 22)
                ShimmerText(width: 120, height: 14)
            } else {
                Text(viewModel.userName)
                    .foregroundColor(.primary)
                    .font(.title2)
                    .fontWeight(.bold)

                // E-posta
                if !viewModel.userEmail.isEmpty {
                    Text(viewModel.userEmail)
                        .font(.subheadline)
                        .foregroundColor(.secondary)
                }

                // Telefon
                if !viewModel.userPhone.isEmpty {
                    HStack(spacing: 4) {
                        Image(systemName: "phone.fill")
                            .font(.caption)
                        Text(formattedPhone(viewModel.userPhone))
                            .font(.subheadline)
                    }
                    .foregroundColor(.secondary)
                }
            }
        }
    }

    // Baş harfler avatarı
    @ViewBuilder
    var defaultAvatar: some View {
        ZStack {
            Circle().fill(Color.yellow.opacity(0.2))
            Text(viewModel.user?.initials ?? "B")
                .font(.largeTitle)
                .fontWeight(.bold)
                .foregroundColor(.yellow)
        }
    }

    // Telefon maskeleme (05XX XXX XX XX)
    private func formattedPhone(_ input: String) -> String {
        let digits = input.filter { $0.isNumber }
        guard digits.count == 11 else { return input }
        var result = ""
        for (i, c) in digits.enumerated() {
            if i == 4 || i == 7 || i == 9 {
                result.append(" ")
            }
            result.append(c)
        }
        return result
    }
}


// MARK: - Menu Section

extension ProfileView {

    var menuSection: some View {

        VStack(spacing: 16) {

            NavigationLink(destination: SecuritySettingsView()) {
                ProfileMenuItemContent(icon: "lock.fill", title: "Güvenlik ve Giriş")
            }

            NavigationLink(destination: SupportView()) {
                ProfileMenuItemContent(icon: "questionmark", title: "Destek ve Yardım")
            }
        }
    }
}


// MARK: - Logout Button

extension ProfileView {

    var logoutButton: some View {

        Button {
            showLogoutAlert = true
        } label: {
            Text("OTURUMU KAPAT")
                .foregroundColor(.red)
                .fontWeight(.bold)
                .frame(maxWidth: .infinity)
                .padding()
                .background(
                    RoundedRectangle(cornerRadius: 25)
                        .fill(Color.red.opacity(0.12))
                        .overlay(
                            RoundedRectangle(cornerRadius: 25)
                                .stroke(Color.red.opacity(0.4), lineWidth: 1)
                        )
                )
        }
        .alert("Oturumu Kapat", isPresented: $showLogoutAlert) {
            Button("Vazgeç", role: .cancel) { }
            Button("Evet, Çıkış Yap", role: .destructive) {
                viewModel.signOut()
            }
        } message: {
            Text("Oturumu kapatmak istediğinizden emin misiniz?")
        }
    }
}


// MARK: - Menu Item Content

struct ProfileMenuItemContent: View {

    var icon: String
    var title: String

    var body: some View {

        HStack(spacing: 16) {

            ZStack {
                RoundedRectangle(cornerRadius: 12)
                    .fill(Color.yellow.opacity(0.2))
                    .frame(width: 44, height: 44)
                Image(systemName: icon)
                    .foregroundColor(.yellow)
            }

            Text(title)
                .foregroundColor(.primary)
                .fontWeight(.medium)

            Spacer()

            Image(systemName: "chevron.right")
                .foregroundColor(.secondary)
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 20)
                .fill(Color(.systemGray6))
        )
    }
}


#Preview {
    ProfileView()
}
