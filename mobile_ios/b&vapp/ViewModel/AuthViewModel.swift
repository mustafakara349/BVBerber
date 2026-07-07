//
//  AuthViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import Combine

class AuthViewModel: ObservableObject {

    // MARK: - Login State
    @Published var loginEmail = ""
    @Published var loginPassword = ""
    @Published var showLoginPassword = false

    // MARK: - Register State
    @Published var registerName = ""
    @Published var registerSurname = ""
    @Published var registerEmail = ""
    @Published var registerPhone = ""
    @Published var registerPassword = ""
    @Published var registerConfirmPassword = ""
    @Published var showRegisterPassword = false
    @Published var showRegisterConfirmPassword = false

    // Kayıt sonrası onboarding navigasyonu için
    @Published var signUpSucceeded = false

    // MARK: - Security State
    @Published var currentPassword = ""
    @Published var newPassword = ""
    @Published var confirmNewPassword = ""
    @Published var showCurrentPassword = false
    @Published var showNewPassword = false
    @Published var showConfirmPassword = false

    // MARK: - UI State
    @Published var isLoading = false
    @Published var alertTitle = ""
    @Published var alertMessage = ""
    @Published var showAlert = false


    // MARK: - Login

    func signIn() {
        guard !loginEmail.isEmpty, !loginPassword.isEmpty else {
            showError(title: "Hata", message: "Email ve şifre alanları boş bırakılamaz.")
            return
        }

        isLoading = true

        AuthManager.shared.signIn(email: loginEmail, password: loginPassword) { [weak self] result in
            DispatchQueue.main.async {
                self?.isLoading = false
                if case .failure(let error) = result {
                    self?.showError(title: "Giriş Hatası", message: error.localizedDescription)
                }
            }
        }
    }


    // MARK: - Register

    func signUp() {
        guard !registerName.isEmpty else {
            showError(title: "Hata", message: "Ad alanını doldurun.")
            return
        }

        guard !registerSurname.isEmpty else {
            showError(title: "Hata", message: "Soyad alanını doldurun.")
            return
        }

        guard !registerEmail.isEmpty else {
            showError(title: "Hata", message: "Email alanını doldurun.")
            return
        }

        guard registerPassword.count >= 6 else {
            showError(title: "Hata", message: "Şifre en az 6 karakter olmalıdır.")
            return
        }

        guard registerPassword == registerConfirmPassword else {
            showError(title: "Hata", message: "Şifreler eşleşmiyor.")
            return
        }

        // Telefon format doğrulaması (opsiyonel ama varsa en az 10 hane)
        let cleanPhone = registerPhone.filter { $0.isNumber }
        if !cleanPhone.isEmpty && cleanPhone.count < 10 {
            showError(title: "Hata", message: "Lütfen geçerli bir telefon numarası girin.")
            return
        }

        isLoading = true

        AuthManager.shared.signUp(
            email: registerEmail,
            password: registerPassword,
            name: registerName,
            surname: registerSurname,
            phone: cleanPhone
        ) { [weak self] result in
            DispatchQueue.main.async {
                self?.isLoading = false
                switch result {
                case .success:
                    // Onboarding: profil fotoğrafı ekranına yönlendir
                    self?.signUpSucceeded = true
                case .failure(let error):
                    self?.showError(title: "Kayıt Hatası", message: error.localizedDescription)
                }
            }
        }
    }


    // MARK: - Change Password

    func changePassword() {
        guard !currentPassword.isEmpty else {
            showError(title: "Hata", message: "Mevcut şifrenizi girmeniz gerekiyor.")
            return
        }

        guard newPassword.count >= 6 else {
            showError(title: "Hata", message: "Yeni şifre en az 6 karakter olmalıdır.")
            return
        }

        guard newPassword == confirmNewPassword else {
            showError(title: "Hata", message: "Yeni şifreler eşleşmiyor.")
            return
        }

        isLoading = true

        AuthManager.shared.updatePassword(
            currentPassword: currentPassword,
            newPassword: newPassword
        ) { [weak self] result in
            DispatchQueue.main.async {
                self?.isLoading = false
                switch result {
                case .success:
                    self?.showError(title: "Başarılı", message: "Şifreniz başarıyla güncellendi.")
                    self?.currentPassword = ""
                    self?.newPassword = ""
                    self?.confirmNewPassword = ""
                case .failure(let error):
                    self?.showError(title: "Hata", message: error.localizedDescription)
                }
            }
        }
    }


    // MARK: - Logout

    func signOut() {
        do {
            try AuthManager.shared.signOut()
        } catch {
            showError(title: "Hata", message: "Çıkış yapılamadı: \(error.localizedDescription)")
        }
    }


    // MARK: - Helpers

    private func showError(title: String, message: String) {
        alertTitle = title
        alertMessage = message
        showAlert = true
    }
}
