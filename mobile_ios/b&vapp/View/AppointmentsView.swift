//
//  AppointmentsView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

import SwiftUI

struct AppointmentsView: View {
    
    @State private var selectedTab = 0
    
    let upcomingAppointments: [Appointment] = [
        Appointment(
            barberName: "Ahmet Yılmaz",
            service: "Saç Kesimi",
            date: "15 Ekim, Salı · 14:00",
            location: "B&V Erkek Saç Tasarım Akademi Merkezi – Nişantaşı",
            status: "ONAYLANDI",
            image: "barber1"
        ),
        Appointment(
            barberName: "Mehmet Kaya",
            service: "Sakal Tasarımı",
            date: "18 Ekim, Cuma · 10:30",
            location: "B&V Erkek Saç Tasarım Akademi Merkezi – Levent",
            status: "BEKLEMEDE",
            image: "barber2"
        )
    ]
    
    let pastAppointments: [Appointment] = [
        Appointment(
            barberName: "Cihan Can",
            service: "Komple Bakım",
            date: "2 Eylül, 2023",
            location: "",
            status: "Tamamlandı",
            image: ""
        )
    ]
    
    var body: some View {
        
        NavigationStack {
                
                VStack(spacing: 0) {
                    
                    // HEADER
                    HStack {
                        Text("Randevularım")
                            .font(.title2)
                            .fontWeight(.bold)
                        
                        Spacer()
                        
                        NavigationLink(destination: SelectAppointmentView()) {
                            Image(systemName: "plus")
                                .font(.title3)
                                .padding(10)
                                .background(Color.yellow)
                                .foregroundColor(.black)
                                .clipShape(Circle())
                        }
                    }
                    .padding()
                    
                    
                    // TAB BAR
                    HStack {
                        
                        Button {
                            selectedTab = 0
                        } label: {
                            VStack {
                                Text("Yaklaşan")
                                    .foregroundColor(selectedTab == 0 ? .yellow : .gray)
                                    .fontWeight(.semibold)
                                
                                Rectangle()
                                    .frame(height: 2)
                                    .foregroundColor(selectedTab == 0 ? .yellow : .clear)
                            }
                        }
                        
                        Button {
                            selectedTab = 1
                        } label: {
                            VStack {
                                Text("Geçmiş")
                                    .foregroundColor(selectedTab == 1 ? .yellow : .gray)
                                    .fontWeight(.semibold)
                                
                                Rectangle()
                                    .frame(height: 2)
                                    .foregroundColor(selectedTab == 1 ? .yellow : .clear)
                            }
                        }
                    }
                    .padding(.horizontal)
                    
                    
                    ScrollView {
                        
                        if selectedTab == 0 {
                            
                            VStack(spacing: 20) {
                                ForEach(upcomingAppointments) { appointment in
                                    AppointmentCardView(appointment: appointment)
                                }
                            }
                            .padding()
                            
                        } else {
                            
                            VStack(spacing: 20) {
                                ForEach(pastAppointments) { appointment in
                                    PastAppointmentCardView(appointment: appointment)
                                }
                            }
                            .padding()
                            
                        }
                        
                    }
                    
                }
            
            
        }
    }
}


struct AppointmentCardView: View {
    
    let appointment: Appointment
    
    var body: some View {
        
        VStack(alignment: .leading, spacing: 12) {
            
            Image(appointment.image)
                .resizable()
                .scaledToFill()
                .frame(height: 160)
                .clipped()
                .cornerRadius(12)
            
            HStack {
                Text("\(appointment.barberName) – \(appointment.service)")
                    .font(.headline)
                
                Spacer()
                
                Text(appointment.status)
                    .font(.caption)
                    .padding(6)
                    .background(Color.yellow.opacity(0.2))
                    .cornerRadius(6)
            }
            
            HStack {
                Image(systemName: "clock")
                Text(appointment.date)
                    .font(.subheadline)
            }
            .foregroundColor(.gray)
            
            
            HStack {
                Image(systemName: "location")
                Text(appointment.location)
                    .font(.subheadline)
            }
            .foregroundColor(.gray)
            
            
            HStack(spacing: 12) {
                
                Button {
                    
                } label: {
                    Text("İptal Et")
                        .foregroundColor(.red)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .overlay(
                            RoundedRectangle(cornerRadius: 10)
                                .stroke(Color.red)
                        )
                }
                
                Button {
                    
                } label: {
                    Text("Yol Tarifi")
                        .foregroundColor(.black)
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.yellow)
                        .cornerRadius(10)
                }
                
            }
            
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6))
        )
    }
}

struct PastAppointmentCardView: View {
    
    let appointment: Appointment
    
    var body: some View {
        
        VStack(alignment: .leading, spacing: 14) {
            
            HStack(spacing: 14) {
                
                // Hizmet Icon
                ZStack {
                    RoundedRectangle(cornerRadius: 12)
                        .fill(Color.yellow.opacity(0.15))
                        .frame(width: 50, height: 50)
                    
                    Image(systemName: "scissors")
                        .font(.title3)
                        .foregroundColor(.yellow)
                }
                
                
                VStack(alignment: .leading, spacing: 6) {
                    
                    Text("\(appointment.barberName) – \(appointment.service)")
                        .font(.headline)
                    
                    Text(appointment.date)
                        .font(.subheadline)
                        .foregroundColor(.gray)
                }
                
                Spacer()
                
            }
            
            
            Divider()
            
            
            // Yıldız Puan
            HStack(spacing: 4) {
                
                ForEach(0..<5) { _ in
                    Image(systemName: "star.fill")
                        .foregroundColor(.yellow)
                        .font(.caption)
                }
                
                Spacer()
                
                Text("Hizmet Puanlandı")
                    .font(.caption)
                    .foregroundColor(.gray)
            }
            
            
            // Butonlar
            HStack(spacing: 12) {
                
                Button {
                    
                } label: {
                    HStack {
                        Image(systemName: "star.bubble")
                        Text("Puanla")
                    }
                    .font(.subheadline)
                    .foregroundColor(.yellow)
                    .frame(maxWidth: .infinity)
                    .padding()
                    .overlay(
                        RoundedRectangle(cornerRadius: 10)
                            .stroke(Color.yellow.opacity(0.6))
                    )
                }
                
                
                Button {
                    
                } label: {
                    HStack {
                        Image(systemName: "doc.text")
                        Text("Detay")
                    }
                    .font(.subheadline)
                    .foregroundColor(.black)
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(Color.yellow)
                    .cornerRadius(10)
                }
            }
            
        }
        .padding()
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6))
        )
    }
}
#Preview {
    AppointmentsView()
}
