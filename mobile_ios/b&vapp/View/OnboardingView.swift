//
//  OnboardingView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI

struct OnboardingView: View {

    @State private var currentPage = 0
    @AppStorage("hasCompletedOnboarding") var hasCompletedOnboarding: Bool = false

    var body: some View {
        ZStack(alignment: .topTrailing) {
            TabView(selection: $currentPage) {
                OnboardingPageView(
                    image: "barber1",
                    title: "Tarzını Keşfet",
                    description: "Uzman berberlerimizle sana en yakışan stili keşfet."
                )
                .tag(0)
                
                OnboardingPageView(
                    image: "barber2",
                    title: "Kişiye Özel Kesim",
                    description: "Yüz şekline ve stiline uygun profesyonel kesimlerle fark yarat."
                )
                .tag(1)
                                
                OnboardingPageView(
                    image: "barber3",
                    title: "Berberden Fazlası",
                    description: "Saçını yaptırırken kahveni iç, rahat bir atmosferde keyifli zaman geçir."
                )
                .tag(2)
                
                OnboardingPageView(
                    image: "barber4",
                    title: "Tarzını Şimdi Oluştur",
                    description: "Randevunu oluştur, bakım ürünlerini keşfet ve stilini bir üst seviyeye taşı.",
                    showButton: false,
                    logInAction: { hasCompletedOnboarding = true },
                    signUpAction: { hasCompletedOnboarding = true }
                )
                .tag(3)
            }
            .tabViewStyle(PageTabViewStyle(indexDisplayMode: currentPage == 3 ? .never : .always))
            .ignoresSafeArea()
        }
        .safeAreaInset(edge: .top) {
            HStack {
                Spacer()

                Button(currentPage == 3 ? "Giriş Yap / Kaydol" : "Atla") {
                    hasCompletedOnboarding = true
                }
                .font(.subheadline)
                .foregroundColor(.yellow)
                .padding(.horizontal, 16)
                .padding(.vertical, 8)
                .background(.ultraThinMaterial)
                .clipShape(Capsule())
                .padding(.trailing, 20)
            }
        }
    }
}

#Preview {
    OnboardingView()
}
