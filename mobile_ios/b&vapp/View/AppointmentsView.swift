//
//  AppointmentsView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI

struct AppointmentsView: View {
    
    @EnvironmentObject var viewModel: AppointmentsViewModel
    @State private var appointmentToCancel: Appointment? = nil
    @State private var showCancelAlert = false
    
    var body: some View {
        
        NavigationStack {
                
            VStack(spacing: 0) {
                
                // HEADER
                HStack {
                    Text("Randevularım")
                        .font(.title2)
                        .fontWeight(.bold)
                        .foregroundColor(.primary)
                    
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
                        viewModel.selectedTab = 0
                    } label: {
                        VStack {
                            Text("Yaklaşan")
                                .foregroundColor(viewModel.selectedTab == 0 ? .yellow : .secondary)
                                .fontWeight(.semibold)
                            
                            Rectangle()
                                .frame(height: 2)
                                .foregroundColor(viewModel.selectedTab == 0 ? .yellow : .clear)
                        }
                    }
                    
                    Button {
                        viewModel.selectedTab = 1
                    } label: {
                        VStack {
                            Text("Geçmiş")
                                .foregroundColor(viewModel.selectedTab == 1 ? .yellow : .secondary)
                                .fontWeight(.semibold)
                            
                            Rectangle()
                                .frame(height: 2)
                                .foregroundColor(viewModel.selectedTab == 1 ? .yellow : .clear)
                        }
                    }
                }
                .padding(.horizontal)
                
                
                // Paging TabView: buton + swipe ile sekme geçişi desteklenir
                TabView(selection: $viewModel.selectedTab) {

                    // MARK: Yaklaşan
                    Group {
                        if viewModel.upcomingAppointments.isEmpty {
                            EmptyAppointmentsState(
                                title: "Henüz Yaklaşan Randevunuz Yok",
                                message: "Bakımınızı planlamak için hemen yeni bir randevu oluşturabilirsiniz."
                            )
                        } else {
                            ScrollView {
                                VStack(spacing: 20) {
                                    ForEach(viewModel.upcomingAppointments) { appointment in
                                        AppointmentCardView(
                                            appointment: appointment,
                                            onCancel: {
                                                appointmentToCancel = appointment
                                                showCancelAlert = true
                                            },
                                            onDirections: {
                                                viewModel.openMaps()
                                            }
                                        )
                                    }
                                }
                                .padding()
                            }
                        }
                    }
                    .tag(0)

                    // MARK: Geçmiş
                    Group {
                        if viewModel.pastAppointments.isEmpty {
                            EmptyAppointmentsState(
                                title: "Geçmiş Randevunuz Bulunmuyor",
                                message: "Daha önce herhangi bir randevu geçmişiniz bulunmamaktadır."
                            )
                        } else {
                            ScrollView {
                                VStack(spacing: 20) {
                                    ForEach(viewModel.pastAppointments) { appointment in
                                        PastAppointmentCardView(appointment: appointment)
                                    }
                                }
                                .padding()
                            }
                        }
                    }
                    .tag(1)
                }
                .tabViewStyle(.page(indexDisplayMode: .never))
                // Swipe animasyonu için geçiş yumuşatma
                .animation(.easeInOut(duration: 0.25), value: viewModel.selectedTab)
                
            }
            .toolbar(.visible, for: .tabBar)
            .alert("Emin misiniz?", isPresented: $showCancelAlert) {
                Button("Vazgeç", role: .cancel) {
                    appointmentToCancel = nil
                }
                Button("Evet, İptal Et", role: .destructive) {
                    if let appt = appointmentToCancel {
                        Task { await viewModel.cancelAppointment(appt) }
                    }
                }
            } message: {
                Text("Randevunuz iptal edilecektir. Onaylıyor musunuz?")
            }
        }
        .task {
            await viewModel.fetchAppointments()
        }
    }
}

#Preview {
    AppointmentsView()
}

struct EmptyAppointmentsState: View {
    
    let title: String
    let message: String
    
    var body: some View {
        VStack(spacing: 16) {
            Image(systemName: "calendar.badge.exclamationmark")
                .font(.system(size: 50))
                .foregroundColor(.gray.opacity(0.5))
            
            Text(title)
                .font(.title3)
                .fontWeight(.bold)
                .foregroundColor(.primary)
            
            Text(message)
                .font(.subheadline)
                .foregroundColor(.secondary)
                .multilineTextAlignment(.center)
                .padding(.horizontal, 40)
        }
        .padding(.top, 80)
    }
}
