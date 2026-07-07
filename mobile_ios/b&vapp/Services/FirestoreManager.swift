//
//  FirestoreManager.swift
//  b&vapp
//
//  Created by Mustafa KARA on 29.03.2026.
//

import Foundation
import Combine

// MARK: - API Response Helpers

struct APIResponseWrapper<T: Codable>: Codable {
    var success: Bool
    var data: T
    var message: String?
}

struct APIResponseError: Codable {
    var success: Bool
    var message: String
}

struct EmptyResponse: Codable {}

// MARK: - FirestoreManager

class FirestoreManager {

    static let shared = FirestoreManager()
    
    // Base URL configuration - change this to production API URL if needed
    let baseURL = "http://192.168.0.2:8000/api/v1/mobile"

    private init() {}

    // MARK: - Generic REST Request Sender

    private func sendRequest<R: Codable>(
        path: String,
        method: String = "GET",
        body: Data? = nil
    ) async throws -> R {
        guard let url = URL(string: "\(baseURL)\(path)") else {
            throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid URL"])
        }

        var request = URLRequest(url: url)
        request.httpMethod = method
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        request.setValue("application/json", forHTTPHeaderField: "Accept")

        // Attach sanctum token if available
        if let token = UserDefaults.standard.string(forKey: "auth_token") {
            request.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization")
        }

        request.httpBody = body

        let (data, response) = try await URLSession.shared.data(for: request)

        guard let httpResponse = response as? HTTPURLResponse else {
            throw NSError(domain: "", code: -1, userInfo: [NSLocalizedDescriptionKey: "Invalid response"])
        }

        // Handle errors
        if !(200...299).contains(httpResponse.statusCode) {
            let errorMsg = String(data: data, encoding: .utf8) ?? "HTTP Error \(httpResponse.statusCode)"
            // Try parsing API error structure if exists
            if let apiError = try? JSONDecoder().decode(APIResponseError.self, from: data) {
                throw NSError(domain: "", code: httpResponse.statusCode, userInfo: [NSLocalizedDescriptionKey: apiError.message])
            }
            throw NSError(domain: "", code: httpResponse.statusCode, userInfo: [NSLocalizedDescriptionKey: errorMsg])
        }

        // Parse the generic API success wrapper
        let decoder = JSONDecoder()

        let formatter = DateFormatter()
        formatter.locale = Locale(identifier: "en_US_POSIX")

        decoder.dateDecodingStrategy = .custom { decoder -> Date in
            let container = try decoder.singleValueContainer()
            let dateStr = try container.decode(String.self)

            // Try ISO8601 with fractional seconds (3 digits or 6 digits)
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSS'Z'"
            if let date = formatter.date(from: dateStr) { return date }
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSZ"
            if let date = formatter.date(from: dateStr) { return date }
            
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSSSS'Z'"
            if let date = formatter.date(from: dateStr) { return date }
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss.SSSSSSZ"
            if let date = formatter.date(from: dateStr) { return date }

            // Try ISO8601 with Z
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ss'Z'"
            if let date = formatter.date(from: dateStr) { return date }
            formatter.dateFormat = "yyyy-MM-dd'T'HH:mm:ssZ"
            if let date = formatter.date(from: dateStr) { return date }

            // Try standard Laravel datetime
            formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
            if let date = formatter.date(from: dateStr) { return date }

            // Try date only
            formatter.dateFormat = "yyyy-MM-dd"
            if let date = formatter.date(from: dateStr) { return date }

            throw DecodingError.dataCorruptedError(in: container, debugDescription: "Cannot decode date string \(dateStr)")
        }

        let wrapper = try decoder.decode(APIResponseWrapper<R>.self, from: data)
        return wrapper.data
    }

    // MARK: - Fetch Collection

    /// Birden fazla whereField koşulu destekler.
    func fetchCollection<T: Codable>(
        _ collection: String,
        whereFields: [(field: String, value: Any)] = [],
        orderBy: String? = nil,
        descending: Bool = true
    ) async throws -> [T] {
        // Map where conditions to JSON-friendly structures
        let queryParams: [[String: String]] = whereFields.map {
            let valStr = "\($0.value)"
            return ["field": $0.field, "value": valStr]
        }

        let queryPayload: [String: Any] = [
            "collection": collection,
            "where": queryParams,
            "orderBy": orderBy ?? "",
            "descending": descending
        ]

        let jsonData = try JSONSerialization.data(withJSONObject: queryPayload)
        return try await sendRequest(path: "/query", method: "POST", body: jsonData)
    }

    // MARK: - Fetch Single Document

    func fetchDocument<T: Codable>(
        _ collection: String,
        documentId: String
    ) async throws -> T? {
        do {
            let result: T = try await sendRequest(path: "/document/\(collection)/\(documentId)", method: "GET")
            return result
        } catch {
            let nsError = error as NSError
            if nsError.code == 404 {
                return nil
            }
            throw error
        }
    }

    // MARK: - Add Document (otomatik ID)

    func addDocument(
        _ collection: String,
        data: [String: Any]
    ) async throws -> String {
        var cleanData = data
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"
        
        for (key, val) in data {
            if let dateVal = val as? Date {
                cleanData[key] = formatter.string(from: dateVal)
            }
        }

        let jsonData = try JSONSerialization.data(withJSONObject: cleanData)

        struct IDResponse: Codable {
            var id: String
        }

        let response: IDResponse = try await sendRequest(path: "/document/\(collection)", method: "POST", body: jsonData)
        return response.id
    }

    // MARK: - Update Document

    func updateDocument(
        _ collection: String,
        documentId: String,
        data: [String: Any]
    ) async throws {
        var cleanData = data
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"

        for (key, val) in data {
            if let dateVal = val as? Date {
                cleanData[key] = formatter.string(from: dateVal)
            }
        }

        let jsonData = try JSONSerialization.data(withJSONObject: cleanData)
        let _: EmptyResponse = try await sendRequest(path: "/document/\(collection)/\(documentId)", method: "PUT", body: jsonData)
    }

    // MARK: - Delete Document

    func deleteDocument(
        _ collection: String,
        documentId: String
    ) async throws {
        let _: EmptyResponse = try await sendRequest(path: "/document/\(collection)/\(documentId)", method: "DELETE")
    }

    // MARK: - Set Document (özel ID)

    func setDocument(
        _ collection: String,
        documentId: String,
        data: [String: Any],
        merge: Bool = false
    ) async throws {
        var cleanData = data
        cleanData["merge"] = merge
        let formatter = DateFormatter()
        formatter.dateFormat = "yyyy-MM-dd HH:mm:ss"

        for (key, val) in data {
            if let dateVal = val as? Date {
                cleanData[key] = formatter.string(from: dateVal)
            }
        }

        let jsonData = try JSONSerialization.data(withJSONObject: cleanData)
        let _: EmptyResponse = try await sendRequest(path: "/document/\(collection)/\(documentId)", method: "PUT", body: jsonData)
    }
}
