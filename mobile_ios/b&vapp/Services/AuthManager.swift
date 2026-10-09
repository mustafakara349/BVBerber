//
//  AuthManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 10.03.2026.
//

import Foundation
import Combine

class AuthManager: ObservableObject {

    static let shared = AuthManager()

    // Base URL configuration
    var baseURL: String { AppConfig.apiBaseURL }

    @Published var currentUserId: String?
    @Published var isCheckingAuth: Bool = true
    /// Kayıt sonrası onboarding için — ProfilePhotoOnboardingView gösterilince false yapılır
    @Published var isNewlyRegistered: Bool = false

    private init() {
        restoreSession()
    }

    // MARK: - Session Recovery

    private func restoreSession() {
        DispatchQueue.main.async {
            if let token = UserDefaults.standard.string(forKey: "auth_token"),
               let userId = UserDefaults.standard.string(forKey: "user_id") {
                self.currentUserId = userId
            } else {
                self.currentUserId = nil
            }
            self.isCheckingAuth = false
        }
    }

    // MARK: - API Helpers

    private func sendPostRequest(
        path: String,
        body: [String: Any],
        completion: @escaping (Result<[String: Any], Error>) -> Void
    ) {
        guard let url = URL(string: "\(baseURL)\(path)") else {
            completion(.failure(NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid URL"])))
            return
        }

        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")

        if let token = UserDefaults.standard.string(forKey: "auth_token") {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        do {
            request.httpBody = try JSONSerialization.data(withJSONObject: body)
        } catch {
            completion(.failure(error))
            return
        }

        URLSession.shared.dataTask(with: request) { data, response, error in
            if let error = error {
                completion(.failure(error))
                return
            }

            guard let data = data else {
                completion(.failure(NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "No data received"])))
                return
            }

            do {
                if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
                    let success = json["success"] as? Bool ?? false
                    if success {
                        completion(.success(json))
                    } else {
                        let message = json["message"] as? String ?? "Sunucu hatası."
                        completion(.failure(NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: message])))
                    }
                } else {
                    completion(.failure(NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Malformed response"])))
                }
            } catch {
                completion(.failure(error))
            }
        }.resume()
    }

    private func sendPostRequestAsync(path: String, body: [String: Any]) async throws -> [String: Any] {
        guard let url = URL(string: "\(baseURL)\(path)") else {
            throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid URL"])
        }
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")
        if let token = UserDefaults.standard.string(forKey: "auth_token") {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }
        request.httpBody = try JSONSerialization.data(withJSONObject: body)
        
        let (data, _) = try await URLSession.shared.data(for: request)
        if let json = try JSONSerialization.jsonObject(with: data) as? [String: Any] {
            let success = json["success"] as? Bool ?? false
            if success {
                return json
            } else {
                let message = json["message"] as? String ?? "Sunucu hatası."
                throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: message])
            }
        } else {
            throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Malformed response"])
        }
    }

    // MARK: - Kullanıcı Kaydı

    func signUp(
        email: String,
        password: String,
        name: String,
        surname: String,
        phone: String
    ) async throws -> String {
        let body: [String: Any] = [
            "name": name,
            "surname": surname,
            "email": email,
            "phone": phone,
            "password": password
        ]

        let json = try await sendPostRequestAsync(path: "/register", body: body)
        guard let responseData = json["data"] as? [String: Any],
              let user = responseData["user"] as? [String: Any],
              let userId = user["id"] as? String,
              let token = responseData["token"] as? String else {
            throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid registration response"])
        }

        UserDefaults.standard.set(token, forKey: "auth_token")
        UserDefaults.standard.set(userId, forKey: "user_id")

        await MainActor.run {
            self.currentUserId = userId
            self.isNewlyRegistered = true
        }
        return userId
    }

    // MARK: - Giriş Yapma

    func signIn(
        email: String,
        password: String
    ) async throws -> String {
        let body: [String: Any] = [
            "email": email,
            "password": password
        ]

        let json = try await sendPostRequestAsync(path: "/login", body: body)
        guard let responseData = json["data"] as? [String: Any],
              let user = responseData["user"] as? [String: Any],
              let userId = user["id"] as? String,
              let token = responseData["token"] as? String else {
            throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid login response"])
        }

        UserDefaults.standard.set(token, forKey: "auth_token")
        UserDefaults.standard.set(userId, forKey: "user_id")

        await MainActor.run {
            self.currentUserId = userId
        }
        return userId
    }

    // MARK: - Çıkış

    func signOut() async {
        // Send async logout to server (optional, fire and forget)
        if let token = UserDefaults.standard.string(forKey: "auth_token"),
           let url = URL(string: "\(baseURL)/logout") {
            var request = URLRequest(url: url)
            request.httpMethod = "POST"
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
            request.setValue("application/json", forHTTPHeaderField: "Accept")
            _ = try? await URLSession.shared.data(for: request)
        }

        // Clean local state
        UserDefaults.standard.removeObject(forKey: "auth_token")
        UserDefaults.standard.removeObject(forKey: "user_id")

        await MainActor.run {
            self.currentUserId = nil
            self.isNewlyRegistered = false
        }
    }

    // MARK: - Kullanıcı Bilgisi Güncelleme

    func updateUserProfile(
        name: String,
        surname: String,
        phone: String
    ) async throws {
        let body: [String: Any] = [
            "name": name,
            "surname": surname,
            "phone": phone
        ]

        _ = try await sendPostRequestAsync(path: "/me/update", body: body)
    }

    // MARK: - Şifre Güncelleme

    func updatePassword(
        currentPassword: String,
        newPassword: String
    ) async throws {
        let body: [String: Any] = [
            "currentPassword": currentPassword,
            "newPassword": newPassword
        ]

        _ = try await sendPostRequestAsync(path: "/me/update-password", body: body)
    }

    // MARK: - Şifremi Unuttum İşlemleri

    func forgotPassword(email: String) async throws {
        let body: [String: Any] = ["email": email]
        _ = try await sendPostRequestAsync(path: "/forgot-password", body: body)
    }

    func verifyOtp(email: String, otp: String) async throws {
        let body: [String: Any] = ["email": email, "otp": otp]
        _ = try await sendPostRequestAsync(path: "/verify-otp", body: body)
    }

    func resetPassword(email: String, otp: String, password: String, passwordConfirmation: String) async throws {
        let body: [String: Any] = [
            "email": email,
            "otp": otp,
            "password": password,
            "password_confirmation": passwordConfirmation
        ]
        _ = try await sendPostRequestAsync(path: "/reset-password", body: body)
    }
}
