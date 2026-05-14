//
//  NotificationView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import SwiftUI

struct NotificationView: View {
    var body: some View {
        ZStack {
            
            Color.black.ignoresSafeArea()
            
            Text("Bildirimler")
                .foregroundColor(.white)
                .font(.title)
        }
    }
}

#Preview {
    NotificationView()
}
