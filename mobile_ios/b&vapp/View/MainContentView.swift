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
    @EnvironmentObject var viewModel: HomeViewModel
    @State private var goToAppointment: Bool = false
    @State private var showNotificationView: Bool = false

    var body: some View {

        Group {
            if viewModel.isLoading {
                // Veriler hazır olana kadar loading spinner
                VStack {
                    Spacer()
                    ProgressView()
                        .scaleEffect(1.4)
                        .tint(.yellow)
                    Text("Yükleniyor...")
                        .font(.subheadline)
                        .foregroundColor(.secondary)
                        .padding(.top, 8)
                    Spacer()
                }
                .frame(maxWidth: .infinity, maxHeight: .infinity)
            } else {
                ScrollView {
                    VStack(spacing: 28) {

                        headerSection
                            .padding(.horizontal, 20)

                        promoCard
                            .padding(.horizontal, 20)

                        campaignsSection

                        servicesSection

                        directionCard
                            .padding(.horizontal, 20)
                    }
                    .padding(.top, 10)
                }
            }
        }
        .background(Color(.systemBackground).ignoresSafeArea())
        .navigationBarTitleDisplayMode(.inline)
        .toolbar(.hidden, for: .navigationBar)
        .toolbar(.visible, for: .tabBar)
        .task {
            await viewModel.fetchHomeData()
        }
        .navigationDestination(isPresented: $showNotificationView) {
            NotificationView()
        }
        .onReceive(NotificationCenter.default.publisher(for: .navigateToNotifications)) { _ in
            showNotificationView = true
        }
    }
}


// MARK: - Header

extension MainContentView {
    
    @ViewBuilder
    var headerSection: some View {

        // Kullanıcı verisi yüklenene kadar shimmer skeleton
        if viewModel.user == nil {
            ShimmerHeaderSkeleton()
        } else {
            HStack {

                Button {
                    selectedTab = .profile
                } label: {
                    // Profil fotoğrafı varsa CachedAsyncImage, yoksa initials
                    Group {
                        if let urlStr = viewModel.user?.profileImageUrl,
                           !urlStr.isEmpty,
                           let url = URL(string: urlStr) {
                            CachedAsyncImage(url: url) { phase in
                                switch phase {
                                case .success(let image):
                                    image
                                        .resizable()
                                        .scaledToFill()
                                default:
                                    initialsAvatar
                                }
                            }
                        } else {
                            initialsAvatar
                        }
                    }
                    .frame(width: 50, height: 50)
                    .clipShape(Circle())
                    .overlay(Circle().stroke(Color.yellow.opacity(0.5), lineWidth: 2))
                }

                VStack(alignment: .leading, spacing: 2) {
                    Text("Hoş geldin,")
                        .foregroundColor(.yellow)
                        .font(.subheadline)

                    Text(viewModel.userName)
                        .foregroundColor(.primary)
                        .font(.title3)
                        .fontWeight(.semibold)
                }

                Spacer()

                NavigationLink(destination: NotificationView()) {
                    ZStack {
                        Circle()
                            .fill(Color(.systemGray5))
                            .frame(width: 42, height: 42)
                        Image(systemName: "bell")
                            .foregroundColor(.primary)
                    }
                }
            }
        }
    }
    // Initials avatar (profil URL yoksa gösterilen)
    private var initialsAvatar: some View {
        ZStack {
            Circle().fill(Color.yellow.opacity(0.2))
            Text(viewModel.user?.initials ?? "B")
                .font(.headline)
                .fontWeight(.bold)
                .foregroundColor(.yellow)
        }
    }
}


// MARK: - Promo Card

extension MainContentView {

    var promoCard: some View {

        ZStack {

            Image("promoBackground")
                .resizable()
                .scaledToFill()
                .frame(height: 180)
                .clipped()
                .opacity(0.2)

            RoundedRectangle(cornerRadius: 22)
                .fill(
                    LinearGradient(
                        colors: [Color.black.opacity(0.9), Color.yellow.opacity(0.35)],
                        startPoint: .leading,
                        endPoint: .trailing
                    )
                )

            VStack(alignment: .leading, spacing: 14) {

                Text("B&V COFFEE BARBER")
                    .font(.title2)
                    .fontWeight(.bold)
                    .foregroundColor(.white)

                Text("Usta ellerden profesyonel tasarım ve bakım hizmeti alın.")
                    .foregroundColor(.gray)
                    .font(.subheadline)
                    .fixedSize(horizontal: false, vertical: true)

                HStack {

                    // Berber avatarları (initials)
                    HStack(spacing: -10) {
                        ForEach(viewModel.barbers.prefix(3)) { barber in
                            ZStack {
                                Circle()
                                    .fill(Color.yellow.opacity(0.3))
                                    .frame(width: 32, height: 32)
                                
                                if let imgUrlStr = barber.profileImageUrl,
                                   !imgUrlStr.isEmpty,
                                   let url = URL(string: imgUrlStr) {
                                    CachedAsyncImage(url: url) { phase in
                                        switch phase {
                                        case .success(let image):
                                            image
                                                .resizable()
                                                .scaledToFill()
                                                .frame(width: 32, height: 32)
                                                .clipShape(Circle())
                                        default:
                                            Text(barber.initials)
                                                .font(.caption2)
                                                .fontWeight(.bold)
                                                .foregroundColor(.white)
                                        }
                                    }
                                } else {
                                    Text(barber.initials)
                                        .font(.caption2)
                                        .fontWeight(.bold)
                                        .foregroundColor(.white)
                                }
                            }
                        }

                        if viewModel.barbers.count > 3 {
                            ZStack {
                                Circle()
                                    .fill(Color.yellow)
                                    .frame(width: 32, height: 32)
                                Text("+\(viewModel.barbers.count - 3)")
                                    .font(.caption2)
                                    .fontWeight(.bold)
                                    .foregroundColor(.black)
                            }
                        }
                    }

                    Spacer()

                    Button {
                        goToAppointment = true
                    } label: {
                        Text("Hemen Randevu Al")
                            .fontWeight(.semibold)
                            .padding(.horizontal, 18)
                            .padding(.vertical, 10)
                            .background(Color.yellow)
                            .foregroundColor(.black)
                            .cornerRadius(10)
                    }
                    .navigationDestination(isPresented: $goToAppointment) {
                        SelectAppointmentView()
                    }
                }
            }
            .padding(20)
        }
        .frame(height: 180)
        .clipShape(RoundedRectangle(cornerRadius: 22))
    }
}


// MARK: - Services Section

extension MainContentView {

    var servicesSection: some View {

        VStack(alignment: .leading, spacing: 16) {

            HStack {
                Text("Hizmetlerimiz")
                    .foregroundColor(.primary)
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
                    if viewModel.services.isEmpty {
                        // Hizmetler yüklenirken 3 skeleton kart göster
                        ForEach(0..<3, id: \.self) { _ in
                            ShimmerServiceCard()
                        }
                    } else {
                        ForEach(viewModel.services.prefix(5)) { service in
                            serviceCard(service: service)
                        }
                    }
                }
                .padding(.leading, 20)
            }
        }
    }

    func serviceCard(service: Service) -> some View {

        VStack(alignment: .leading, spacing: 8) {

            ZStack(alignment: .topTrailing) {
                ZStack(alignment: .bottomLeading) {

                    // CachedAsyncImage: önbelleğe alınan görsel
                    CachedAsyncImage(url: URL(string: service.imageUrl)) { phase in
                        switch phase {
                        case .success(let image):
                            image.resizable().scaledToFill()
                        case .empty:
                            // Yükleniyor — shimmer placeholder
                            ShimmerCard(width: 160, height: 180, cornerRadius: 16)
                        default:
                            ZStack {
                                Color.gray.opacity(0.2)
                                Image(systemName: "scissors").foregroundColor(.gray)
                            }
                        }
                    }
                    .frame(width: 160, height: 180)
                    .clipped()
                    .cornerRadius(16)

                    HStack(spacing: 4) {
                        if let discounted = service.discountedPrice, discounted < service.price {
                            Text("₺\(service.price)")
                                .font(.caption2)
                                .strikethrough()
                                .foregroundColor(.white.opacity(0.6))
                            Text("₺\(discounted)")
                                .foregroundColor(.yellow)
                                .fontWeight(.bold)
                                .font(.caption)
                        } else {
                            Text("₺\(service.price)")
                                .foregroundColor(.yellow)
                                .fontWeight(.bold)
                                .font(.caption)
                        }
                    }
                    .padding(.horizontal, 8)
                    .padding(.vertical, 4)
                    .background(Color.black.opacity(0.7))
                    .cornerRadius(8)
                    .padding(8)
                }

                if let discounted = service.discountedPrice, discounted < service.price {
                    let discountPercent = Int(round(Double(service.price - discounted) / Double(service.price) * 100))
                    Text("%\(discountPercent) İNDİRİM")
                        .font(.system(size: 9, weight: .bold))
                        .foregroundColor(.black)
                        .padding(.horizontal, 8)
                        .padding(.vertical, 4)
                        .background(Color.yellow)
                        .clipShape(Capsule())
                        .padding(8)
                }
            }

            Text(service.name)
                .foregroundColor(.primary)
                .fontWeight(.semibold)
                .lineLimit(1)
        }
        .frame(width: 160)
    }
}


// MARK: - Direction Card

extension MainContentView {

    var directionCard: some View {

        let location = CLLocationCoordinate2D(latitude: 36.9238616, longitude: 34.9011379)

        return ZStack {

            RoundedRectangle(cornerRadius: 20)
                .fill(Color.gray.opacity(0.2))

            HStack(spacing: 16) {

                Map(position: .constant(
                    MapCameraPosition.region(
                        MKCoordinateRegion(
                            center: location,
                            span: MKCoordinateSpan(latitudeDelta: 0.003, longitudeDelta: 0.003)
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
                        .foregroundColor(.primary)
                        .fontWeight(.bold)

                    Text("Fatih Mah. Çağlayan Cad. No:39D/B Tarsus/Mersin")
                        .foregroundColor(.secondary)
                        .font(.caption2)
                }

                Spacer()

                Button {
                    viewModel.openMaps()
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
    }
}


// MARK: - Campaigns Section

extension MainContentView {

    var campaignsSection: some View {
        Group {
            if !viewModel.campaigns.isEmpty {
                VStack(alignment: .leading, spacing: 16) {
                    HStack {
                        Text("Aktif Kampanyalar")
                            .foregroundColor(.primary)
                            .font(.title3)
                            .fontWeight(.bold)
                        
                        Spacer()
                        
                        Image(systemName: "tag.fill")
                            .foregroundColor(.yellow)
                    }
                    .padding(.horizontal, 20)
                    
                    ScrollView(.horizontal, showsIndicators: false) {
                        HStack(spacing: 16) {
                            ForEach(viewModel.campaigns) { campaign in
                                campaignCard(campaign: campaign)
                            }
                        }
                        .padding(.horizontal, 20)
                    }
                }
            }
        }
    }

    func campaignCard(campaign: Campaign) -> some View {
        HStack(spacing: 0) {
            // Sol Taraf (Hizmet Bilgileri, Başlık vb.)
            VStack(alignment: .leading, spacing: 6) {
                // Fırsat Etiketi
                Text("KAMPANYA")
                    .font(.system(size: 9, weight: .bold))
                    .foregroundColor(.yellow)
                    .padding(.horizontal, 8)
                    .padding(.vertical, 3)
                    .background(Color.yellow.opacity(0.15))
                    .cornerRadius(6)
                
                Text(campaign.title)
                    .font(.subheadline)
                    .fontWeight(.bold)
                    .foregroundColor(.white)
                    .lineLimit(1)
                
                Text(campaign.description)
                    .font(.system(size: 11))
                    .foregroundColor(.gray)
                    .lineLimit(2)
                    .multilineTextAlignment(.leading)
                    .fixedSize(horizontal: false, vertical: true)
                
                Spacer(minLength: 0)
                
                if let endDate = campaign.endDate {
                    HStack(spacing: 4) {
                        Image(systemName: "calendar.badge.clock")
                            .font(.system(size: 10))
                            .foregroundColor(.yellow)
                        Text("Son gün: \(formatCampaignDate(endDate))")
                            .font(.system(size: 9, weight: .medium))
                            .foregroundColor(.yellow.opacity(0.8))
                    }
                }
            }
            .padding(.all, 14)
            
            Spacer(minLength: 0)
            
            // Dikey Dotted Çizgi
            DottedLine()
                .stroke(style: StrokeStyle(lineWidth: 1, dash: [4, 4]))
                .foregroundColor(.yellow.opacity(0.3))
                .frame(width: 1)
                .padding(.vertical, 10)
            
            // Sağ Taraf (İndirim Tutarı / Ticket Stub)
            VStack(spacing: 4) {
                Spacer()
                if campaign.discountType == "percentage" {
                    Text("%\(Int(campaign.discountValue))")
                        .font(.system(size: 28, weight: .black, design: .rounded))
                        .foregroundColor(.yellow)
                } else {
                    Text("₺\(Int(campaign.discountValue))")
                        .font(.system(size: 24, weight: .black, design: .rounded))
                        .foregroundColor(.yellow)
                }
                
                Text("İNDİRİM")
                    .font(.system(size: 9, weight: .bold))
                    .foregroundColor(.white)
                    .tracking(1.5)
                Spacer()
            }
            .frame(width: 85)
            .background(
                LinearGradient(
                    colors: [Color.yellow.opacity(0.08), Color.clear],
                    startPoint: .trailing,
                    endPoint: .leading
                )
            )
        }
        .frame(width: 300, height: 130)
        .background(
            Color(red: 0.12, green: 0.12, blue: 0.12)
        )
        .clipShape(TicketShape(notchRadius: 10))
        .overlay(
            TicketShape(notchRadius: 10)
                .stroke(
                    LinearGradient(
                        colors: [Color.yellow.opacity(0.4), Color.yellow.opacity(0.1)],
                        startPoint: .topLeading,
                        endPoint: .bottomTrailing
                    ),
                    lineWidth: 1.5
                )
        )
        .shadow(color: Color.black.opacity(0.3), radius: 8, x: 0, y: 4)
    }

    private func formatCampaignDate(_ dateStr: String) -> String {
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd"
        if let date = formatter.date(from: dateStr) {
            formatter.dateFormat = "d MMMM"
            formatter.locale = Locale(identifier: "tr_TR")
            return formatter.string(from: date)
        }
        return dateStr
    }
}

// MARK: - Premium Ticket UI Helpers

struct TicketShape: Shape {
    var notchRadius: CGFloat = 8
    var notchXFromRight: CGFloat = 85
    
    func path(in rect: CGRect) -> Path {
        var path = Path()
        
        let notchX = rect.width - notchXFromRight
        
        // Start top-left
        path.move(to: CGPoint(x: rect.minX, y: rect.minY))
        
        // Top edge with notch
        path.addLine(to: CGPoint(x: notchX - notchRadius, y: rect.minY))
        path.addArc(center: CGPoint(x: notchX, y: rect.minY),
                    radius: notchRadius,
                    startAngle: Angle(degrees: 180),
                    endAngle: Angle(degrees: 0),
                    clockwise: true)
        path.addLine(to: CGPoint(x: rect.maxX, y: rect.minY))
        
        // Right edge
        path.addLine(to: CGPoint(x: rect.maxX, y: rect.maxY))
        
        // Bottom edge with notch
        path.addLine(to: CGPoint(x: notchX + notchRadius, y: rect.maxY))
        path.addArc(center: CGPoint(x: notchX, y: rect.maxY),
                    radius: notchRadius,
                    startAngle: Angle(degrees: 0),
                    endAngle: Angle(degrees: 180),
                    clockwise: true)
        path.addLine(to: CGPoint(x: rect.minX, y: rect.maxY))
        
        // Left edge
        path.closeSubpath()
        
        return path
    }
}

struct DottedLine: Shape {
    func path(in rect: CGRect) -> Path {
        var path = Path()
        path.move(to: CGPoint(x: rect.midX, y: rect.minY))
        path.addLine(to: CGPoint(x: rect.midX, y: rect.maxY))
        return path
    }
}


#Preview {
    MainContentView(selectedTab: .constant(.home))
}
