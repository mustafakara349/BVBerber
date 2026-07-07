//
//  SecuritySettingsView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 28.03.2026.
//

import SwiftUI

struct SecuritySettingsView: View {
    
    @Environment(\.dismiss) var dismiss
    @StateObject private var viewModel = AuthViewModel()
    
    var body: some View {
        
        VStack(spacing: 0) {
            
            // HEADER
            HStack {
                
                Button {
                    dismiss()
                } label: {
                    Image(systemName: "chevron.left")
                        .font(.title3)
                        .foregroundColor(.primary)
                }
                
                Spacer()
                
                Text("Güvenlik ve Giriş")
                    .font(.headline)
                    .fontWeight(.semibold)
                
                Spacer()
                
                Circle()
                    .fill(Color.clear)
                    .frame(width: 24)
            }
            .padding()
            
            
            ScrollView {
                
                VStack(spacing: 24) {
                    
                    // INFO CARD
                    HStack(spacing: 14) {
                        
                        ZStack {
                            RoundedRectangle(cornerRadius: 12)
                                .fill(Color.yellow.opacity(0.15))
                                .frame(width: 44, height: 44)
                            
                            Image(systemName: "lock.shield.fill")
                                .foregroundColor(.yellow)
                        }
                        
                        VStack(alignment: .leading, spacing: 4) {
                            Text("Şifre Değiştir")
                                .font(.headline)
                                .foregroundColor(.primary)
                            
                            Text("Hesabınızı güvende tutmak için şifrenizi düzenli olarak değiştirin.")
                                .font(.caption)
                                .foregroundColor(.secondary)
                        }
                    }
                    .padding()
                    .frame(maxWidth: .infinity, alignment: .leading)
                    .background(
                        RoundedRectangle(cornerRadius: 16)
                            .fill(Color(.systemGray6))
                    )
                    
                    
                    // MEVCUT ŞİFRE
                    VStack(alignment: .leading, spacing: 8) {
                        
                        Text("Mevcut Şifre")
                            .foregroundColor(.secondary)
                            .font(.caption)
                        
                        HStack {
                            if viewModel.showCurrentPassword {
                                TextField("Mevcut şifrenizi girin", text: $viewModel.currentPassword)
                            } else {
                                SecureField("Mevcut şifrenizi girin", text: $viewModel.currentPassword)
                            }
                            
                            Button {
                                viewModel.showCurrentPassword.toggle()
                            } label: {
                                Image(systemName: viewModel.showCurrentPassword ? "eye.slash" : "eye")
                                    .foregroundColor(.secondary)
                            }
                        }
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(12)
                        .foregroundColor(.primary)
                    }
                    
                    
                    // YENİ ŞİFRE
                    VStack(alignment: .leading, spacing: 8) {
                        
                        Text("Yeni Şifre")
                            .foregroundColor(.secondary)
                            .font(.caption)
                        
                        HStack {
                            if viewModel.showNewPassword {
                                TextField("Yeni şifrenizi girin", text: $viewModel.newPassword)
                            } else {
                                SecureField("Yeni şifrenizi girin", text: $viewModel.newPassword)
                            }
                            
                            Button {
                                viewModel.showNewPassword.toggle()
                            } label: {
                                Image(systemName: viewModel.showNewPassword ? "eye.slash" : "eye")
                                    .foregroundColor(.secondary)
                            }
                        }
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(12)
                        .foregroundColor(.primary)
                    }
                    
                    
                    // YENİ ŞİFRE TEKRAR
                    VStack(alignment: .leading, spacing: 8) {
                        
                        Text("Yeni Şifre Tekrar")
                            .foregroundColor(.secondary)
                            .font(.caption)
                        
                        HStack {
                            if viewModel.showConfirmPassword {
                                TextField("Yeni şifrenizi tekrar girin", text: $viewModel.confirmNewPassword)
                            } else {
                                SecureField("Yeni şifrenizi tekrar girin", text: $viewModel.confirmNewPassword)
                            }
                            
                            Button {
                                viewModel.showConfirmPassword.toggle()
                            } label: {
                                Image(systemName: viewModel.showConfirmPassword ? "eye.slash" : "eye")
                                    .foregroundColor(.secondary)
                            }
                        }
                        .padding()
                        .background(Color(.systemGray6))
                        .cornerRadius(12)
                        .foregroundColor(.primary)
                    }
                    
                    
                    // ŞİFRE GEREKSİNİMLERİ
                    VStack(alignment: .leading, spacing: 6) {
                        
                        Text("Şifre Gereksinimleri")
                            .font(.caption)
                            .foregroundColor(.secondary)
                            .fontWeight(.medium)
                        
                        requirementRow(met: viewModel.newPassword.count >= 6, text: "En az 6 karakter")
                        requirementRow(met: viewModel.newPassword == viewModel.confirmNewPassword && !viewModel.newPassword.isEmpty, text: "Şifreler eşleşiyor")
                    }
                    .padding()
                    .frame(maxWidth: .infinity, alignment: .leading)
                    .background(
                        RoundedRectangle(cornerRadius: 12)
                            .fill(Color(.systemGray6))
                    )
                    
                }
                .padding()
            }
            
            
            // KAYDET BUTONU
            Button {
                viewModel.changePassword()
            } label: {
                
                if viewModel.isLoading {
                    ProgressView()
                        .tint(.black)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.yellow)
                        .cornerRadius(16)
                } else {
                    Text("Şifreyi Güncelle")
                        .font(.title3)
                        .fontWeight(.bold)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.yellow)
                        .foregroundColor(.black)
                        .cornerRadius(16)
                }
            }
            .disabled(viewModel.isLoading)
            .padding()
        }
        .background(Color(.systemBackground).ignoresSafeArea())
        .navigationBarBackButtonHidden(true)
        .toolbar(.hidden, for: .tabBar)
        .alert(viewModel.alertTitle, isPresented: $viewModel.showAlert) {
            Button("Tamam", role: .cancel) { }
        } message: {
            Text(viewModel.alertMessage)
        }
    }
    
    
    // MARK: - Helpers
    
    private func requirementRow(met: Bool, text: String) -> some View {
        HStack(spacing: 8) {
            Image(systemName: met ? "checkmark.circle.fill" : "circle")
                .foregroundColor(met ? .green : .secondary)
                .font(.caption)
            
            Text(text)
                .font(.caption)
                .foregroundColor(met ? .primary : .secondary)
        }
    }
}

#Preview {
    NavigationStack {
        SecuritySettingsView()
    }
}
