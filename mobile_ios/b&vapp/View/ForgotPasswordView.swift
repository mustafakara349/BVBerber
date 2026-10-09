//
//  ForgotPasswordView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 1.10.2026.
//

import SwiftUI

struct ForgotPasswordView: View {
    @ObservedObject var viewModel: AuthViewModel
    @Environment(\.dismiss) var dismiss
    
    var body: some View {
        ZStack {
            Color(UIColor.systemBackground).ignoresSafeArea()
            
            
            VStack(spacing: 25) {
                
                Spacer()
                
                Image(systemName: "lock.rotation")
                    .font(.system(size: 60))
                    .foregroundColor(.yellow)
                    .padding(.bottom, 10)
                
                Text("Şifremi Unuttum")
                    .font(.title.bold())
                    .foregroundColor(.primary)
                
                Text("Şifrenizi sıfırlamak için hesabınıza bağlı e-posta adresini girin. Size 6 haneli bir kod göndereceğiz.")
                    .font(.subheadline)
                    .foregroundColor(.gray)
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 20)
                
                // EMAIL INPUT
                VStack(alignment: .leading, spacing: 8) {
                    Text("Email")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    TextField("example@email.com", text: $viewModel.forgotPasswordEmail)
                        .padding()
                        .background(Color(UIColor.secondarySystemBackground))
                        .cornerRadius(10)
                        .foregroundColor(.primary)
                        .autocapitalization(.none)
                        .keyboardType(.emailAddress)
                        .textContentType(.emailAddress)
                }
                
                // SEND BUTTON
                Button {
                    viewModel.sendOtp()
                } label: {
                    if viewModel.isLoading {
                        ProgressView()
                            .tint(.black)
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .cornerRadius(12)
                    } else {
                        Text("Kod Gönder")
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
                
                NavigationLink(destination: OtpVerificationView(viewModel: viewModel), isActive: $viewModel.navigateToOtp) {
                    EmptyView()
                }
            }
            .padding(.horizontal, 30)
        }
        .onTapGesture { hideKeyboard() }
        .navigationBarTitleDisplayMode(.inline)
        // Ensure ViewModel uses the common alert state
        .alert(viewModel.alertTitle, isPresented: $viewModel.showAlert) {
            Button("Tamam", role: .cancel) { }
        } message: {
            Text(viewModel.alertMessage)
        }
    }
}
