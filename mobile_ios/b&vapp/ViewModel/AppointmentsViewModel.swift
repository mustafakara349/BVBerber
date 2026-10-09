//
//  AppointmentsViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import MapKit
import Combine

enum DiscountMode {
    case campaign
    case coupon
}

@MainActor
class AppointmentsViewModel: ObservableObject {

    // MARK: - Randevular
    @Published var upcomingAppointments: [Appointment] = []
    @Published var pastAppointments: [Appointment] = []
    @Published var selectedTab = 0
    @Published var appointmentToCancel: Appointment? = nil
    @Published var showCancelAlert = false

    // MARK: - Misafir (Guest) Durumu
    @Published var isGuestMode: Bool = false
    @Published var guestFirstName: String = ""
    @Published var guestLastName: String = ""
    @Published var guestPhone: String = ""

    // MARK: - Randevu Oluşturma Akışı
    @Published var barbers: [Barber] = []
    @Published var services: [Service] = []
    @Published var store: StoreModel? = nil

    @Published var selectedBarber: Barber? = nil
    @Published var selectedService: Service? = nil
    @Published var selectedServices: Set<Service> = []
    @Published var selectedDate: Date = Date()
    @Published var selectedTime: String? = nil
    @Published var couponCode: String = ""
    @Published var validatedDiscountAmount: Double = 0.0
    @Published var isCouponValid: Bool? = nil
    @Published var couponMessage: String = ""
    @Published var isValidatingCoupon: Bool = false
    
    @Published var discountMode: DiscountMode = .campaign
    @Published var availableCampaigns: [Campaign] = []
    @Published var selectedCampaignId: String? = nil
    
    // Kampanya Ödülü Bilgileri
    @Published var validatedRewardType: String? = nil
    @Published var validatedRewardProductId: String? = nil
    @Published var validatedRewardCafeProductId: String? = nil
    @Published var validatedRewardProductName: String? = nil

    @Published var availableDates: [Date] = []
    @Published var blockedSlots: [String] = []      // barberAvailability.blockedSlots + aktif randevular
    @Published var barberAvailabilityMap: [String: BarberAvailability] = [:]

    // MARK: - Loading
    @Published var isLoading = false
    @Published var isLoadingAvailability = false

    // MARK: - Alert
    @Published var alertTitle = ""
    @Published var alertMessage = ""
    @Published var showAlert = false

    private let db = APIClient.shared

    // MARK: - Helpers

    init() {
        NotificationCenter.default.addObserver(
            forName: .refreshAppointments,
            object: nil,
            queue: .main
        ) { [weak self] _ in
            Task {
                await self?.fetchAppointments()
            }
        }
    }

    private func showError(title: String = "Hata", message: String) {
        alertTitle = title
        alertMessage = message
        showAlert = true
    }

    // MARK: - Computed

    var selectedDateString: String {
        DateManager.toString(selectedDate)
    }

    private func fetchCampaigns() async {
        do {
            let all: [Campaign] = try await db.fetchCollection("campaigns")
            self.availableCampaigns = all.filter { $0.type == "auto_apply" && $0.isActive }
        } catch {
            showError(message: "Kampanyalar yüklenemedi: \(error.localizedDescription)")
        }
    }

    // MARK: - Required Slots
    var totalServiceDuration: Int {
        selectedServices.reduce(0) { $0 + $1.duration }
    }
    
    var requiredSlotsCount: Int {
        let count = Int(ceil(Double(totalServiceDuration) / 30.0))
        return max(1, count)
    }

    func isSlotDisabled(time: String) -> Bool {
        if TimeManager.isPastTime(selectedDate: selectedDate, time: time) { return true }
        
        let allSlots = currentTimeSlots
        guard let startIndex = allSlots.firstIndex(of: time) else { return true }
        
        let requiredCount = requiredSlotsCount
        for i in 0..<requiredCount {
            if startIndex + i >= allSlots.count {
                return true // Not enough slots remaining
            }
            let checkTime = allSlots[startIndex + i]
            if blockedSlots.contains(checkTime) {
                return true
            }
        }
        return false
    }

    /// Seçili berberin çalışma saatleri ve uygunluk durumue slot listesi
    var currentTimeSlots: [String] {
        let dayName = DateManager.weekdayName(from: selectedDate)
        return TimeManager.generateSlots(for: store?.workingHours?[dayName])
    }

    // MARK: - Randevuları Getir

    func fetchAppointments() async {
        guard let userId = AuthManager.shared.currentUserId else { return }

        if upcomingAppointments.isEmpty && pastAppointments.isEmpty {
            isLoading = true
        }

        do {
            let all: [Appointment] = try await db.fetchCollection(
                "appointments",
                whereFields: [("userId", userId)]
            )

            upcomingAppointments = all
                .filter { $0.isUpcoming }
                .sorted {
                    if $0.date != $1.date { return $0.date < $1.date }
                    return $0.time < $1.time
                }

            pastAppointments = all
                .filter { !$0.isUpcoming }
                .sorted {
                    if $0.date != $1.date { return $0.date > $1.date }
                    return $0.time > $1.time
                }

        } catch {
            showError(message: "Randevular yüklenemedi: \(error.localizedDescription)")
        }

        isLoading = false
    }

    // MARK: - Randevu Oluşturma Verilerini Getir

    func fetchBookingData() async {
        await withTaskGroup(of: Void.self) { group in
            group.addTask { await self.fetchStore() }
            group.addTask { await self.fetchServicesForBooking() }
            group.addTask { await self.fetchBarbers() }
            group.addTask { await self.fetchCampaigns() }
        }
    }

    func fetchStore() async {
        do {
            store = try await db.fetchDocument("store", documentId: "main")
        } catch {
            showError(message: "Mağaza bilgisi yüklenemedi: \(error.localizedDescription)")
        }
    }

    func fetchBarbers() async {
        do {
            let all: [Barber] = try await db.fetchCollection(
                "barbers",
                whereFields: [("isActive", true)]
            )
            barbers = all.filter { $0.isAvailable }

            if selectedBarber == nil, let first = barbers.first {
                selectedBarber = first
                await fetchBarberAvailability()
            }
        } catch {
            showError(message: "Berberler yüklenemedi: \(error.localizedDescription)")
        }
    }

    func fetchServicesForBooking() async {
        do {
            let all: [Service] = try await db.fetchCollection(
                "services",
                whereFields: [("isActive", true)]
            )
            services = all
            if selectedService == nil { selectedService = all.first }
            if selectedServices.isEmpty, let first = all.first {
                selectedServices = [first]
            }
        } catch {
            showError(message: "Hizmetler yüklenemedi: \(error.localizedDescription)")
        }
    }

    // MARK: - Berber Müsaitlik

    /// Berber değiştiğinde çağrılır
    func onBarberChanged() {
        selectedServices.removeAll()
        selectedDate = Date()
        selectedTime = nil
        couponCode = ""
        validatedDiscountAmount = 0.0
        isCouponValid = nil
        couponMessage = ""
        selectedCampaignId = nil
        discountMode = .campaign
        availableDates = []
        blockedSlots = []
        barberAvailabilityMap = [:]
        Task { await fetchBarberAvailability() }
    }

    func fetchBarberAvailability() async {
        guard let barber = selectedBarber, let barberId = barber.id else {
            availableDates = []
            return
        }

        isLoadingAvailability = true

        do {
            let availabilities: [BarberAvailability] = try await db.fetchCollection(
                "barberAvailability",
                whereFields: [("barberId", barberId)]
            )

            // Harita: date → BarberAvailability
            barberAvailabilityMap = Dictionary(
                availabilities.map { ($0.date, $0) },
                uniquingKeysWith: { first, _ in first }
            )

            computeAvailableDates(for: barber)
            await fetchBlockedSlots()

        } catch {
            showError(message: "Müsaitlik bilgisi yüklenemedi: \(error.localizedDescription)")
        }

        isLoadingAvailability = false
    }

    /// Berber's workingDays + store closed days + barberAvailability.fullDayOff baz alınarak müsait günler hesaplanır
    private func computeAvailableDates(for barber: Barber) {
        let calendar = Calendar.current
        let today = Date()
        let maxDays = store?.settings?.maxBookingDaysAhead ?? 30

        let dates: [Date] = (0..<maxDays).compactMap { offset -> Date? in
            guard let date = calendar.date(byAdding: .day, value: offset, to: today) else { return nil }

            let dayName = DateManager.weekdayName(from: date)

            // Berber bu gün çalışıyor mu?
            guard barber.workingDays.contains(dayName) else { return nil }

            // Mağaza bu gün kapalı mı?
            if let workingDay = store?.workingHours?[dayName], workingDay.closed == true {
                return nil
            }

            // barberAvailability: fullDayOff var mı?
            let dateStr = DateManager.toString(date)
            if let avail = barberAvailabilityMap[dateStr], avail.fullDayOff == true {
                return nil
            }

            return date
        }

        availableDates = dates

        // Seçili tarih artık geçerli değilse birinci müsait güne sıfırla
        let isSelectedDateValid = dates.contains {
            calendar.isDate($0, inSameDayAs: selectedDate)
        }
        if !isSelectedDateValid, let first = dates.first {
            selectedDate = first
        }
    }

    // MARK: - Tarih Değişince

    func onDateChanged() {
        selectedTime = nil
        Task { await fetchBlockedSlots() }
    }

    // MARK: - Bloke Saatler

    func fetchBlockedSlots() async {
        guard let barber = selectedBarber, let barberId = barber.id else {
            blockedSlots = []
            return
        }

        let dateStr = selectedDateString
        var blocked: [String] = []

        // 1) barberAvailability.blockedSlots
        if let avail = barberAvailabilityMap[dateStr] {
            blocked += avail.effectiveBlockedSlots
        }

        // 2) Aynı berberin aynı gündeki aktif randevuları
        do {
            let existingAppts: [Appointment] = try await db.fetchCollection(
                "appointments",
                whereFields: [("barberId", barberId), ("date", dateStr)]
            )
            let bookedTimes = existingAppts
                .filter { $0.isActive }
                .map { $0.time }
            blocked += bookedTimes
        } catch {
            showError(message: "Randevu kontrol hatası: \(error.localizedDescription)")
        }

        blockedSlots = Array(Set(blocked)) // tekrarları temizle
    }

    // MARK: - Randevu Oluştur

    func createAppointment() async -> Bool {
        guard let userId = AuthManager.shared.currentUserId else { return false }
        guard let barber = selectedBarber, let barberId = barber.id else {
            showError(title: "Hata", message: "Lütfen bir berber seçin.")
            return false
        }
        guard !selectedServices.isEmpty else {
            showError(title: "Hata", message: "Lütfen en az bir hizmet seçin.")
            return false
        }
        guard let time = selectedTime else {
            showError(title: "Hata", message: "Lütfen bir saat seçin.")
            return false
        }
        
        isLoading = true
        defer { isLoading = false }

        // MARK: Veritabanı Çakışma Kontrolü
        // Kaydetmeden önce seçilen berber + tarih + saat için aktif kayıt var mı kontrol et
        do {
            let conflicting: [Appointment] = try await db.fetchCollection(
                "appointments",
                whereFields: [
                    ("barberId", barberId),
                    ("date", selectedDateString),
                    ("time", time),
                    ("status", "active")
                ]
            )
            if !conflicting.isEmpty {
                showError(
                    title: "Saat Dolu",
                    message: "Bu saat müsait değildir, lütfen farklı bir saat seçiniz."
                )
                // Bloke listesini de güncelle, kullanıcı anında görsün
                await fetchBlockedSlots()
                return false
            }
        } catch {
            // Kontrol hatası durumunda kaydetmeyi engelleme; sunucu tarafı catch eder
            print("Çakışma kontrolü hatası: \(error.localizedDescription)")
        }

        let serviceIds = Array(selectedServices.compactMap { $0.id })
        let serviceNames = selectedServices.map { $0.name }.joined(separator: " + ")
        let totalPrice = selectedServices.reduce(0) { $0 + $1.effectivePrice }

        var data: [String: Any] = [
            "userId": userId,
            "barberId": barberId,
            "barberName": barber.fullName,
            "serviceId": serviceIds.first ?? "",
            "serviceIds": serviceIds,
            "serviceName": serviceNames,
            "date": selectedDateString,
            "time": time,
            "price": totalPrice,
            "status": "active"
        ]

        if discountMode == .coupon && !couponCode.isEmpty {
            data["couponCode"] = couponCode
        } else if discountMode == .campaign, let cid = selectedCampaignId {
            data["campaignId"] = cid
        }

        do {
            _ = try await db.addDocument("appointments", data: data)
            await fetchAppointments()
            return true
        } catch {
            showError(title: "Hata", message: "Randevu oluşturulamadı: \(error.localizedDescription)")
            return false
        }
    }

    // MARK: - Misafir Randevusu Oluştur
    func createGuestAppointment() async -> Bool {
        guard let barber = selectedBarber, let barberId = barber.id else {
            showError(title: "Hata", message: "Lütfen bir berber seçin.")
            return false
        }
        guard !selectedServices.isEmpty else {
            showError(title: "Hata", message: "Lütfen en az bir hizmet seçin.")
            return false
        }
        guard let time = selectedTime else {
            showError(title: "Hata", message: "Lütfen bir saat seçin.")
            return false
        }

        let serviceIds = Array(selectedServices.compactMap { $0.id })
        
        let data: [String: Any] = [
            "firstName": guestFirstName,
            "lastName": guestLastName,
            "phone": guestPhone,
            "barberId": Int(barberId) ?? 0,
            "serviceIds": serviceIds.compactMap { Int($0) },
            "date": selectedDateString,
            "time": time
        ]
        
        isLoading = true
        defer { isLoading = false }
        
        do {
            _ = try await db.postData(to: "guest-appointment", data: data)
            return true
        } catch {
            showError(title: "Hata", message: "Randevu oluşturulamadı: \(error.localizedDescription)")
            return false
        }
    }

    // MARK: - İndirim Doğrulama

    func validateDiscount() async {
        let isUsingCoupon = discountMode == .coupon
        let codeToValidate = isUsingCoupon ? couponCode : nil
        let campaignToValidate = isUsingCoupon ? nil : selectedCampaignId
        
        if isUsingCoupon && (codeToValidate ?? "").isEmpty {
            isCouponValid = nil
            couponMessage = "Lütfen bir kupon kodu girin."
            validatedDiscountAmount = 0.0
            return
        }
        
        if !isUsingCoupon && (campaignToValidate ?? "").isEmpty {
            isCouponValid = nil
            couponMessage = "Lütfen bir kampanya seçin."
            validatedDiscountAmount = 0.0
            return
        }

        isValidatingCoupon = true
        isCouponValid = nil
        couponMessage = ""
        validatedDiscountAmount = 0.0
        validatedRewardType = nil
        validatedRewardProductId = nil
        validatedRewardCafeProductId = nil
        validatedRewardProductName = nil

        let subtotal = Double(selectedServices.reduce(0) { $0 + $1.effectivePrice })
        let serviceIds = Array(selectedServices.compactMap { Int($0.id ?? "0") ?? 0 })

        do {
            let response = try await db.validateCoupon(code: codeToValidate, campaignId: campaignToValidate, subtotal: subtotal, serviceIds: serviceIds)
            isCouponValid = response.isValid
            validatedDiscountAmount = response.discountAmount
            couponMessage = response.message
            validatedRewardType = response.rewardType
            validatedRewardProductId = response.rewardProductId
            validatedRewardCafeProductId = response.rewardCafeProductId
            validatedRewardProductName = response.rewardProductName
        } catch {
            isCouponValid = false
            let nsError = error as NSError
            couponMessage = nsError.userInfo[NSLocalizedDescriptionKey] as? String ?? "Kupon doğrulanamadı."
            validatedDiscountAmount = 0.0
        }

        isValidatingCoupon = false
    }

    // MARK: - Randevu İptal

    func cancelAppointment(_ appointment: Appointment) async {
        guard let appointmentId = appointment.id else { return }

        do {
            // Silmek yerine status'u "cancelled" yapıyoruz;
            // updateDocument updatedAt'i otomatik ekler
            try await db.updateDocument(
                "appointments",
                documentId: appointmentId,
                data: ["status": "cancelled"]
            )
            await fetchAppointments()
        } catch {
            showError(title: "Hata", message: "Randevu iptal edilemedi.")
        }
    }

    // MARK: - Haritayı Aç

    func openMaps() {
        let latitude: CLLocationDegrees = 36.9238616
        let longitude: CLLocationDegrees = 34.9011379
        let coordinate = CLLocationCoordinate2D(latitude: latitude, longitude: longitude)
        let mapItem = MKMapItem(placemark: MKPlacemark(coordinate: coordinate))
        mapItem.name = store?.name ?? "B&V Coffee Barber"
        mapItem.openInMaps()
    }

    // MARK: - Seçimi Sıfırla

    func resetSelection() {
        selectedBarber = barbers.first
        if let first = services.first {
            selectedServices = [first]
        } else {
            selectedServices = []
        }
        selectedDate = availableDates.first ?? Date()
        selectedTime = nil
        couponCode = ""
        validatedDiscountAmount = 0.0
        isCouponValid = nil
        couponMessage = ""
        selectedCampaignId = nil
        blockedSlots = []
        Task { await fetchBlockedSlots() }
    }

}
