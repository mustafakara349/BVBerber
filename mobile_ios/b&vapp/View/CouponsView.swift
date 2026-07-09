import SwiftUI

struct CouponsView: View {
    @StateObject private var viewModel = CouponsViewModel()
    @Environment(\.dismiss) var dismiss
    
    var onSelect: ((String) -> Void)? = nil
    
    var activeCoupons: [Coupon] {
        viewModel.coupons.filter { $0.isValid }
    }
    
    var pastCoupons: [Coupon] {
        viewModel.coupons.filter { !$0.isValid }
    }
    
    var body: some View {
        ScrollView {
            VStack(spacing: 20) {
                if viewModel.isLoading {
                    ProgressView()
                        .scaleEffect(1.5)
                        .padding(.top, 50)
                } else if let error = viewModel.errorMessage {
                    VStack(spacing: 12) {
                        Image(systemName: "exclamationmark.triangle")
                            .font(.largeTitle)
                            .foregroundColor(.red)
                        Text(error)
                            .foregroundColor(.secondary)
                            .multilineTextAlignment(.center)
                        Button("Tekrar Dene") {
                            Task {
                                await viewModel.fetchCoupons()
                            }
                        }
                        .padding()
                    }
                    .padding(.top, 50)
                } else if viewModel.coupons.isEmpty {
                    VStack(spacing: 16) {
                        Image(systemName: "ticket")
                            .font(.system(size: 60))
                            .foregroundColor(.gray)
                            .padding(.top, 50)
                        Text("Henüz kuponunuz bulunmuyor.")
                            .font(.headline)
                        Text("Size özel tanımlanan indirim kuponları burada listelenecektir.")
                            .font(.subheadline)
                            .foregroundColor(.secondary)
                            .multilineTextAlignment(.center)
                            .padding(.horizontal)
                    }
                } else {
                    VStack(alignment: .leading, spacing: 12) {
                        Text("Aktif Kuponlar")
                            .font(.headline)
                            .foregroundColor(.primary)
                            .padding(.horizontal, 4)
                        
                        if activeCoupons.isEmpty {
                            Text("Şu anda kullanabileceğiniz aktif bir kuponunuz bulunmamaktadır.")
                                .font(.subheadline)
                                .foregroundColor(.secondary)
                                .padding(.horizontal, 4)
                                .padding(.vertical, 8)
                        } else {
                            ForEach(activeCoupons) { coupon in
                                if let onSelect = onSelect {
                                    Button(action: {
                                        onSelect(coupon.code)
                                        dismiss()
                                    }) {
                                        CompactCouponCard(coupon: coupon, isSelectable: true)
                                    }
                                    .buttonStyle(.plain)
                                } else {
                                    CompactCouponCard(coupon: coupon, isSelectable: false)
                                }
                            }
                        }
                    }
                    
                    if !pastCoupons.isEmpty {
                        VStack(alignment: .leading, spacing: 12) {
                            Text("Geçmiş Kuponlar")
                                .font(.headline)
                                .foregroundColor(.secondary)
                                .padding(.horizontal, 4)
                                .padding(.top, activeCoupons.isEmpty ? 0 : 16)
                            
                            ForEach(pastCoupons) { coupon in
                                CompactCouponCard(coupon: coupon, isSelectable: false)
                            }
                        }
                    }
                }
            }
            .padding()
        }
        .navigationTitle("Kuponlarım")
        .navigationBarTitleDisplayMode(.inline)
        .task {
            await viewModel.fetchCoupons()
        }
    }
}

struct CompactCouponCard: View {
    let coupon: Coupon
    var isSelectable: Bool = false
    
    var body: some View {
        HStack(spacing: 12) {
            // Sol Taraf: İkon ve Miktar
            VStack(spacing: 4) {
                Image(systemName: "ticket.fill")
                    .font(.title2)
                    .foregroundColor(coupon.isValid ? .yellow : .gray)
                
                if coupon.isPercentage {
                    Text("%\(Int(coupon.discountValue))")
                        .font(.headline)
                        .fontWeight(.black)
                        .foregroundColor(coupon.isValid ? .yellow : .gray)
                } else {
                    Text("₺\(Int(coupon.discountValue))")
                        .font(.headline)
                        .fontWeight(.black)
                        .foregroundColor(coupon.isValid ? .yellow : .gray)
                }
            }
            .frame(width: 60)
            .padding(.vertical, 12)
            .background(Color.yellow.opacity(coupon.isValid ? 0.15 : 0.05))
            .cornerRadius(10)
            
            // Orta Taraf: Detaylar
            VStack(alignment: .leading, spacing: 4) {
                Text(coupon.title)
                    .font(.subheadline)
                    .fontWeight(.bold)
                    .foregroundColor(coupon.isValid ? .primary : .secondary)
                
                if let expiresAt = coupon.expiresAt {
                    Text("Son Kullanma: \(expiresAt)")
                        .font(.caption2)
                        .foregroundColor(.gray)
                }
                
                Text("Kalan Kullanım: \(coupon.remainingUsage)/\(coupon.perCustomerLimit)")
                    .font(.caption2)
                    .foregroundColor(.secondary)
                
                if !coupon.isValid {
                    Text("Süresi doldu veya kullanıldı")
                        .font(.caption2)
                        .foregroundColor(.red)
                }
            }
            
            Spacer()
            
            // Sağ Taraf: Kod
            if isSelectable {
                Text("Kullan")
                    .font(.caption)
                    .fontWeight(.bold)
                    .foregroundColor(.white)
                    .padding(.horizontal, 12)
                    .padding(.vertical, 8)
                    .background(coupon.isValid ? Color.blue : Color.gray)
                    .cornerRadius(8)
            } else {
                Button(action: {
                    UIPasteboard.general.string = coupon.code
                }) {
                    VStack(spacing: 2) {
                        Text(coupon.code)
                            .font(.caption)
                            .fontWeight(.bold)
                            .foregroundColor(coupon.isValid ? .primary : .secondary)
                        
                        Image(systemName: "doc.on.doc")
                            .font(.caption2)
                            .foregroundColor(coupon.isValid ? .yellow : .gray)
                    }
                    .padding(8)
                    .background(Color(.systemBackground))
                    .cornerRadius(8)
                    .shadow(color: Color.black.opacity(0.05), radius: 2, x: 0, y: 1)
                }
                .disabled(!coupon.isValid)
            }
        }
        .padding(12)
        .background(Color(.systemGray6))
        .cornerRadius(12)
        .opacity(coupon.isValid ? 1.0 : 0.6)
    }
}

#Preview {
    CouponsView()
}
