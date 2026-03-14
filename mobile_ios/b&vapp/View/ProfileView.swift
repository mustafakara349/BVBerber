//
//  ProfileView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct ProfileView: View {
    
    @State private var showWelcome = false
    
    var body: some View {
                
        VStack(spacing: 30) {
                
            profileHeader
            
            Spacer()
            
            menuSection
                
            Spacer()
                
            logoutButton
        }
        .padding()
    }
}

extension ProfileView {
    
    var profileHeader: some View {
        
        VStack(spacing: 14) {
            
            ZStack(alignment: .bottomTrailing) {
                
                Image("icon")
                    .resizable()
                    .scaledToFill()
                    .frame(width: 120, height: 120)
                    .clipShape(Circle())
                    .overlay(
                        Circle()
                            .stroke(Color.yellow.opacity(0.7), lineWidth: 4)
                    )
                
                Button {
                    
                } label: {
                    Image(systemName: "camera.fill")
                        .foregroundColor(.black)
                        .padding(8)
                        .background(Color.yellow)
                        .clipShape(Circle())
                }
                .offset(x: 5, y: 5)
            }
            
            Text("Mustafa KARA")
                .foregroundColor(.white)
                .font(.title2)
                .fontWeight(.bold)
        }
    }
}

extension ProfileView {
    
    var menuSection: some View {
        
        VStack(spacing: 16) {
            
            ProfileMenuItem(
                icon: "lock.fill",
                title: "Güvenlik ve Giriş"
            )
            
            ProfileMenuItem(
                icon: "questionmark",
                title: "Destek ve Yardım"
            )
        }
    }
}

extension ProfileView {
    
    var logoutButton: some View {
        
        Button {
            do {
                try AuthManager.shared.signOut()
                showWelcome = true
            } catch {
                print("Çıkış yapılamadı:", error.localizedDescription)
            }
        } label: {
            
            Text("OTURUMU KAPAT")
                .foregroundColor(.red)
                .fontWeight(.bold)
                .frame(maxWidth: .infinity)
                .padding()
                .background(
                    RoundedRectangle(cornerRadius: 25)
                        .fill(Color.red.opacity(0.15))
                        .overlay(
                            RoundedRectangle(cornerRadius: 25)
                                .stroke(Color.red.opacity(0.4), lineWidth: 1)
                        )
                )
        }.fullScreenCover(isPresented: $showWelcome) {
            WelcomeView()
        }
    }
}

struct ProfileMenuItem: View {
    
    var icon: String
    var title: String
    
    var body: some View {
        
        HStack(spacing: 16) {
            
            ZStack {
                RoundedRectangle(cornerRadius: 12)
                    .fill(Color.yellow.opacity(0.2))
                    .frame(width: 44, height: 44)
                
                Image(systemName: icon)
                    .foregroundColor(.yellow)
            }
            
            Text(title)
                .foregroundColor(.white)
                .fontWeight(.medium)
            
            Spacer()
            
            Image(systemName: "chevron.right")
                .foregroundColor(.gray)
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 20)
                .fill(Color.white.opacity(0.05))
                .overlay(
                    RoundedRectangle(cornerRadius: 20)
                        .stroke(Color.white.opacity(0.05))
                )
        )
    }
}

#Preview {
    ProfileView()
}
