//
//  LogInView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI

struct WelcomeView: View {
    
    var body: some View {
        NavigationStack {
            ZStack {
                
                // Background Image
                Image("barber1")
                    .resizable()
                    .scaledToFill()
                    .ignoresSafeArea()
                    .opacity(0.8)
                
                // Dark overlay
                Color.black.opacity(0.7)
                    .ignoresSafeArea()
                
                VStack {
                    
                    Spacer()
                    
                    // Icon
                    Image("icon")
                        .resizable()
                        .frame(width: 120, height: 120)
                        .clipShape(Circle())
                    
                    Text("B&V COFFE BERBER")
                        .font(.system(size: 34, weight: .bold))
                        .foregroundColor(.white)
                        .padding(.top, 20)
                    
                    Text("Kolay randevu seçeneği ile üye olmadan ve giriş yapmadan randevu alabilirsiniz.")
                        .font(.system(size: 16))
                        .foregroundColor(.white.opacity(0.8))
                        .multilineTextAlignment(.center)
                        .padding(.horizontal, 40)
                        .padding(.top, 8)
                    
                    Spacer()
                    
                    HStack(spacing: 20) {
                        FeatureButton(
                            icon: "calendar",
                            text: "KOLAY RANDEVU"
                        )
                    }
                    
                    // LOGIN BUTTON
                    
                    NavigationLink {
                        LoginView()
                    } label: {
                        Text("Giriş Yap")
                            .font(.system(size: 18, weight: .semibold))
                            .foregroundColor(.white)
                            .frame(width: UIScreen.main.bounds.width * 0.8)
                            .padding()
                            .background(Color.blue)
                            .cornerRadius(30)
                    }
                    .padding(.top, 30)
                    
                    
                    // REGISTER BUTTON
                    
                    NavigationLink {
                        RegisterView()
                    } label: {
                        Text("Üye Ol")
                            .font(.system(size: 18, weight: .medium))
                            .foregroundColor(.white)
                            .frame(width: UIScreen.main.bounds.width * 0.8)
                            .padding()
                            .background(Color.gray.opacity(0.3))
                            .cornerRadius(30)
                    }
                    .padding(.top, 10)
                    
                    
                    // TERMS
                    
                    Text("Devam ederek \(Text("Kullanım Koşullarını").foregroundColor(.blue)) kabul etmiş olursunuz.")
                        .font(.footnote)
                        .foregroundColor(.white.opacity(0.6))
                        .multilineTextAlignment(.center)
                        .padding(.top, 15)
                    
                    Spacer()
                }
                .padding(.horizontal, 30)
            }
        }
    }
}

struct FeatureButton: View {
    
    var icon: String
    var text: String
    
    var body: some View {
        
        HStack {
            Image(systemName: icon)
                .foregroundColor(.blue)
            
            Text(text)
                .font(.system(size: 14, weight: .medium))
                .foregroundColor(.white)
        }
        .padding(.vertical, 14)
        .padding(.horizontal, 22)
        .background(Color.white.opacity(0.08))
        .cornerRadius(30)
    }
}

#Preview {
    WelcomeView()
}
