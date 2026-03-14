//
//  LoginView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 6.03.2026.
//

import SwiftUI

struct LoginView: View {
    
    @State private var email = ""
    @State private var password = ""
    @State private var showPassword = false
    @State private var goToMain = false
    
    var body: some View {
        
        ZStack {
            
            Color.black
                .ignoresSafeArea()
            
            VStack(spacing: 25) {
                
                Spacer()
                
                // Logo
                Image("icon")
                    .resizable()
                    .frame(width: 100, height: 100)
                    .clipShape(Circle())
                
                Text("Hesabına Giriş Yap")
                    .font(.title.bold())
                    .foregroundColor(.white)
                
                
                // EMAIL
                
                VStack(alignment: .leading, spacing: 8) {
                    
                    Text("Email")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    TextField("example@email.com", text: $email)
                        .padding()
                        .background(Color.white.opacity(0.1))
                        .cornerRadius(10)
                        .foregroundColor(.white)
                        .autocapitalization(.none)
                }
                
                
                // PASSWORD
                
                VStack(alignment: .leading, spacing: 8) {
                    
                    Text("Şifre")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    HStack {
                        
                        if showPassword {
                            TextField("Şifre", text: $password)
                        } else {
                            SecureField("Şifre", text: $password)
                        }
                        
                        Button {
                            showPassword.toggle()
                        } label: {
                            Image(systemName: showPassword ? "eye.slash" : "eye")
                                .foregroundColor(.gray)
                        }
                    }
                    .padding()
                    .background(Color.white.opacity(0.1))
                    .cornerRadius(10)
                    .foregroundColor(.white)
                }
                
                
                // Forgot Password
                
                HStack {
                    Spacer()
                    
                    Button("Şifremi Unuttum") {
                        
                    }
                    .font(.footnote)
                    .foregroundColor(.blue)
                }
                
                
                // LOGIN BUTTON
                
                Button {
                    
                    // LOGIN ACTION
                    AuthManager.shared.signIn(email: email, password: password) { result in

                        switch result {

                        case .success(let uid):
                            print("Giriş başarılı: \(uid)")

                            DispatchQueue.main.async {
                                goToMain = true
                            }

                        case .failure(let error):
                            print("Hata: \(error.localizedDescription)")
                        }
                    }
                    
                } label: {
                    
                    Text("Giriş Yap")
                        .font(.headline)
                        .foregroundColor(.white)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.blue)
                        .cornerRadius(12)
                }
                .padding(.top, 10)
                
                
                Spacer()
                
                
                // Register
                
                HStack {
                    Text("Hesabın yok mu?")
                        .foregroundColor(.gray)
                    
                    NavigationLink("Üye Ol") {
                        RegisterView()
                    }
                    .foregroundColor(.blue)
                }
                
                Spacer()
            }
            .padding(.horizontal, 30)
        }
        .navigationBarTitleDisplayMode(.inline)
        
    }
}

#Preview {
    LoginView()
}
