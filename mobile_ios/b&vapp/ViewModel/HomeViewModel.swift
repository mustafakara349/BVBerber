//
//  HomeViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import MapKit
import Combine

enum ServicesNavigationAction {
    case barber(Service)
    case cafe(CafeProduct)
    case product(SaleProduct)
}

@MainActor
class HomeViewModel: ObservableObject {

    @Published var pendingNavigationAction: ServicesNavigationAction? = nil

    @Published var services: [Service] = []
    @Published var barbers: [Barber] = []
    @Published var campaigns: [Campaign] = []
    @Published var user: UserModel? = nil
    @Published var isLoading = false

    private let db = APIClient.shared

    @Published var cafeProducts: [CafeProduct] = []
    @Published var saleProducts: [SaleProduct] = []

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
            async let cafeProductsTask: [CafeProduct] = db.fetchPublicList("cafe-products")
            async let saleProductsTask: [SaleProduct] = db.fetchPublicList("sale-products")

            let (fetchedServices, fetchedBarbers, fetchedCampaigns, fetchedCafe, fetchedSale) = try await (servicesTask, barbersTask, campaignsTask, cafeProductsTask, saleProductsTask)

            services = fetchedServices
            barbers = fetchedBarbers.filter { $0.isAvailable }
            campaigns = fetchedCampaigns
            cafeProducts = fetchedCafe
            saleProducts = fetchedSale

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
