//
//  RegisterView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 6.03.2026.
//

import SwiftUI

struct RegisterView: View {

    @StateObject private var viewModel = AuthViewModel()

    var body: some View {

        ZStack {

            Color.black.ignoresSafeArea()

            // ScrollView + SwiftUI klavye yönetimi
            // .ignoresSafeArea(.keyboard) KULLANILMADI — SwiftUI otomatik ScrollView'i
            // klavye yüksekliği kadar küçültür, odaklanan field görünür kalır.
            ScrollView(showsIndicators: false) {
                VStack(spacing: 20) {

                    // Logo üstünde dengeli boşluk
                    Spacer(minLength: 20)

                    // Logo
                    Image("icon")
                        .resizable()
                        .frame(width: 90, height: 90)
                        .clipShape(Circle())

                    Text("Hesap Oluştur")
                        .font(.title.bold())
                        .foregroundColor(.white)
                        .padding(.bottom, 4)

                    // AD
                    inputField(label: "Ad", placeholder: "Adınız",
                               text: $viewModel.registerName,
                               contentType: .givenName)

                    // SOYAD
                    inputField(label: "Soyad", placeholder: "Soyadınız",
                               text: $viewModel.registerSurname,
                               contentType: .familyName)

                    // EMAIL
                    inputField(label: "Email", placeholder: "example@email.com",
                               text: $viewModel.registerEmail,
                               contentType: .emailAddress,
                               keyboardType: .emailAddress,
                               autocapitalize: false)

                    // TELEFON — 05XX XXX XX XX maskesi
                    VStack(alignment: .leading, spacing: 6) {
                        Text("Telefon Numarası").foregroundColor(.gray).font(.caption)
                        TextField("05XX XXX XX XX", text: $viewModel.registerPhone)
                            .padding()
                            .background(Color.white.opacity(0.1))
                            .cornerRadius(10)
                            .foregroundColor(.white)
                            .textContentType(.telephoneNumber)
                            .keyboardType(.numberPad)
                            .onChange(of: viewModel.registerPhone) { newVal in
                                let masked = applyPhoneMask(newVal)
                                if masked != newVal {
                                    viewModel.registerPhone = masked
                                }
                            }
                            .onAppear {
                                if viewModel.registerPhone.isEmpty {
                                    viewModel.registerPhone = "05"
                                }
                            }
                    }

                    // ŞİFRE
                    passwordField(label: "Şifre",
                                  text: $viewModel.registerPassword,
                                  isVisible: $viewModel.showRegisterPassword,
                                  contentType: .newPassword)

                    // ŞİFRE TEKRAR
                    passwordField(label: "Şifre Tekrar",
                                  text: $viewModel.registerConfirmPassword,
                                  isVisible: $viewModel.showRegisterConfirmPassword,
                                  contentType: .newPassword)

                    // KAYIT BUTONU
                    Button {
                        viewModel.signUp()
                    } label: {
                        if viewModel.isLoading {
                            ProgressView()
                                .tint(.black)
                                .frame(maxWidth: .infinity)
                                .padding()
                                .background(Color.yellow)
                                .cornerRadius(12)
                        } else {
                            Text("Üye Ol")
                                .font(.headline)
                                .foregroundColor(.black)
                                .frame(maxWidth: .infinity)
                                .padding()
                                .background(Color.yellow)
                                .cornerRadius(12)
                        }
                    }
                    .disabled(viewModel.isLoading)
                    .padding(.top, 6)

                    // GİRİŞ YÖNLENDİRME
                    HStack {
                        Text("Zaten hesabın var mı?")
                            .foregroundColor(.gray)
                        NavigationLink("Giriş Yap") {
                            LoginView()
                        }
                        .foregroundColor(.yellow)
                    }
                    .padding(.top, 4)

                    Spacer(minLength: 40)
                }
                .padding(.horizontal, 28)
            }
            // NOT: .ignoresSafeArea(.keyboard) kasıtlı olarak ÇIKARILDI.
            // SwiftUI, ScrollView'in alt kenarını klavye belirince otomatik kısaltır.
        }
        .navigationBarTitleDisplayMode(.inline)
        .alert(viewModel.alertTitle, isPresented: $viewModel.showAlert) {
            Button("Tamam", role: .cancel) { }
        } message: {
            Text(viewModel.alertMessage)
        }
        // Routing: b_vappApp.swift içinde AuthManager.isNewlyRegistered üzerinden yapılıyor.
        // Bu nedenle burada fullScreenCover kullanılmıyor.
    }

    // MARK: - Telefon Maskesi: 05XX XXX XX XX

    private func applyPhoneMask(_ input: String) -> String {
        var digits = input.filter { $0.isNumber }

        // "05" prefix'ini her zaman zorla
        if digits.count < 2 {
            return "05"
        }
        if !digits.hasPrefix("05") {
            digits = "05" + String(digits.dropFirst(2))
        }

        // Türk GSM: 11 rakam (05XXXXXXXXX)
        digits = String(digits.prefix(11))

        // Boşluk ekle: 0-3 | boşluk | 4-6 | boşluk | 7-8 | boşluk | 9-10
        var result = ""
        for (i, c) in digits.enumerated() {
            if i == 4 || i == 7 || i == 9 {
                result.append(" ")
            }
            result.append(c)
        }
        return result
    }

    // MARK: - Helper Views

    @ViewBuilder
    private func inputField(
        label: String,
        placeholder: String,
        text: Binding<String>,
        contentType: UITextContentType,
        keyboardType: UIKeyboardType = .default,
        autocapitalize: Bool = true
    ) -> some View {
        VStack(alignment: .leading, spacing: 6) {
            Text(label).foregroundColor(.gray).font(.caption)
            TextField(placeholder, text: text)
                .padding()
                .background(Color.white.opacity(0.1))
                .cornerRadius(10)
                .foregroundColor(.white)
                .textContentType(contentType)
                .keyboardType(keyboardType)
                .autocapitalization(autocapitalize ? .words : .none)
        }
    }

    @ViewBuilder
    private func passwordField(
        label: String,
        text: Binding<String>,
        isVisible: Binding<Bool>,
        contentType: UITextContentType
    ) -> some View {
        VStack(alignment: .leading, spacing: 6) {
            Text(label).foregroundColor(.gray).font(.caption)
            HStack {
                if isVisible.wrappedValue {
                    TextField(label, text: text)
                } else {
                    SecureField(label, text: text)
                }
                Button {
                    isVisible.wrappedValue.toggle()
                } label: {
                    Image(systemName: isVisible.wrappedValue ? "eye.slash" : "eye")
                        .foregroundColor(.gray)
                }
            }
            .padding()
            .background(Color.white.opacity(0.1))
            .cornerRadius(10)
            .foregroundColor(.white)
            .textContentType(contentType)
        }
    }
}

#Preview {
    RegisterView()
}


