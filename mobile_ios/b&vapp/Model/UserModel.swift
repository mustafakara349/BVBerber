//
//  UserModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation
import Combine

struct UserModel: Identifiable, Codable {

    var id: String?

    var email: String
    var isActive: Bool
    var name: String
    var surname: String
    var phone: String
    var profileImageUrl: String
    var role: String            // "customer", "admin", "barber"

    var fcmToken: String?

    var createdAt: Date?
    var updatedAt: Date?

    // MARK: - Computed
    var fullName: String {
        "\(name) \(surname)".trimmingCharacters(in: .whitespaces)
    }

    var initials: String {
        let parts = fullName.split(separator: " ")
        return parts.prefix(2).compactMap { $0.first }.map(String.init).joined()
    }
}
