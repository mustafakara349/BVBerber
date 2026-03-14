//
//  SelectAppointmentView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 14.03.2026.
//

import SwiftUI

struct SelectAppointmentView: View {
    
    @Environment(\.dismiss) var dismiss
    
    // MARK: - STATE
    
    @State private var selectedDate: Date = Date()
    @State private var selectedBarber: Barber? = barbers.first
    @State private var selectedTime: String? = nil
    
    // MARK: - DATA
    
    let dates = DateManager.generateDates()
    let times = TimeManager.generateTimeSlots()
    
    var body: some View {
        
        VStack(spacing: 0) {
            
            // HEADER
            HStack {
                
                Button {
                    dismiss()
                } label: {
                    Image(systemName: "chevron.left")
                        .font(.title3)
                        .foregroundColor(.white)
                }
                
                Spacer()
                
                Text("Randevu Oluştur")
                    .font(.headline)
                    .fontWeight(.semibold)
                
                Spacer()
                
                Circle()
                    .fill(Color.clear)
                    .frame(width: 24)
            }
            .padding()
            
            
            ScrollView {
                
                VStack(alignment: .leading, spacing: 24) {
                    
                    // DATE SECTION
                    
                    HStack {
                        Text("Tarih Seçin")
                            .font(.title3)
                            .fontWeight(.semibold)
                        
                        Spacer()
                        
                        Text(DateManager.monthYearString(from: selectedDate))
                            .foregroundColor(.yellow)
                    }
                    
                    ScrollView(.horizontal, showsIndicators: false) {
                        HStack(spacing: 12) {
                            
                            ForEach(dates, id:\.self) { date in
                                
                                DateCard(
                                    date: date,
                                    isSelected: Calendar.current.isDate(date, inSameDayAs: selectedDate)
                                )
                                .onTapGesture {
                                    selectedDate = date
                                    selectedTime = nil
                                }
                            }
                        }
                    }
                    
                    
                    // PERSONEL
                    
                    Text("Personel Seç")
                        .font(.title3)
                        .fontWeight(.semibold)
                    
                    ScrollView(.horizontal, showsIndicators: false) {
                        HStack(spacing: 18) {
                            
                            ForEach(barbers) { barber in
                                
                                BarberCard(
                                    barber: barber,
                                    isSelected: barber.id == selectedBarber?.id
                                )
                                .onTapGesture {
                                    selectedBarber = barber
                                }
                            }
                        }
                    }
                    
                    
                    // TIME
                    
                    HStack {
                        
                        Text("Saat Seçin")
                            .font(.title3)
                            .fontWeight(.semibold)
                        
                        Spacer()
                        
                        HStack(spacing: 6){
                            Circle()
                                .frame(width: 8,height: 8)
                                .foregroundColor(.yellow)
                            
                            Text("DOLU")
                                .font(.caption)
                                .foregroundColor(.gray)
                        }
                    }
                    
                    
                    LazyVGrid(
                        columns: Array(repeating: GridItem(.flexible()), count: 4),
                        spacing: 14
                    ) {
                        
                        ForEach(times, id:\.self) { time in
                            
                            let disabled = TimeManager.isPastTime(
                                selectedDate: selectedDate,
                                time: time
                            )
                            
                            TimeCard(
                                time: time,
                                isSelected: selectedTime == time,
                                disabled: disabled
                            )
                            .onTapGesture {
                                if !disabled {
                                    selectedTime = time
                                }
                            }
                        }
                    }
                    
                }
                .padding()
            }
            
            
            // BOTTOM BUTTON
            
            Button {
                
            } label: {
                
                Text("Randevuyu Onayla")
                    .font(.title3)
                    .fontWeight(.bold)
                    .frame(maxWidth:.infinity)
                    .padding()
                    .background(Color.yellow)
                    .foregroundColor(.black)
                    .cornerRadius(16)
            }
            .padding()
            
        }
        .background(Color.black)
        .foregroundColor(.white)
        .toolbar(.hidden, for: .tabBar)   // TAB BAR GİZLER
        .navigationBarBackButtonHidden(true)
    }
}


struct DateManager {
    
    static func generateDates() -> [Date] {
        
        let today = Date()
        let calendar = Calendar.current
        
        return (0..<15).compactMap {
            calendar.date(byAdding: .day, value: $0, to: today)
        }
    }
    
    
    static func monthYearString(from date: Date) -> String {
        
        let formatter = DateFormatter()
        formatter.dateFormat = "LLLL yyyy"
        formatter.locale = Locale(identifier: "tr_TR")
        
        return formatter.string(from: date)
    }
    
    
    static func dayString(from date: Date) -> String {
        
        let formatter = DateFormatter()
        formatter.dateFormat = "EEE"
        formatter.locale = Locale(identifier: "tr_TR")
        
        return formatter.string(from: date).uppercased()
    }
    
    
    static func dayNumber(from date: Date) -> String {
        
        let formatter = DateFormatter()
        formatter.dateFormat = "d"
        
        return formatter.string(from: date)
    }
}

struct TimeManager {
    
    static func generateTimeSlots() -> [String] {
        
        [
            "09:00","09:30","10:00","10:30",
            "11:00","11:30","12:00","12:30",
            "13:00","13:30","14:00","14:30",
            "15:00","15:30","16:00","16:30"
        ]
    }
    
    
    static func isPastTime(selectedDate: Date, time: String) -> Bool {
        
        let calendar = Calendar.current
        
        // sadece bugün için kontrol yap
        if !calendar.isDateInToday(selectedDate) {
            return false
        }
        
        let formatter = DateFormatter()
        formatter.dateFormat = "HH:mm"
        
        guard let slotTime = formatter.date(from: time) else { return false }
        
        let now = Date()
        
        let components = calendar.dateComponents([.hour,.minute], from: slotTime)
        
        let slotDate = calendar.date(
            bySettingHour: components.hour!,
            minute: components.minute!,
            second: 0,
            of: now
        )!
        
        return slotDate < now
    }
}

struct DateCard: View {
    
    let date: Date
    let isSelected: Bool
    
    var body: some View {
        
        VStack(spacing:4) {
            
            Text(DateManager.dayString(from: date))
                .font(.caption)
            
            Text(DateManager.dayNumber(from: date))
                .font(.title2)
                .fontWeight(.bold)
        }
        .frame(width:70,height:80)
        .background(
            isSelected ? Color.yellow : Color(.systemGray6).opacity(0.2)
        )
        .foregroundColor(isSelected ? .black : .white)
        .cornerRadius(14)
    }
}

struct BarberCard: View {
    
    let barber: Barber
    let isSelected: Bool
    
    var body: some View {
        
        VStack {
            
            Image(barber.image)
                .resizable()
                .scaledToFill()
                .frame(width:70,height:70)
                .clipShape(Circle())
                .overlay(
                    Circle()
                        .stroke(isSelected ? Color.yellow : Color.clear, lineWidth:3)
                )
            
            Text(barber.name)
                .font(.caption)
        }
    }
}

struct TimeCard: View {
    
    let time: String
    let isSelected: Bool
    let disabled: Bool
    
    var body: some View {
        
        Text(time)
            .fontWeight(.semibold)
            .frame(maxWidth:.infinity)
            .padding()
            .background(
                disabled ? Color.gray.opacity(0.15) :
                isSelected ? Color.yellow :
                Color(.systemGray6).opacity(0.2)
            )
            .foregroundColor(
                disabled ? .gray :
                isSelected ? .black : .white
            )
            .cornerRadius(10)
    }
}


#Preview {
    SelectAppointmentView()
}
