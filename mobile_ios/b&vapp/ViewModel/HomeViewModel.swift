//
//  HomeViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import MapKit
import Combine

@MainActor
class HomeViewModel: ObservableObject {

    @Published var services: [Service] = []
    @Published var barbers: [Barber] = []
    @Published var campaigns: [Campaign] = []
    @Published var user: UserModel? = nil
    @Published var isLoading = false

    private let db = FirestoreManager.shared

    // MARK: - Computed

    var userName: String { user?.name ?? "Kullanıcı" }

    // MARK: - Fetch Data

    func fetchHomeData() async {
        if services.isEmpty && barbers.isEmpty && campaigns.isEmpty {
            isLoading = true
        }

        do {
            async let servicesTask: [Service] = db.fetchCollection(
                "services",
                whereFields: [("isActive", true)]
            )
            async let barbersTask: [Barber] = db.fetchCollection(
                "barbers",
                whereFields: [("isActive", true)]
            )
            async let campaignsTask: [Campaign] = db.fetchCollection(
                "campaigns"
            )

            let (fetchedServices, fetchedBarbers, fetchedCampaigns) = try await (servicesTask, barbersTask, campaignsTask)

            services = fetchedServices
            barbers = fetchedBarbers.filter { $0.isAvailable }
            campaigns = fetchedCampaigns

            if let userId = AuthManager.shared.currentUserId {
                user = try await db.fetchDocument("users", documentId: userId)
            }

        } catch {
            print("Ana sayfa verileri yüklenemedi: \(error.localizedDescription)")
        }

        isLoading = false
    }

    // MARK: - Open Maps

    func openMaps() {
        let coordinate = CLLocationCoordinate2D(latitude: 36.9238616, longitude: 34.9011379)
        let mapItem = MKMapItem(placemark: MKPlacemark(coordinate: coordinate))
        mapItem.name = "B&V Coffee Barber"
        mapItem.openInMaps()
    }
}
