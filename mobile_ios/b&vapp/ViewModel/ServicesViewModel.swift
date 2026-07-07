//
//  ServicesViewModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import Combine

@MainActor
class ServicesViewModel: ObservableObject {

    @Published var services: [Service] = []
    @Published var isLoading = false

    private let db = FirestoreManager.shared

    // MARK: - Fetch Services

    func fetchServices() async {
        if services.isEmpty { isLoading = true }

        do {
            services = try await db.fetchCollection(
                "services",
                whereFields: [("isActive", true)]
            )
        } catch {
            print("Hizmetler yüklenemedi: \(error.localizedDescription)")
        }

        isLoading = false
    }
}
