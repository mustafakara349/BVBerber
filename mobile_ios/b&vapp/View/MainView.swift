//
//  MainView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI

struct MainView: View {
    
    @State private var selectedTab: Tab = .home
    
    // ViewModels for the entire Main app flow. Preserved in memory.
    @StateObject private var homeVM = HomeViewModel()
    @StateObject private var apptVM = AppointmentsViewModel()
    @StateObject private var servicesVM = ServicesViewModel()
    @StateObject private var profileVM = ProfileViewModel()
    @StateObject private var notifyVM = NotificationViewModel()
    
    var body: some View {
        
        TabView(selection: $selectedTab) {
            
            NavigationStack {
                MainContentView(selectedTab: $selectedTab)
            }
                .tag(Tab.home)
                .tabItem {
                    Image(systemName: "house")
                    Text("Ana Sayfa")
                }
            
            AppointmentsView()
                .tag(Tab.appointments)
                .tabItem {
                    Image(systemName: "calendar")
                    Text("Randevular")
                }
            
            ServicesView()
                .tag(Tab.services)
                .tabItem {
                    Image(systemName: "scissors")
                    Text("Hizmetler")
                }
            
            ProfileView()
                .tag(Tab.profile)
                .tabItem {
                    Image(systemName: "person.circle")
                    Text("Profil")
                }
        }
        .accentColor(.yellow)
        .environmentObject(homeVM)
        .environmentObject(apptVM)
        .environmentObject(servicesVM)
        .environmentObject(profileVM)
        .environmentObject(notifyVM)
        .task {
            // Uygulama ilk açıldığında arka planda diğer tüm sekmelerin verilerini yükle
            // Böylece sekme değiştirildiğinde bekleme/kasma hissedilmez (veriler anında görünür).
            async let t1: () = homeVM.fetchHomeData()
            async let t2: () = apptVM.fetchAppointments()
            async let t3: () = servicesVM.fetchServices()
            async let t4: () = profileVM.fetchProfile()
            async let t5: () = notifyVM.fetchNotifications()
            
            _ = await (t1, t2, t3, t4, t5)
        }
        .onReceive(NotificationCenter.default.publisher(for: .navigateToNotifications)) { _ in
            selectedTab = .home
        }
    }
}


#Preview {
    MainView()
}
