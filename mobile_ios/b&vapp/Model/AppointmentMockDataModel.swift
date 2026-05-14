//
//  AppointmentMockDataModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

struct Appointment: Identifiable {
    let id = UUID()
    let barberName: String
    let service: String
    let date: String
    let location: String
    let status: String
    let image: String
}
