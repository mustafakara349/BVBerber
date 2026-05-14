//
//  OnboardingViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI

struct OnboardingViewModel: View {
    
    var image : String
    var title : String
    var description : String
    var showButton: Bool = false
    var logInAction: (() -> Void)? = nil
    var signUpAction: (() -> Void)? = nil
    
    var body: some View {
        ZStack(alignment: .topTrailing) {
            Image(image)
                .resizable()
                .scaledToFill()
                .ignoresSafeArea()
                .frame(width: UIScreen.main.bounds.width, height: 880)
                .clipped()
            
            LinearGradient( gradient: Gradient(colors: [Color.white.opacity(1.2), Color.gray.opacity(0.0)]), startPoint: .bottom, endPoint: .center ) .ignoresSafeArea()
            
            
            VStack(spacing: 20) {
                Spacer()
                
                Text(title)
                    .font(.title)
                    .fontWeight(.bold)
                    .foregroundColor(.black)
                    .padding(.bottom, 5)
                
                Text(description)
                    .font(.body)
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 30)
                    .foregroundColor(.black)
                
                if showButton, let logInAction = logInAction, let signUpAction = signUpAction {
                    Button("Giriş Yap") {
                        logInAction()
                    }
                    .font(.headline)
                    .foregroundColor(.white)
                    .padding()
                    .background(Color.blue)
                    .cornerRadius(12)
                    .padding(.top, 20)
                    
                    HStack {
                        Text("Hesabın yok mu? Hemen")
                            .font(.subheadline)
                            .foregroundColor(.black)
                            .padding(0)
                        Button(action: {
                            signUpAction()
                        }) {
                            Text("kaydol.")
                                .font(.subheadline)
                                .foregroundColor(.blue)
                                .underline().padding(0)
                        }
                    }
                }
                Spacer().frame(height: 80)
            }
            .frame(maxWidth: .infinity)
            .padding(.bottom, 10)
        }
        
    }
}

#Preview {
    OnboardingViewModel(image: "barber1", title: "Title", description: "Description")
}

