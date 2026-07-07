//
//  SupportView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 28.03.2026.
//

import SwiftUI

struct SupportView: View {
    
    @Environment(\.dismiss) var dismiss
    
    let faqItems: [FAQItem] = [
        FAQItem(
            question: "Randevu nasıl alabilirim?",
            answer: "Ana sayfadaki 'Hemen Randevu Al' butonuna veya alt menüdeki Randevular sekmesindeki '+' ikonuna tıklayarak tarih, personel ve saat seçerek kolayca randevu oluşturabilirsiniz."
        ),
        FAQItem(
            question: "Randevumu nasıl iptal edebilirim?",
            answer: "Randevular sekmesindeki yaklaşan randevunuzun altındaki 'İptal Et' butonuna tıklayarak randevunuzu iptal edebilirsiniz."
        ),
        FAQItem(
            question: "Şifremi nasıl değiştirebilirim?",
            answer: "Profil sayfasındaki 'Güvenlik ve Giriş' bölümüne giderek mevcut şifrenizi girip yeni şifrenizi belirleyebilirsiniz."
        ),
        FAQItem(
            question: "Hangi hizmetleri sunuyorsunuz?",
            answer: "Saç kesimi, sakal tıraşı, cilt bakımı, saç boyama gibi birçok profesyonel bakım hizmeti sunuyoruz. Tüm hizmetlerimizi alt menüdeki Hizmetler sekmesinden inceleyebilirsiniz."
        ),
        FAQItem(
            question: "Ödeme yöntemleri nelerdir?",
            answer: "Hizmet bedelini randevu sonrasında nakit veya kredi/banka kartı ile ödeyebilirsiniz. Uygulama üzerinden online ödeme şu an aktif değildir."
        ),
        FAQItem(
            question: "Çalışma saatleriniz nedir?",
            answer: "Salonumuz her gün 09:00 - 21:00 saatleri arasında hizmet vermektedir. Resmi tatillerde çalışma saatlerimiz farklılık gösterebilir."
        )
    ]
    
    var body: some View {
        
        VStack(spacing: 0) {
            
            // HEADER
            HStack {
                
                Button {
                    dismiss()
                } label: {
                    Image(systemName: "chevron.left")
                        .font(.title3)
                        .foregroundColor(.primary)
                }
                
                Spacer()
                
                Text("Destek ve Yardım")
                    .font(.headline)
                    .fontWeight(.semibold)
                
                Spacer()
                
                Circle()
                    .fill(Color.clear)
                    .frame(width: 24)
            }
            .padding()
            
            
            ScrollView {
                
                VStack(spacing: 20) {
                    
                    // SSS BAŞLIĞI
                    HStack {
                        
                        ZStack {
                            RoundedRectangle(cornerRadius: 12)
                                .fill(Color.yellow.opacity(0.15))
                                .frame(width: 44, height: 44)
                            
                            Image(systemName: "questionmark.bubble.fill")
                                .foregroundColor(.yellow)
                        }
                        
                        VStack(alignment: .leading, spacing: 2) {
                            Text("Sıkça Sorulan Sorular")
                                .font(.headline)
                                .foregroundColor(.primary)
                            
                            Text("Merak ettiğiniz soruların cevapları")
                                .font(.caption)
                                .foregroundColor(.secondary)
                        }
                        
                        Spacer()
                    }
                    .padding(.horizontal)
                    
                    
                    // SSS KARTLARI
                    VStack(spacing: 12) {
                        ForEach(faqItems) { item in
                            FAQCardView(item: item)
                        }
                    }
                    .padding(.horizontal)
                    
                    
                    // İLETİŞİM BÖLÜMÜ
                    VStack(spacing: 16) {
                        
                        Divider()
                            .padding(.vertical, 8)
                        
                        Text("Farklı destek ve yardım ihtiyacı için\nbizimle iletişime geçin")
                            .font(.subheadline)
                            .fontWeight(.medium)
                            .foregroundColor(.primary)
                            .multilineTextAlignment(.center)
                        
                        // EMAIL
                        HStack(spacing: 14) {
                            
                            ZStack {
                                RoundedRectangle(cornerRadius: 12)
                                    .fill(Color.yellow.opacity(0.15))
                                    .frame(width: 44, height: 44)
                                
                                Image(systemName: "envelope.fill")
                                    .foregroundColor(.yellow)
                            }
                            
                            VStack(alignment: .leading, spacing: 2) {
                                Text("E-posta")
                                    .font(.caption)
                                    .foregroundColor(.secondary)
                                
                                Text("mustafakara200533@gmail.com")
                                    .font(.subheadline)
                                    .fontWeight(.medium)
                                    .foregroundColor(.primary)
                            }
                            
                            Spacer()
                        }
                        .padding()
                        .background(
                            RoundedRectangle(cornerRadius: 16)
                                .fill(Color(.systemGray6))
                        )
                        
                        // TELEFON
                        HStack(spacing: 14) {
                            
                            ZStack {
                                RoundedRectangle(cornerRadius: 12)
                                    .fill(Color.yellow.opacity(0.15))
                                    .frame(width: 44, height: 44)
                                
                                Image(systemName: "phone.fill")
                                    .foregroundColor(.yellow)
                            }
                            
                            VStack(alignment: .leading, spacing: 2) {
                                Text("Telefon")
                                    .font(.caption)
                                    .foregroundColor(.secondary)
                                
                                Text("+90 552 812 0412")
                                    .font(.subheadline)
                                    .fontWeight(.medium)
                                    .foregroundColor(.primary)
                            }
                            
                            Spacer()
                        }
                        .padding()
                        .background(
                            RoundedRectangle(cornerRadius: 16)
                                .fill(Color(.systemGray6))
                        )
                    }
                    .padding(.horizontal)
                    .padding(.bottom, 30)
                }
                .padding(.top, 8)
            }
        }
        .background(Color(.systemBackground).ignoresSafeArea())
        .navigationBarBackButtonHidden(true)
        .toolbar(.hidden, for: .tabBar)
    }
}


struct FAQCardView: View {
    
    let item: FAQItem
    @State private var isExpanded = false
    
    var body: some View {
        
        VStack(alignment: .leading, spacing: 0) {
            
            // SORU BAŞLIĞI
            Button {
                withAnimation(.easeInOut(duration: 0.25)) {
                    isExpanded.toggle()
                }
            } label: {
                
                HStack {
                    
                    Text(item.question)
                        .font(.subheadline)
                        .fontWeight(.semibold)
                        .foregroundColor(.primary)
                        .multilineTextAlignment(.leading)
                    
                    Spacer()
                    
                    Image(systemName: isExpanded ? "chevron.up" : "chevron.down")
                        .font(.caption)
                        .foregroundColor(.yellow)
                }
                .padding()
            }
            
            // CEVAP
            if isExpanded {
                
                Divider()
                    .padding(.horizontal)
                
                Text(item.answer)
                    .font(.caption)
                    .foregroundColor(.secondary)
                    .padding()
                    .fixedSize(horizontal: false, vertical: true)
            }
        }
        .background(
            RoundedRectangle(cornerRadius: 16)
                .fill(Color(.systemGray6))
        )
        .clipShape(RoundedRectangle(cornerRadius: 16))
    }
}

#Preview {
    NavigationStack {
        SupportView()
    }
}
