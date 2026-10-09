//
//  ResetPasswordView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 1.10.2026.
//

import SwiftUI

struct ResetPasswordView: View {
    @ObservedObject var viewModel: AuthViewModel
    
    // To navigate back to login upon success
    @Environment(\.presentationMode) var presentationMode
    @Environment(\.dismiss) var dismiss
    
    var body: some View {
        ZStack {
            Color(UIColor.systemBackground).ignoresSafeArea()
            
            VStack(spacing: 25) {
                Spacer()
                
                Image(systemName: "checkmark.shield")
                    .font(.system(size: 60))
                    .foregroundColor(.yellow)
                    .padding(.bottom, 10)
                
                Text("Yeni Şifre Belirle")
                    .font(.title.bold())
                    .foregroundColor(.primary)
                
                Text("Lütfen hesabınız için yeni bir şifre belirleyin.")
                    .font(.subheadline)
                    .foregroundColor(.gray)
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 20)
                
                // NEW PASSWORD
                VStack(alignment: .leading, spacing: 8) {
                    Text("Yeni Şifre")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    HStack {
                        if viewModel.showForgotPasswordNewPassword {
                            TextField("Yeni Şifre", text: $viewModel.forgotPasswordNewPassword)
                        } else {
                            SecureField("Yeni Şifre", text: $viewModel.forgotPasswordNewPassword)
                        }
                        
                        Button {
                            viewModel.showForgotPasswordNewPassword.toggle()
                        } label: {
                            Image(systemName: viewModel.showForgotPasswordNewPassword ? "eye.slash" : "eye")
                                .foregroundColor(.gray)
                        }
                    }
                    .padding()
                    .background(Color(UIColor.secondarySystemBackground))
                    .cornerRadius(10)
                    .foregroundColor(.primary)
                    .textContentType(.newPassword)
                }
                
                // CONFIRM PASSWORD
                VStack(alignment: .leading, spacing: 8) {
                    Text("Yeni Şifre (Tekrar)")
                        .foregroundColor(.gray)
                        .font(.caption)
                    
                    HStack {
                        if viewModel.showForgotPasswordConfirmPassword {
                            TextField("Yeni Şifre (Tekrar)", text: $viewModel.forgotPasswordConfirmPassword)
                        } else {
                            SecureField("Yeni Şifre (Tekrar)", text: $viewModel.forgotPasswordConfirmPassword)
                        }
                        
                        Button {
                            viewModel.showForgotPasswordConfirmPassword.toggle()
                        } label: {
                            Image(systemName: viewModel.showForgotPasswordConfirmPassword ? "eye.slash" : "eye")
                                .foregroundColor(.gray)
                        }
                    }
                    .padding()
                    .background(Color(UIColor.secondarySystemBackground))
                    .cornerRadius(10)
                    .foregroundColor(.primary)
                    .textContentType(.newPassword)
                }
                
                // RESET BUTTON
                Button {
                    viewModel.resetForgotPassword()
                } label: {
                    if viewModel.isLoading {
                        ProgressView()
                            .tint(.black)
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .cornerRadius(12)
                    } else {
                        Text("Şifreyi Güncelle")
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
            }
            .padding(.horizontal, 30)
        }
        .onTapGesture { hideKeyboard() }
        .navigationBarTitleDisplayMode(.inline)
        // Only show success alert or error alert
        .alert(isPresented: .init(get: { viewModel.showAlert || viewModel.resetPasswordSuccess }, set: { val in
            if viewModel.resetPasswordSuccess && !val {
                // Return to login
                viewModel.navigateToForgotPassword = false
                viewModel.navigateToOtp = false
                viewModel.navigateToReset = false
                viewModel.resetPasswordSuccess = false
            }
            viewModel.showAlert = val
        })) {
            if viewModel.resetPasswordSuccess {
                return Alert(title: Text(viewModel.alertTitle), message: Text(viewModel.alertMessage), dismissButton: .default(Text("Giriş Yap")) {
                    // Navigate all the way back
                    viewModel.navigateToForgotPassword = false
                    viewModel.navigateToOtp = false
                    viewModel.navigateToReset = false
                    viewModel.resetPasswordSuccess = false
                    viewModel.forgotPasswordEmail = ""
                    viewModel.forgotPasswordOtp = ""
                    viewModel.forgotPasswordNewPassword = ""
                    viewModel.forgotPasswordConfirmPassword = ""
                })
            } else {
                return Alert(title: Text(viewModel.alertTitle), message: Text(viewModel.alertMessage), dismissButton: .cancel(Text("Tamam")))
            }
        }
    }
}
