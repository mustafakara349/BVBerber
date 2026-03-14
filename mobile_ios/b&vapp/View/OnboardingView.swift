//
//  OnboardingView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI

struct OnboardingView: View {

    @State private var currentPage = 0
    @State private var showLogIn: Bool = false
    @State private var showSignUp: Bool = false
    @State private var showMainView: Bool = false

    var body: some View {
        ZStack(alignment: .topTrailing) {
            if showMainView {
                MainView()
            } else if showLogIn {
                WelcomeView()
            } else {
                TabView(selection: $currentPage) {
                    OnboardingViewModel(
                        image: "barber1",
                        title: "Tarzını Keşfet",
                        description: "Uzman berberlerimizle sana en yakışan stili keşfet."
                    )
                    .tag(0)
                    
                    OnboardingViewModel(
                        image: "barber2",
                        title: "Kişiye Özel Kesim",
                        description: "Yüz şekline ve stiline uygun profesyonel kesimlerle fark yarat."
                    )
                    .tag(1)
                                    
                    OnboardingViewModel(
                        image: "barber3",
                        title: "Berberden Fazlası",
                        description: "Saçını yaptırırken kahveni iç, rahat bir atmosferde keyifli zaman geçir."
                    )
                    .tag(2)
                    
                    OnboardingViewModel(
                        image: "barber4",
                        title: "Tarzını Şimdi Oluştur",
                        description: "Randevunu oluştur, bakım ürünlerini keşfet ve stilini bir üst seviyeye taşı.",
                        showButton: false,
                        logInAction: { showLogIn = false },
                        signUpAction: { showSignUp = false }
                    )
                    .tag(3)
                }
                .tabViewStyle(PageTabViewStyle(indexDisplayMode: currentPage == 3 ? .never : .always))
                .ignoresSafeArea()
            }
        }
        .safeAreaInset(edge: .top) {
            if !showLogIn && !showMainView {
                HStack {
                    Spacer()

                    Button(currentPage == 3 ? "Giriş Yap / Kaydol" : "Atla") {
                        showLogIn = true
                    }
                    .font(.default  )
                    .foregroundColor(.yellow)
                    .padding(.horizontal, 16)
                    .padding(.vertical, 8)
                    .background(.ultraThinMaterial)
                    .clipShape(Capsule())
                    .padding(.trailing, 20)
                }
            }
        }
        .onAppear {
            //DispatchQueue.main.async {
              //  if Auth.auth().currentUser != nil {
                //    showMainView = true
               // }
            }
        }
        //.fullScreenCover(isPresented: $showSignUp) {
            
        //}
    }
//}
#Preview {
    OnboardingView()
}
