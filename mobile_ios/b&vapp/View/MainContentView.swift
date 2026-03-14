//
//  MainContentView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 13.03.2026.
//

import SwiftUI
import MapKit

struct MainContentView: View {
    
    @Binding var selectedTab: Tab

    var body: some View {
        
        ScrollView {
            
            VStack(spacing: 28) {
                
                headerSection
                    .padding(.horizontal, 20)
                
                promoCard
                    .padding(.horizontal, 20)
                
                servicesSection
                
                directionCard
                    .padding(.horizontal, 20)
                
            }
            .padding(.top, 10)
        }
        .background(Color.black.ignoresSafeArea())
        
    }
}


extension MainContentView {
    
    var headerSection: some View {
        
        HStack {
            
            Button {
                selectedTab = .profile
            } label: {
                
                Image("icon")
                    .resizable()
                    .frame(width: 50, height: 50)
                    .clipShape(Circle())
            }
                
            
            VStack(alignment: .leading, spacing: 2) {
                
                Text("Hoş geldin,")
                    .foregroundColor(.yellow)
                    .font(.subheadline)
                
                Text("Mustafa KARA")
                    .foregroundColor(.white)
                    .font(.title3)
                    .fontWeight(.semibold)
            }
            
            Spacer()
            
            NavigationLink(destination: NotificationView()) {
                
                ZStack {
                    Circle()
                        .fill(Color.gray.opacity(0.2))
                        .frame(width: 42, height: 42)
                    
                    Image(systemName: "bell")
                        .foregroundColor(.white)
                }
            }
        }
    }
}


extension MainContentView {
    var promoCard: some View {
        
        ZStack {
            
            Image("promoBackground")
                .resizable()
                .scaledToFill()
                .frame(height: 180)
                .clipped()
                .opacity(0.25)
            
            RoundedRectangle(cornerRadius: 22)
                .fill(
                    LinearGradient(
                        colors: [Color.black.opacity(0.9), Color.yellow.opacity(0.35)],
                        startPoint: .leading,
                        endPoint: .trailing
                    )
                )
            
            VStack(alignment: .leading, spacing: 14) {
                
                Text("B&V COFFE BARBER")
                    .font(.title2)
                    .fontWeight(.bold)
                    .foregroundColor(.white)
                
                Text("Usta ellerden profesyonel tasarım ve bakım hizmeti alın.")
                    .foregroundColor(.gray)
                    .font(.subheadline)
                    .fixedSize(horizontal: false, vertical: true)
                
                HStack {
                    
                    HStack(spacing: -10) {
                        
                        Image("icon")
                            .resizable()
                            .frame(width: 32, height: 32)
                            .clipShape(Circle())
                        
                        Image("icon")
                            .resizable()
                            .frame(width: 32, height: 32)
                            .clipShape(Circle())
                        
                        ZStack {
                            Circle()
                                .fill(Color.yellow)
                                .frame(width: 32, height: 32)
                            
                            Text("+3")
                                .font(.caption)
                                .fontWeight(.bold)
                        }
                    }
                    
                    Spacer()
                    
                    Button {
                        selectedTab = .appointments
                    } label: {
                        Text("Hemen Randevu Al")
                            .fontWeight(.semibold)
                            .padding(.horizontal, 18)
                            .padding(.vertical, 10)
                            .background(Color.yellow)
                            .foregroundColor(.black)
                            .cornerRadius(10)
                    }
                }
            }
            .padding(20)
        }
        .frame(height: 180)
        .clipShape(RoundedRectangle(cornerRadius: 22))
    }
}

extension MainContentView {
    
    var servicesSection: some View {
        
        VStack(alignment: .leading, spacing: 16) {
            
            HStack {
                
                Text("Hizmetlerimiz")
                    .foregroundColor(.white)
                    .font(.title3)
                    .fontWeight(.bold)
                
                Spacer()
                
                Button {
                    selectedTab = .services
                } label: {
                    Text("Tümünü Gör")
                        .foregroundColor(.yellow)
                        .fontWeight(.medium)
                }
            }
            .padding(.horizontal, 20)
            
            
            ScrollView(.horizontal, showsIndicators: false) {
                
                HStack(spacing: 16) {
                    
                    serviceCard(
                        image: "barber1",
                        price: "₺350",
                        title: "Saç Kesimi",
                        duration: "45 dk"
                    )
                    
                    serviceCard(
                        image: "barber3",
                        price: "₺200",
                        title: "Sakal Tıraşı",
                        duration: "30 dk"
                    )
                    
                    serviceCard(
                        image: "barber1",
                        price: "₺500",
                        title: "Cilt Bakımı",
                        duration: "60 dk"
                    )
                }
                .padding(.leading, 20)
            }
        }
    }}


extension MainContentView {
    
    func serviceCard(image: String, price: String, title: String, duration: String) -> some View {
        
        VStack(alignment: .leading, spacing: 8) {
            
            ZStack(alignment: .bottomLeading) {
                
                Image(image)
                    .resizable()
                    .scaledToFill()
                    .frame(width: 160, height: 180)
                    .clipped()
                    .cornerRadius(16)
                
                Text(price)
                    .foregroundColor(.yellow)
                    .fontWeight(.bold)
                    .padding(8)
            }
            
            Text(title)
                .foregroundColor(.white)
                .fontWeight(.semibold)
            
            /*HStack {
                Image(systemName: "clock")
                    .foregroundColor(.gray)
                
                Text(duration)
                    .foregroundColor(.gray)
                    .font(.caption)
            }*/
        }
        .frame(width: 160)
    }
}


extension MainContentView {
    
    var directionCard: some View {
        
        let location = CLLocationCoordinate2D(
            latitude: 36.9238616,
            longitude: 34.9011379
        )
        
        return ZStack {
            
            RoundedRectangle(cornerRadius: 20)
                .fill(Color.gray.opacity(0.25))
            
            HStack(spacing: 16) {
                
                Map(position: .constant(
                    MapCameraPosition.region(
                        MKCoordinateRegion(
                            center: location,
                            span: MKCoordinateSpan(
                                latitudeDelta: 0.003,
                                longitudeDelta: 0.003
                            )
                        )
                    )
                )) {
                    Marker("B&V Co...", coordinate: location)
                }
                .mapStyle(.hybrid)
                .frame(width: 100, height: 80)
                .clipShape(RoundedRectangle(cornerRadius: 12))
                
                VStack(alignment: .leading, spacing: 6) {
                    
                    Text("B&V COFFEE BARBER")
                        .foregroundColor(.white)
                        .fontWeight(.bold)
                        
                    
                    Text("Fatih Mah. Çağlayan Cad. No:39D/B Tarsus/Mersin")
                        .foregroundColor(.gray)
                        .font(.caption2)
                }
                
                Spacer()
                
                Button {
                    openMaps()
                } label: {
                    
                    Image(systemName: "arrow.triangle.turn.up.right.diamond.fill")
                        .foregroundColor(.white)
                        .font(.system(size: 20))
                        .padding(11)
                        .background(Color.yellow)
                        .clipShape(Circle())
                }
            }
            .padding(16)
        }
        .frame(height: 120)
        
        func openMaps() {
            
            let latitude: CLLocationDegrees = 36.9238616
            let longitude: CLLocationDegrees = 34.9011379
            
            let coordinate = CLLocationCoordinate2D(latitude: latitude, longitude: longitude)
            
            let mapItem = MKMapItem(placemark: MKPlacemark(coordinate: coordinate))
            mapItem.name = "B&V Berber"
            
            mapItem.openInMaps()
        }
    }
}
#Preview {
    MainContentView(selectedTab: .constant(.home))
}
