//  SelectAppointmentView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import SwiftUI

struct SelectAppointmentView: View {
    
    @Environment(\.dismiss) var dismiss
    @EnvironmentObject var viewModel: AppointmentsViewModel
    
    // Özet bottom sheet
    @State private var showBookingSummary = false
    
    // Açık kategorileri takip eden set
    @State private var expandedCategories: Set<String> = []
      var body: some View {
        
        VStack(spacing: 0) {
            
            // HEADER
            HStack {
                Button { dismiss() } label: {
                    Image(systemName: "chevron.left")
                        .font(.title3)
                        .foregroundColor(.primary)
                }
                Spacer()
                Text("Randevu Oluştur")
                    .font(.headline)
                    .fontWeight(.semibold)
                Spacer()
                Circle().fill(Color.clear).frame(width: 24)
            }
            .padding()
            
            ScrollView {
                VStack(alignment: .leading, spacing: 28) {
                    barberSelectionSection
                    dateSelectionSection
                    serviceSelectionSection
                    timeSelectionSection
                    Spacer(minLength: 20)
                }
                .padding()
            }
            
            // MARK: Onayla Butonu
            Button {
                showBookingSummary = true
            } label: {
                Text("Randevuyu Onayla")
                    .font(.title3)
                    .fontWeight(.bold)
                    .frame(maxWidth: .infinity)
                    .padding()
                    .background(canConfirm ? Color.yellow : Color.gray.opacity(0.4))
                    .foregroundColor(canConfirm ? .black : .gray)
                    .cornerRadius(16)
            }
            .disabled(!canConfirm)
            .padding()
        }
        .background(Color(.systemBackground))
        .foregroundColor(.primary)
        .toolbar(.hidden, for: .tabBar)
        .navigationBarBackButtonHidden(true)
        .alert(viewModel.alertTitle, isPresented: $viewModel.showAlert) {
            Button("Tamam", role: .cancel) { }
        } message: {
            Text(viewModel.alertMessage)
        }
        .task {
            await viewModel.fetchBookingData()
            if let selected = viewModel.selectedServices.first {
                expandedCategories.insert(selected.category)
            }
        }
        .onChange(of: viewModel.services.isEmpty) { isEmpty in
            if !isEmpty, let selected = viewModel.selectedServices.first {
                expandedCategories.insert(selected.category)
            }
        }
        // MARK: Özet Bottom Sheet
        .sheet(isPresented: $showBookingSummary) {
            NavigationStack {
                BookingSummarySheet(
                    viewModel: viewModel,
                    onConfirm: {
                        showBookingSummary = false
                        Task {
                            let success = await viewModel.createAppointment()
                            if success {
                                viewModel.resetSelection()
                                dismiss()
                            }
                        }
                    },
                    onEdit: {
                        showBookingSummary = false
                    }
                )
                .toolbar(.hidden, for: .navigationBar)
            }
            .presentationDetents([.fraction(0.8), .large])
            .presentationCornerRadius(28)
            .presentationDragIndicator(.visible)
        }
    }
    
    // MARK: - Helpers
    
    private var popularServices: [Service] {
        viewModel.services.filter { ($0.isPopular ?? false) || ($0.isFeatured ?? false) }
    }
    
    private var groupedServices: [String: [Service]] {
        Dictionary(grouping: viewModel.services) { $0.category }
    }
    
    private var sortedCategories: [String] {
        groupedServices.keys.sorted()
    }
    
    // MARK: - Subviews for Booking
    
    @ViewBuilder
    private var barberSelectionSection: some View {
        VStack(alignment: .leading, spacing: 12) {
            sectionHeader("Personel Seç")
            
            if viewModel.barbers.isEmpty {
                loadingOrEmpty("Berber yükleniyor...")
            } else {
                ScrollView(.horizontal, showsIndicators: false) {
                    HStack(alignment: .top, spacing: 18) {
                        ForEach(viewModel.barbers) { barber in
                            BarberCard(
                                barber: barber,
                                isSelected: barber.id == viewModel.selectedBarber?.id
                            )
                            .onTapGesture {
                                if barber.id != viewModel.selectedBarber?.id {
                                    viewModel.selectedBarber = barber
                                    viewModel.onBarberChanged()
                                }
                            }
                        }
                    }
                    .padding(.top, 8)
                    .padding(.bottom, 4)
                }
            }
        }
    }
    
    @ViewBuilder
    private var dateSelectionSection: some View {
        VStack(alignment: .leading, spacing: 12) {
            HStack {
                sectionHeader("Tarih Seçin")
                Spacer()
                Text(DateManager.monthYearString(from: viewModel.selectedDate))
                    .foregroundColor(.yellow)
            }
            
            if viewModel.isLoadingAvailability {
                HStack {
                    ProgressView()
                        .tint(.yellow)
                    Text("Müsait günler yükleniyor...")
                        .font(.subheadline)
                        .foregroundColor(.secondary)
                }
            } else if viewModel.availableDates.isEmpty {
                Text("Bu berber için müsait gün bulunamadı.")
                    .font(.subheadline)
                    .foregroundColor(.secondary)
                    .padding(.vertical, 8)
            } else {
                ScrollView(.horizontal, showsIndicators: false) {
                    HStack(spacing: 12) {
                        ForEach(viewModel.availableDates, id: \.self) { date in
                            DateCard(
                                date: date,
                                isSelected: Calendar.current.isDate(date, inSameDayAs: viewModel.selectedDate)
                            )
                            .onTapGesture {
                                viewModel.selectedDate = date
                                viewModel.onDateChanged()
                            }
                        }
                    }
                }
            }
        }
    }
    
    @ViewBuilder
    private var serviceSelectionSection: some View {
        VStack(alignment: .leading, spacing: 16) {
            sectionHeader("Hizmet Seçin")
            
            if viewModel.services.isEmpty {
                loadingOrEmpty("Hizmetler yükleniyor...")
            } else {
                VStack(alignment: .leading, spacing: 16) {
                    // 1. Popüler & Öne Çıkanlar (isPopular == true veya isFeatured == true olanlar)
                    if !popularServices.isEmpty {
                        VStack(alignment: .leading, spacing: 10) {
                            HStack(spacing: 6) {
                                Image(systemName: "star.fill")
                                    .foregroundColor(.yellow)
                                    .font(.footnote)
                                Text("Popüler & Öne Çıkanlar")
                                    .font(.subheadline)
                                    .fontWeight(.bold)
                                    .foregroundColor(.secondary)
                            }
                            .padding(.horizontal, 4)
                            
                            ForEach(popularServices) { service in
                                ServiceSelectCard(
                                    service: service,
                                    isSelected: viewModel.selectedServices.contains(service)
                                )
                                .onTapGesture {
                                    toggleService(service)
                                    // Seçilen popüler hizmetin ait olduğu kategoriyi de otomatik açalım
                                    withAnimation {
                                        expandedCategories.insert(service.category)
                                    }
                                }
                            }
                        }
                        .padding(.bottom, 6)
                    }
                    
                    // 2. Kategorilere Göre Hizmetler (Accordion — SSS Tarzı)
                    ForEach(sortedCategories, id: \.self) { category in
                        CategoryAccordionCard(
                            category: category,
                            displayName: categoryDisplayName(category),
                            services: groupedServices[category] ?? [],
                            isExpanded: expandedCategories.contains(category),
                            selectedServices: viewModel.selectedServices,
                            onToggle: {
                                withAnimation(.easeInOut(duration: 0.25)) {
                                    if expandedCategories.contains(category) {
                                        expandedCategories.remove(category)
                                    } else {
                                        expandedCategories.insert(category)
                                    }
                                }
                            },
                            onSelectService: { service in
                                toggleService(service)
                            }
                        )
                    }
                }
            }
        }
    }
    
    @ViewBuilder
    private var timeSelectionSection: some View {
        VStack(alignment: .leading, spacing: 12) {
            HStack {
                sectionHeader("Saat Seçin")
                Spacer()
                HStack(spacing: 6) {
                    Circle().frame(width: 8, height: 8).foregroundColor(.gray)
                    Text("DOLU").font(.caption).foregroundColor(.gray)
                }
            }
            
            if viewModel.isLoadingAvailability {
                ShimmerTimeGrid()
            } else if viewModel.currentTimeSlots.isEmpty {
                Text("Bu gün için müsait saat bulunamadı.")
                    .font(.subheadline)
                    .foregroundColor(.secondary)
            } else {
                LazyVGrid(
                    columns: Array(repeating: GridItem(.flexible()), count: 4),
                    spacing: 14
                ) {
                    ForEach(viewModel.currentTimeSlots, id: \.self) { time in
                        let isPast    = TimeManager.isPastTime(selectedDate: viewModel.selectedDate, time: time)
                        let isBlocked = viewModel.blockedSlots.contains(time)
                        let disabled  = isPast || isBlocked
                        
                        TimeCard(
                            time: time,
                            isSelected: viewModel.selectedTime == time,
                            disabled: disabled
                        )
                        .onTapGesture {
                            if !disabled { viewModel.selectedTime = time }
                        }
                    }
                }
            }
        }
    }
    
    private func categoryDisplayName(_ category: String) -> String {
        switch category.lowercased() {
        case "hair", "saç", "sac":
            return "Saç Kesim & Tasarım"
        case "shave", "sakal":
            return "Sakal Tıraşı & Bakımı"
        case "care", "cilt", "cilt bakımı", "bakım":
            return "Cilt Bakımı & Maske"
        case "hair_care", "saç bakımı":
            return "Saç Bakımı & Fön"
        default:
            return category.capitalized
        }
    }
    
    private var canConfirm: Bool {
        viewModel.selectedBarber != nil &&
        !viewModel.selectedServices.isEmpty &&
        viewModel.selectedTime != nil &&
        !viewModel.availableDates.isEmpty
    }
    
    private func toggleService(_ service: Service) {
        if viewModel.selectedServices.contains(service) {
            if viewModel.selectedServices.count > 1 {
                viewModel.selectedServices.remove(service)
            }
        } else {
            viewModel.selectedServices.insert(service)
        }
    }
    
    private func sectionHeader(_ text: String) -> some View {
        Text(text)
            .font(.title3)
            .fontWeight(.semibold)
    }
    
    private func loadingOrEmpty(_ text: String) -> some View {
        Text(text)
            .font(.subheadline)
            .foregroundColor(.secondary)
            .padding(.vertical, 4)
    }
    
    // MARK: - Date Card
    
    struct DateCard: View {
        let date: Date
        let isSelected: Bool
        
        var body: some View {
            VStack(spacing: 4) {
                Text(DateManager.dayString(from: date)).font(.caption)
                Text(DateManager.dayNumber(from: date)).font(.title2).fontWeight(.bold)
            }
            .frame(width: 70, height: 80)
            .background(isSelected ? Color.yellow : Color(.systemGray6).opacity(0.6))
            .foregroundColor(isSelected ? .black : .primary)
            .cornerRadius(14)
        }
    }
    
    
    // MARK: - Barber Card
    
    struct BarberCard: View {
        let barber: Barber
        let isSelected: Bool
        
        var body: some View {
            VStack(spacing: 6) {
                ZStack {
                    // Daire 60x60'a küçültüldü — üst kesimleri önlemek için
                    Circle()
                        .fill(isSelected ? Color.yellow.opacity(0.25) : Color(.systemGray5))
                        .frame(width: 60, height: 60)
                        .overlay(Circle().stroke(isSelected ? Color.yellow : Color.clear, lineWidth: 3))
                    
                    if let imgUrlStr = barber.profileImageUrl,
                       !imgUrlStr.isEmpty,
                       let url = URL(string: imgUrlStr) {
                        CachedAsyncImage(url: url) { phase in
                            switch phase {
                            case .success(let image):
                                image
                                    .resizable()
                                    .scaledToFill()
                                    .frame(width: 60, height: 60)
                                    .clipShape(Circle())
                            default:
                                Text(barber.initials)
                                    .font(.headline)
                                    .fontWeight(.bold)
                                    .foregroundColor(isSelected ? .yellow : .primary)
                            }
                        }
                    } else {
                        Text(barber.initials)
                            .font(.headline)
                            .fontWeight(.bold)
                            .foregroundColor(isSelected ? .yellow : .primary)
                    }
                }
                
                Text(barber.fullName)
                    .font(.caption)
                    .multilineTextAlignment(.center)
                    .lineLimit(2)
                    .frame(width: 70)
                
                if barber.rating > 0 {
                    HStack(spacing: 2) {
                        Image(systemName: "star.fill")
                            .font(.caption2)
                            .foregroundColor(.yellow)
                        Text(String(format: "%.1f", barber.rating))
                            .font(.caption2)
                            .foregroundColor(.secondary)
                    }
                }
            }
            // Sabit yükseklik + ek üst boşluk: scroll view'da kesilmeyi önler
            .frame(minHeight: 100)
            .padding(.top, 4)
        }
    }
    
    
    // MARK: - Service Select Card
    
    struct ServiceSelectCard: View {
        let service: Service
        let isSelected: Bool
        
        var body: some View {
            HStack(spacing: 14) {
                CachedAsyncImage(url: URL(string: service.imageUrl)) { phase in
                    switch phase {
                    case .success(let image):
                        image.resizable().scaledToFill()
                    case .empty:
                        // Yükleniyor — shimmer
                        ShimmerCard(width: 54, height: 54, cornerRadius: 10)
                    default:
                        ZStack {
                            Color.gray.opacity(0.2)
                            Image(systemName: "scissors").foregroundColor(.gray)
                        }
                    }
                }
                .frame(width: 54, height: 54)
                .clipShape(RoundedRectangle(cornerRadius: 10))
                
                VStack(alignment: .leading, spacing: 4) {
                    Text(service.name)
                        .font(.subheadline)
                        .fontWeight(.semibold)
                        .foregroundColor(isSelected ? .yellow : .primary)
                    HStack(spacing: 8) {
                        Label("\(service.duration) dk", systemImage: "clock")
                            .font(.caption2)
                            .foregroundColor(.secondary)
                        Text("·").foregroundColor(.secondary).font(.caption2)
                        Text(service.category.capitalized)
                            .font(.caption2)
                            .foregroundColor(.secondary)
                    }
                }
                
                Spacer()
                
                VStack(alignment: .trailing, spacing: 3) {
                    if let discounted = service.discountedPrice, discounted < service.price {
                        let discountPercent = Int(round(Double(service.price - discounted) / Double(service.price) * 100))
                        Text("%\(discountPercent) İndirim")
                            .font(.system(size: 8, weight: .bold))
                            .foregroundColor(.black)
                            .padding(.horizontal, 6)
                            .padding(.vertical, 2)
                            .background(Color.yellow)
                            .clipShape(Capsule())
                        
                        Text("₺\(service.price)")
                            .font(.caption2)
                            .strikethrough()
                            .foregroundColor(.secondary)
                        Text("₺\(discounted)")
                            .font(.subheadline)
                            .fontWeight(.bold)
                            .foregroundColor(.yellow)
                    } else {
                        Text("₺\(service.price)")
                            .font(.subheadline)
                            .fontWeight(.bold)
                            .foregroundColor(.yellow)
                    }
                }
                
                if isSelected {
                    Image(systemName: "checkmark.circle.fill")
                        .foregroundColor(.yellow)
                }
            }
            .padding(12)
            .background(
                RoundedRectangle(cornerRadius: 14)
                    .fill(isSelected ? Color.yellow.opacity(0.08) : Color(.systemGray6))
                    .overlay(
                        RoundedRectangle(cornerRadius: 14)
                            .stroke(isSelected ? Color.yellow : Color.clear, lineWidth: 1.5)
                    )
            )
        }
    }
    
    
    // MARK: - Time Card
    
    struct TimeCard: View {
        let time: String
        let isSelected: Bool
        let disabled: Bool
        
        var body: some View {
            Text(TimeManager.displayTime(time))
                .font(.subheadline)
                .fontWeight(.semibold)
                .frame(maxWidth: .infinity)
                .padding(.vertical, 12)
                .background(
                    disabled ? Color.gray.opacity(0.12) :
                        isSelected ? Color.yellow :
                        Color(.systemGray6).opacity(0.6)
                )
                .foregroundColor(
                    disabled ? .gray :
                        isSelected ? .black : .primary
                )
                .cornerRadius(10)
        }
    }
    
    
    // MARK: - Category Accordion Card (SSS Tarzı)
    
    struct CategoryAccordionCard: View {
        let category: String
        let displayName: String
        let services: [Service]
        let isExpanded: Bool
        let selectedServices: Set<Service>
        let onToggle: () -> Void
        let onSelectService: (Service) -> Void
        
        var body: some View {
            VStack(alignment: .leading, spacing: 0) {
                
                // BAŞLIK
                Button(action: onToggle) {
                    HStack {
                        Text(displayName)
                            .font(.footnote)
                            .fontWeight(.semibold)
                            .foregroundColor(.primary)
                            .multilineTextAlignment(.leading)
                        
                        Spacer()
                        
                        Text("\(services.count) Hizmet")
                            .font(.caption2)
                            .foregroundColor(.secondary)
                        
                        Image(systemName: isExpanded ? "chevron.up" : "chevron.down")
                            .font(.caption2)
                            .foregroundColor(.yellow)
                    }
                    .padding()
                }
                
                // İÇERİK
                if isExpanded {
                    
                    Divider()
                        .padding(.horizontal)
                    
                    VStack(spacing: 10) {
                        ForEach(services) { service in
                            ServiceSelectCard(
                                service: service,
                                isSelected: selectedServices.contains(service)
                            )
                            .onTapGesture {
                                onSelectService(service)
                            }
                        }
                    }
                    .padding()
                    .fixedSize(horizontal: false, vertical: true)
                }
            }
            .background(
                RoundedRectangle(cornerRadius: 16)
                    .fill(Color(.systemGray6))
            )
            .clipShape(RoundedRectangle(cornerRadius: 16))
        }
    }
    
    
    // MARK: - Booking Summary Sheet
    
    struct BookingSummarySheet: View {
        
        @ObservedObject var viewModel: AppointmentsViewModel
        let onConfirm: () -> Void
        let onEdit: () -> Void
        
        var body: some View {
            VStack(spacing: 0) {
                
                // Başlık ve Düzenle Butonu
                HStack {
                    Text("Randevu Özeti")
                        .font(.title3.bold())
                    
                    Spacer()
                    
                    Button(action: onEdit) {
                        Text("Randevuyu Düzenle")
                            .font(.subheadline)
                            .foregroundColor(.black)
                            .padding(.horizontal, 12)
                            .padding(.vertical, 6)
                            .background(Color.yellow.opacity(0.85))
                            .cornerRadius(8)
                    }
                }
                .padding(.horizontal, 24)
                .padding(.top, 24)
                .padding(.bottom, 16)
                
                Divider()
                
                // Detay satırları
                VStack(spacing: 0) {
                    
                    summaryRow(
                        icon: "calendar",
                        label: "Tarih",
                        value: formattedDate
                    )
                    Divider().padding(.leading, 56)
                    
                    summaryRow(
                        icon: "clock.fill",
                        label: "Saat",
                        value: viewModel.selectedTime ?? "-"
                    )
                    Divider().padding(.leading, 56)
                    
                    summaryRow(
                        icon: "person.fill",
                        label: "Personel",
                        value: viewModel.selectedBarber?.fullName ?? "-"
                    )
                    Divider().padding(.leading, 56)
                    
                    summaryRow(
                        icon: "scissors",
                        label: "Hizmet",
                        value: viewModel.selectedServices.isEmpty ? "-" : viewModel.selectedServices.map { $0.name }.joined(separator: " + ")
                    )
                    Divider().padding(.leading, 56)
                    
                    let totalPrice = viewModel.selectedServices.reduce(0) { $0 + $1.effectivePrice }
                    let finalPrice = max(0, Double(totalPrice) - viewModel.validatedDiscountAmount)
                    
                    HStack {
                        Image(systemName: "turkishlirasign.circle.fill")
                            .foregroundColor(.yellow)
                            .frame(width: 24, alignment: .center)
                            .padding(.leading, 20)
                        Text("Toplam Ücret")
                            .font(.subheadline)
                            .foregroundColor(.secondary)
                        Spacer()
                        
                        if viewModel.isCouponValid == true && viewModel.validatedDiscountAmount > 0 {
                            Text("₺\(totalPrice)")
                                .strikethrough()
                                .foregroundColor(.gray)
                                .font(.subheadline)
                            Text("₺\(finalPrice, specifier: "%.2f")")
                                .fontWeight(.bold)
                                .foregroundColor(.green)
                        } else {
                            Text("₺\(totalPrice)")
                                .fontWeight(.bold)
                                .foregroundColor(.yellow)
                        }
                    }
                    .padding(.trailing, 20)
                    .padding(.vertical, 12)
                }
                .padding(.vertical, 8)
                
                Divider()
                    .padding(.bottom, 16)
                
                // Kupon / Kampanya Seçimi
                VStack(alignment: .leading, spacing: 8) {
                    Picker("İndirim Seçimi", selection: $viewModel.discountMode) {
                        Text("Kampanya Kullan").tag(DiscountMode.campaign)
                        Text("Kupon Kodu Gir").tag(DiscountMode.coupon)
                    }
                    .pickerStyle(.segmented)
                    .padding(.bottom, 8)
                    .onChange(of: viewModel.discountMode) { _ in
                        viewModel.isCouponValid = nil
                        viewModel.couponMessage = ""
                        viewModel.validatedDiscountAmount = 0.0
                    }
                    
                    if viewModel.discountMode == .campaign {
                        if viewModel.availableCampaigns.isEmpty {
                            Text("Size özel aktif bir kampanya bulunmuyor.")
                                .font(.subheadline)
                                .foregroundColor(.secondary)
                                .padding(.vertical, 8)
                        } else {
                            ScrollView(.horizontal, showsIndicators: false) {
                                HStack(spacing: 12) {
                                    ForEach(viewModel.availableCampaigns) { campaign in
                                        let isSelected = viewModel.selectedCampaignId == campaign.id
                                        VStack(alignment: .leading, spacing: 4) {
                                            HStack(alignment: .top) {
                                                Text(campaign.title)
                                                    .font(.subheadline.bold())
                                                    .foregroundColor(isSelected ? .white : .primary)
                                                
                                                if isSelected {
                                                    Spacer(minLength: 8)
                                                    Button(action: {
                                                        viewModel.selectedCampaignId = nil
                                                        viewModel.isCouponValid = nil
                                                        viewModel.couponMessage = ""
                                                        viewModel.validatedDiscountAmount = 0.0
                                                    }) {
                                                        Image(systemName: "xmark.circle.fill")
                                                            .foregroundColor(.white.opacity(0.8))
                                                    }
                                                }
                                            }
                                            
                                            if !campaign.description.isEmpty {
                                                Text(campaign.description)
                                                    .font(.caption)
                                                    .foregroundColor(isSelected ? .white.opacity(0.8) : .secondary)
                                                    .lineLimit(1)
                                            }
                                        }
                                        .padding(.vertical, 10)
                                        .padding(.horizontal, 16)
                                        .frame(minWidth: 140)
                                        .background(isSelected ? Color.blue : Color.gray.opacity(0.15))
                                        .cornerRadius(12)
                                        .onTapGesture {
                                            if !isSelected {
                                                viewModel.selectedCampaignId = campaign.id
                                                Task { await viewModel.validateDiscount() }
                                            }
                                        }
                                    }
                                }
                            }
                            
                            if viewModel.isValidatingCoupon {
                                HStack {
                                    Spacer()
                                    ProgressView().padding()
                                    Spacer()
                                }
                            }
                        }
                    } else {
                        // Kupon Kodu
                        HStack {
                            Image(systemName: "ticket")
                                .foregroundColor(.primary)
                            TextField("Kupon Kodu", text: $viewModel.couponCode)
                                .autocapitalization(.allCharacters)
                                .disableAutocorrection(true)
                                .onChange(of: viewModel.couponCode) { _ in
                                    viewModel.isCouponValid = nil
                                    viewModel.couponMessage = ""
                                    viewModel.validatedDiscountAmount = 0.0
                                }
                            
                            if viewModel.isCouponValid == true {
                                Image(systemName: "checkmark.circle.fill")
                                    .foregroundColor(.green)
                            }
                            
                            Button(action: {
                                Task { await viewModel.validateDiscount() }
                            }) {
                                if viewModel.isValidatingCoupon {
                                    ProgressView().padding(.horizontal, 8)
                                } else {
                                    Text("Uygula")
                                        .font(.subheadline)
                                        .underline()
                                        .foregroundColor(viewModel.couponCode.isEmpty ? .gray : .yellow)
                                        .padding(.horizontal, 12)
                                        .padding(.vertical, 6)
                                }
                            }
                            .disabled(viewModel.couponCode.isEmpty || viewModel.isValidatingCoupon)
                        }
                        .padding(.horizontal, 16)
                        .padding(.vertical, 8)
                        .background(Color.gray.opacity(0.15))
                        .cornerRadius(12)
                        
                        // Kuponlarım'a yönlendiren link
                        NavigationLink(destination: CouponsView(onSelect: { code in
                            viewModel.couponCode = code
                            Task {
                                await viewModel.validateDiscount()
                            }
                        })) {
                            HStack {
                                Image(systemName: "ticket.fill")
                                Text("Kuponlarımı Gör")
                                    .fontWeight(.medium)
                                Spacer()
                                Image(systemName: "chevron.right")
                                    .font(.caption)
                            }
                            .font(.subheadline)
                            .foregroundColor(.yellow)
                            .padding(.vertical, 8)
                            .padding(.horizontal, 12)
                            .background(Color.yellow.opacity(0.1))
                            .cornerRadius(10)
                        }
                        .padding(.top, 4)
                    }
                    
                    if !viewModel.couponMessage.isEmpty {
                        Text(viewModel.couponMessage)
                            .font(.caption)
                            .foregroundColor(viewModel.isCouponValid == true ? .green : .red)
                            .padding(.horizontal, 4)
                    }
                }
                .padding(.horizontal, 24)
                .padding(.top, 16)
                
                // Butonlar
                VStack(spacing: 10) {
                    
                    // ONAYLA — DB kaydı tetiklenir
                    Button(action: onConfirm) {
                        HStack {
                            if viewModel.isLoading {
                                ProgressView().tint(.black)
                            } else {
                                Image(systemName: "checkmark.circle.fill")
                                Text("Randevuyu Onayla")
                                    .fontWeight(.bold)
                            }
                        }
                        .frame(maxWidth: .infinity)
                        .padding()
                        .background(Color.yellow)
                        .foregroundColor(.black)
                        .cornerRadius(16)
                    }
                    .disabled(viewModel.isLoading)
                }
                .padding(.horizontal, 24)
                .padding(.vertical, 20)
                
                Spacer()
            }
            .background(Color(UIColor.systemBackground).ignoresSafeArea())
        }
        
        // MARK: - Helpers
        
        private var formattedDate: String {
            let df = DateFormatter()
            df.dateFormat = "yyyy-MM-dd"
            df.locale = Locale(identifier: "tr_TR")
            let display = DateFormatter()
            display.dateFormat = "d MMMM EEEE"
            display.locale = Locale(identifier: "tr_TR")
            let str = DateManager.toString(viewModel.selectedDate)
            return df.date(from: str).map { display.string(from: $0) } ?? str
        }
        
        private func summaryRow(
            icon: String,
            label: String,
            value: String,
            valueColor: Color = .primary
        ) -> some View {
            HStack(spacing: 16) {
                Image(systemName: icon)
                    .font(.system(size: 20))
                    .foregroundColor(.yellow)
                    .frame(width: 36)
                
                Text(label)
                    .foregroundColor(.secondary)
                    .frame(width: 70, alignment: .leading)
                
                Spacer()
                
                Text(value)
                    .fontWeight(.semibold)
                    .foregroundColor(valueColor)
                    .multilineTextAlignment(.trailing)
            }
            .padding(.horizontal, 24)
            .padding(.vertical, 14)
        }
    }
}

#Preview {
    SelectAppointmentView()
        .environmentObject(AppointmentsViewModel())
}
