//
//  AppConfig.swift
//  b&vapp
//
//  Created by BVBerber
//

import Foundation

enum AppConfig {
    /// Tüm API istemcilerinin kullandığı tek URL kaynağı.
    #if targetEnvironment(simulator)
    static let apiBaseURL = "http://127.0.0.1:8000/api/v1/mobile"
    #else
    static let apiBaseURL = "http://172.20.10.3:8000/api/v1/mobile"
    #endif
}
