//
//  RegisterView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 6.03.2026.
//

import SwiftUI

struct RegisterView: View {
    
    @State private var name = ""
    @State private var email = ""
    @State private var password = ""
    @State private var confirmPassword = ""
    
    @State private var showPassword = false
    @State private var showConfirmPassword = false
    @State private var goToMain = false
    
    var body: some View {
        
        ZStack {
            
            Color.black
                .ignoresSafeArea()
            
            VStack(spacing: 22) {
                
                Spacer()
                
                // Logo
                Image("icon")
                    .resizable()
                    .frame(width: 100, height: 100)
                    .clipShape(Circle())
                
                Text("Hesap Oluştur")
                    .font(.title.bold())
                    .foregroundColor(.white)
                
                
                // AD SOYAD
                
                VStack(alignment: .leading, spacing: 8) {
                    
                    Text("Ad Soyad")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    TextField("Ad Soyad", text: $name)
                        .padding()
                        .background(Color.white.opacity(0.1))
                        .cornerRadius(10)
                        .foregroundColor(.white)
                }
                
                
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
                        .keyboardType(.emailAddress)
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
                
                
                // PASSWORD TEKRAR
                
                VStack(alignment: .leading, spacing: 8) {
                    
                    Text("Şifre Tekrar")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    HStack {
                        
                        if showConfirmPassword {
                            TextField("Şifre Tekrar", text: $confirmPassword)
                        } else {
                            SecureField("Şifre Tekrar", text: $confirmPassword)
                        }
                        
                        Button {
                            showConfirmPassword.toggle()
                        } label: {
                            Image(systemName: showConfirmPassword ? "eye.slash" : "eye")
                                .foregroundColor(.gray)
                        }
                    }
                    .padding()
                    .background(Color.white.opacity(0.1))
                    .cornerRadius(10)
                    .foregroundColor(.white)
                }
                
                
                // REGISTER BUTTON
                
                Button {
                    
                    // REGISTER ACTION
                    AuthManager.shared.signUp(
                        email: email,
                        password: password,
                        name: name
                    ) { result in
                        
                        switch result {
                            
                        case .success:
                            print("Kayıt başarılı")
                            
                            DispatchQueue.main.async {
                                goToMain = true
                            }
                            
                        case .failure(let error):
                            print(error.localizedDescription)
                        }
                    }
                    
                } label: {
                    
                    Text("Üye Ol")
                        .font(.headline)
                        .foregroundColor(.white)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.blue)
                        .cornerRadius(12)
                }
                .padding(.top, 10)
                
                
                Spacer()
                
                
                // LOGIN REDIRECT
                
                HStack {
                    Text("Zaten hesabın var mı?")
                        .foregroundColor(.gray)
                    
                    NavigationLink("Giriş Yap") {
                        LoginView()
                    }
                    .foregroundColor(.blue)
                }
                
                Spacer()
            }
            .padding(.horizontal, 30)
        }
        .navigationBarTitleDisplayMode(.inline)
        .navigationDestination(isPresented: $goToMain) {
            MainView()
        }
    }
}
#Preview {
    RegisterView()
}
