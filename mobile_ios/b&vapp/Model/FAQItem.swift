//
//  FAQItem.swift
//  b&vapp
//
//  Created by Mustafa KARA on 28.03.2026.
//

import Foundation

struct FAQItem: Identifiable {
    let id = UUID()
    let question: String
    let answer: String
}
