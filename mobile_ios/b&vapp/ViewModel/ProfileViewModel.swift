//
//  ProfileViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import SwiftUI
import PhotosUI
import Combine

@MainActor
class ProfileViewModel: ObservableObject {

    @Published var user: UserModel? = nil
    @Published var profileImage: UIImage? = nil

    // Upload durumu
    @Published var isUploadingPhoto = false

    // Photo picker state
    @Published var showPhotoOptions = false
    @Published var showPhotoPicker = false
    @Published var showCamera = false
    @Published var showCropView = false
    @Published var selectedImage: UIImage? = nil
    @Published var photoPickerItem: PhotosPickerItem? = nil

    // MARK: - Computed

    var userName: String    { user?.fullName ?? "Kullanıcı" }
    var userEmail: String   { user?.email ?? "" }
    var userPhone: String   { user?.phone ?? "" }
    var profileImageUrl: String { user?.profileImageUrl ?? "" }

    // MARK: - Fetch Profile

    func fetchProfile() async {
        guard let userId = AuthManager.shared.currentUserId else { return }

        do {
            user = try await FirestoreManager.shared.fetchDocument("users", documentId: userId)
        } catch {
            print("Profil yüklenemedi: \(error.localizedDescription)")
        }
    }

    // MARK: - Handle Photo Selection

    func handlePhotoPickerItem() async {
        guard let item = photoPickerItem else { return }

        if let data = try? await item.loadTransferable(type: Data.self),
           let rawImage = UIImage(data: data) {
            // EXIF oryantasyonunu düzelt — 90 derece dönme sorununu giderir
            selectedImage = rawImage.fixedOrientation()
            showPhotoPicker = false
            try? await Task.sleep(nanoseconds: 300_000_000)
            showCropView = true
        }
    }

    // MARK: - Handle Camera Result

    func handleCameraResult(_ image: UIImage?) {
        guard let image = image else { return }
        // EXIF oryantasyonunu düzelt — kameradan gelen fotoğraflarda dönme olur
        selectedImage = image.fixedOrientation()
        showCamera = false
        DispatchQueue.main.asyncAfter(deadline: .now() + 0.3) {
            self.showCropView = true
        }
    }

    // MARK: - Apply Cropped Image + Firebase Storage Upload

    func applyCroppedImage(_ image: UIImage) {
        // Önce lokal göster
        profileImage = image
        showCropView = false

        // Arka planda Firebase Storage'a yükle
        Task {
            await uploadProfilePhoto(image)
        }
    }

    // MARK: - Firebase Storage Upload

    private func uploadProfilePhoto(_ image: UIImage) async {
        guard let userId = AuthManager.shared.currentUserId else { return }
        guard let jpegData = image.jpegData(compressionQuality: 0.8) else { return }

        isUploadingPhoto = true
        defer { isUploadingPhoto = false }

        let boundary = "Boundary-\(UUID().uuidString)"
        guard let url = URL(string: "\(AuthManager.shared.baseURL)/me/upload-photo") else { return }
        
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        
        if let token = UserDefaults.standard.string(forKey: "auth_token") {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        var body = Data()
        body.append("--\(boundary)\r\n".data(using: .utf8)!)
        body.append("Content-Disposition: form-data; name=\"photo\"; filename=\"photo.jpg\"\r\n".data(using: .utf8)!)
        body.append("Content-Type: image/jpeg\r\n\r\n".data(using: .utf8)!)
        body.append(jpegData)
        body.append("\r\n".data(using: .utf8)!)
        body.append("--\(boundary)--\r\n".data(using: .utf8)!)
        
        request.httpBody = body

        do {
            let (data, response) = try await URLSession.shared.data(for: request)
            guard let httpResponse = response as? HTTPURLResponse, (200...299).contains(httpResponse.statusCode) else {
                print("Profil fotoğrafı yüklenemedi: Geçersiz HTTP response")
                return
            }

            struct PhotoUploadResponse: Codable {
                struct UploadData: Codable {
                    var profileImageUrl: String
                }
                var success: Bool
                var data: UploadData
            }

            let uploadResponse = try JSONDecoder().decode(PhotoUploadResponse.self, from: data)
            if uploadResponse.success {
                let downloadURL = uploadResponse.data.profileImageUrl
                
                // Lokal user modelini güncelle (ana menü senkronizasyonu için)
                if user != nil {
                    user?.profileImageUrl = downloadURL
                    user?.updatedAt = Date()
                }
                print("Profil fotoğrafı başarıyla yüklendi: \(downloadURL)")
            }
        } catch {
            print("Profil fotoğrafı yüklenemedi: \(error.localizedDescription)")
        }
    }

    // MARK: - Logout

    func signOut() {
        do {
            try AuthManager.shared.signOut()
        } catch {
            print("Çıkış yapılamadı: \(error.localizedDescription)")
        }
    }
}


// MARK: - UIImage Oryantasyon Düzeltme Extension

extension UIImage {
    /// EXIF oryantasyon bilgisini uygulayarak görseli her zaman dik (up) döndürür.
    /// Kamera ve galeri fotoğraflarında yaşanan 90° dönme sorununu giderir.
    func fixedOrientation() -> UIImage {
        guard imageOrientation != .up else { return self }

        var transform = CGAffineTransform.identity

        switch imageOrientation {
        case .down, .downMirrored:
            transform = transform.translatedBy(x: size.width, y: size.height)
            transform = transform.rotated(by: .pi)
        case .left, .leftMirrored:
            transform = transform.translatedBy(x: size.width, y: 0)
            transform = transform.rotated(by: .pi / 2)
        case .right, .rightMirrored:
            transform = transform.translatedBy(x: 0, y: size.height)
            transform = transform.rotated(by: -.pi / 2)
        default:
            break
        }

        switch imageOrientation {
        case .upMirrored, .downMirrored:
            transform = transform.translatedBy(x: size.width, y: 0)
            transform = transform.scaledBy(x: -1, y: 1)
        case .leftMirrored, .rightMirrored:
            transform = transform.translatedBy(x: size.height, y: 0)
            transform = transform.scaledBy(x: -1, y: 1)
        default:
            break
        }

        guard let cgImage = cgImage,
              let colorSpace = cgImage.colorSpace,
              let ctx = CGContext(
                  data: nil,
                  width: Int(size.width),
                  height: Int(size.height),
                  bitsPerComponent: cgImage.bitsPerComponent,
                  bytesPerRow: 0,
                  space: colorSpace,
                  bitmapInfo: cgImage.bitmapInfo.rawValue
              ) else {
            return self
        }

        ctx.concatenate(transform)

        switch imageOrientation {
        case .left, .leftMirrored, .right, .rightMirrored:
            ctx.draw(cgImage, in: CGRect(x: 0, y: 0, width: size.height, height: size.width))
        default:
            ctx.draw(cgImage, in: CGRect(x: 0, y: 0, width: size.width, height: size.height))
        }

        guard let resultCGImage = ctx.makeImage() else { return self }
        return UIImage(cgImage: resultCGImage)
    }
}

