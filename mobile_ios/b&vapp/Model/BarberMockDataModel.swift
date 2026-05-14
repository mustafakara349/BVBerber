//
//  BarberMockDataModel.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import Foundation

struct Barber: Identifiable {
    
    let id = UUID()
    let name: String
    let image: String
}

let barbers: [Barber] = [
    
    Barber(name: "Baran V.", image: "barber1"),
    Barber(name: "Murat K.", image: "barber2"),
    Barber(name: "Sinan A.", image: "barber3"),
    Barber(name: "Caner Ö.", image: "barber4")
]
