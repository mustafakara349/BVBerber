//
//  NotificationView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import SwiftUI

struct NotificationView: View {
    
    @Environment(\.dismiss) var dismiss
    @EnvironmentObject var viewModel: NotificationViewModel
    
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
                
                Text("Bildirimler")
                    .font(.headline)
                    .fontWeight(.semibold)
                
                Spacer()
                
                Circle()
                    .fill(Color.clear)
                    .frame(width: 24)
            }
            .padding()
            
            
            if viewModel.notifications.isEmpty {
                
                Spacer()
                
                VStack(spacing: 16) {
                    Image(systemName: "bell.slash")
                        .font(.system(size: 50))
                        .foregroundColor(.secondary)
                    
                    Text("Henüz bildiriminiz yok")
                        .font(.subheadline)
                        .foregroundColor(.secondary)
                }
                
                Spacer()
                
            } else {
                
                ScrollView {
                    
                    VStack(spacing: 14) {
                        
                        ForEach(viewModel.notifications) { notification in
                            NotificationCard(notification: notification)
                        }
                    }
                    .padding()
                }
            }
        }
        .background(Color(.systemBackground).ignoresSafeArea())
        .navigationBarBackButtonHidden(true)
        .toolbar(.hidden, for: .tabBar)
        .task {
            await viewModel.fetchNotifications()
        }
    }
}


struct NotificationCard: View {
    
    let notification: NotificationItem
    
    var body: some View {
        
        HStack(alignment: .top, spacing: 14) {
            
            ZStack {
                RoundedRectangle(cornerRadius: 12)
                    .fill(Color.yellow.opacity(0.15))
                    .frame(width: 44, height: 44)
                
                Image(systemName: notification.icon)
                    .foregroundColor(.yellow)
                    .font(.body)
            }
            
            VStack(alignment: .leading, spacing: 6) {
                
                Text(notification.title)
                    .font(.subheadline)
                    .fontWeight(.semibold)
                    .foregroundColor(.primary)
                
                Text(notification.message)
                    .font(.caption)
                    .foregroundColor(.secondary)
                    .fixedSize(horizontal: false, vertical: true)
                
                Text(notification.timeFormatted)
                    .font(.caption2)
                    .foregroundColor(.secondary.opacity(0.7))
            }
            
            Spacer()
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6))
        )
    }
}

#Preview {
    NavigationStack {
        NotificationView()
    }
}
