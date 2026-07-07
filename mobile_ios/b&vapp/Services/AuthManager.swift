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

    // Base URL configuration - change this to production API URL if needed
    let baseURL = "http://192.168.0.2:8000/api/v1/mobile"

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

    // MARK: - Kullanıcı Kaydı

    func signUp(
        email: String,
        password: String,
        name: String,
        surname: String,
        phone: String,
        completion: @escaping (Result<String, Error>) -> Void
    ) {
        let body: [String: Any] = [
            "name": name,
            "surname": surname,
            "email": email,
            "phone": phone,
            "password": password
        ]

        sendPostRequest(path: "/register", body: body) { result in
            switch result {
            case .success(let json):
                guard let responseData = json["data"] as? [String: Any],
                      let user = responseData["user"] as? [String: Any],
                      let userId = user["id"] as? String,
                      let token = responseData["token"] as? String else {
                    completion(.failure(NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid registration response"])))
                    return
                }

                UserDefaults.standard.set(token, forKey: "auth_token")
                UserDefaults.standard.set(userId, forKey: "user_id")

                DispatchQueue.main.async {
                    self.currentUserId = userId
                    self.isNewlyRegistered = true
                }
                completion(.success(userId))

            case .failure(let error):
                completion(.failure(error))
            }
        }
    }

    // MARK: - Giriş Yapma

    func signIn(
        email: String,
        password: String,
        completion: @escaping (Result<String, Error>) -> Void
    ) {
        let body: [String: Any] = [
            "email": email,
            "password": password
        ]

        sendPostRequest(path: "/login", body: body) { result in
            switch result {
            case .success(let json):
                guard let responseData = json["data"] as? [String: Any],
                      let user = responseData["user"] as? [String: Any],
                      let userId = user["id"] as? String,
                      let token = responseData["token"] as? String else {
                    completion(.failure(NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid login response"])))
                    return
                }

                UserDefaults.standard.set(token, forKey: "auth_token")
                UserDefaults.standard.set(userId, forKey: "user_id")

                DispatchQueue.main.async {
                    self.currentUserId = userId
                }
                completion(.success(userId))

            case .failure(let error):
                completion(.failure(error))
            }
        }
    }

    // MARK: - Çıkış

    func signOut() throws {
        // Send async logout to server (optional, fire and forget)
        if let token = UserDefaults.standard.string(forKey: "auth_token"),
           let url = URL(string: "\(baseURL)/logout") {
            var request = URLRequest(url: url)
            request.httpMethod = "POST"
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
            request.setValue("application/json", forHTTPHeaderField: "Accept")
            URLSession.shared.dataTask(with: request).resume()
        }

        // Clean local state
        UserDefaults.standard.removeObject(forKey: "auth_token")
        UserDefaults.standard.removeObject(forKey: "user_id")

        DispatchQueue.main.async {
            self.currentUserId = nil
            self.isNewlyRegistered = false
        }
    }

    // MARK: - Kullanıcı Bilgisi Güncelleme

    func updateUserProfile(
        name: String,
        surname: String,
        phone: String,
        completion: @escaping (Result<Void, Error>) -> Void
    ) {
        let body: [String: Any] = [
            "name": name,
            "surname": surname,
            "phone": phone
        ]

        sendPostRequest(path: "/me/update", body: body) { result in
            switch result {
            case .success:
                completion(.success(()))
            case .failure(let error):
                completion(.failure(error))
            }
        }
    }

    // MARK: - Şifre Güncelleme

    func updatePassword(
        currentPassword: String,
        newPassword: String,
        completion: @escaping (Result<Void, Error>) -> Void
    ) {
        let body: [String: Any] = [
            "currentPassword": currentPassword,
            "newPassword": newPassword
        ]

        sendPostRequest(path: "/me/update-password", body: body) { result in
            switch result {
            case .success:
                completion(.success(()))
            case .failure(let error):
                completion(.failure(error))
            }
        }
    }
}
