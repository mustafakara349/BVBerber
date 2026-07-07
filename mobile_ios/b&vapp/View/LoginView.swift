//
//  LoginView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 6.03.2026.
//

import SwiftUI

struct LoginView: View {
    
    @StateObject private var viewModel = AuthViewModel()
    
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
                    
                    TextField("example@email.com", text: $viewModel.loginEmail)
                        .padding()
                        .background(Color.white.opacity(0.1))
                        .cornerRadius(10)
                        .foregroundColor(.white)
                        .autocapitalization(.none)
                        .keyboardType(.emailAddress)
                        .textContentType(.emailAddress)
                }
                
                
                // PASSWORD
                
                VStack(alignment: .leading, spacing: 8) {
                    
                    Text("Şifre")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    HStack {
                        
                        if viewModel.showLoginPassword {
                            TextField("Şifre", text: $viewModel.loginPassword)
                        } else {
                            SecureField("Şifre", text: $viewModel.loginPassword)
                        }
                        
                        Button {
                            viewModel.showLoginPassword.toggle()
                        } label: {
                            Image(systemName: viewModel.showLoginPassword ? "eye.slash" : "eye")
                                .foregroundColor(.gray)
                        }
                    }
                    .padding()
                    .background(Color.white.opacity(0.1))
                    .cornerRadius(10)
                    .foregroundColor(.white)
                    .textContentType(.password)
                }
                
                
                // Forgot Password
                
                HStack {
                    Spacer()
                    
                    Button("Şifremi Unuttum") {
                        
                    }
                    .font(.footnote)
                    .foregroundColor(.yellow)
                }
                
                
                // LOGIN BUTTON
                
                Button {
                    viewModel.signIn()
                } label: {
                    
                    if viewModel.isLoading {
                        ProgressView()
                            .tint(.black)
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .cornerRadius(12)
                    } else {
                        Text("Giriş Yap")
                            .font(.headline)
                            .foregroundColor(.black)
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .cornerRadius(12)
                    }
                }
                .disabled(viewModel.isLoading)
                .padding(.top, 10)
                
                
                Spacer()
                
                
                // Register
                
                HStack {
                    Text("Hesabın yok mu?")
                        .foregroundColor(.gray)
                    
                    NavigationLink("Üye Ol") {
                        RegisterView()
                    }
                    .foregroundColor(.yellow)
                }
                
                Spacer()
            }
            .padding(.horizontal, 30)
        }
        .navigationBarTitleDisplayMode(.inline)
        .alert(viewModel.alertTitle, isPresented: $viewModel.showAlert) {
            Button("Tamam", role: .cancel) { }
        } message: {
            Text(viewModel.alertMessage)
        }
    }
}

#Preview {
    LoginView()
}
