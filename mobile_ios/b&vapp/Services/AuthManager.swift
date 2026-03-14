//
//  AuthManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 10.03.2026.
//

import Foundation
import FirebaseAuth
import FirebaseFirestore
import Combine

class AuthManager: ObservableObject {

    static let shared = AuthManager()

    private let auth = Auth.auth()
    private let db = Firestore.firestore()

    @Published var currentUserId: String?

    private var authListener: AuthStateDidChangeListenerHandle?

    private init() {
        listenAuthState()
    }

    // MARK: - Auth State Listener

    private func listenAuthState() {
        authListener = auth.addStateDidChangeListener { _, user in
            DispatchQueue.main.async {
                self.currentUserId = user?.uid
            }
        }
    }

    // MARK: - Kullanıcı Kaydı

    func signUp(email: String,
                password: String,
                name: String,
                completion: @escaping (Result<String, Error>) -> Void) {

        auth.createUser(withEmail: email, password: password) { result, error in

            if let error = error {
                completion(.failure(error))
                return
            }

            guard let userId = result?.user.uid else { return }

            let userData: [String: Any] = [
                "uid": userId,
                "name": name,
                "email": email,
                "createdAt": Timestamp()
            ]

            self.db.collection("users").document(userId).setData(userData) { err in

                if let err = err {
                    completion(.failure(err))
                } else {
                    completion(.success(userId))
                }
            }
        }
    }

    // MARK: - Giriş Yapma

    func signIn(email: String,
                password: String,
                completion: @escaping (Result<String, Error>) -> Void) {

        auth.signIn(withEmail: email, password: password) { result, error in

            if let error = error {
                completion(.failure(error))
                return
            }

            guard let uid = result?.user.uid else { return }

            completion(.success(uid))
        }
    }

    // MARK: - Çıkış

    func signOut() throws {
        try auth.signOut()
    }
}
