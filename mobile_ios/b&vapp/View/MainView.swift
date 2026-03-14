//
//  MainView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 5.03.2026.
//

import SwiftUI

struct MainView: View {
    
    @State private var selectedTab: Tab = .home
    
    var body: some View {
        
        TabView(selection: $selectedTab) {
            
            MainContentView(selectedTab: $selectedTab)
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
    }
}


#Preview {
    MainView()
}
