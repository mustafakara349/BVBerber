//
//  OtpVerificationView.swift
//  b&vapp
//
//  Created by Mustafa KARA on 1.10.2026.
//

import SwiftUI
import Combine

struct OtpVerificationView: View {
    @ObservedObject var viewModel: AuthViewModel
    @Environment(\.dismiss) var dismiss
    @FocusState private var isOtpFocused: Bool
    
    // Countdown timer for 3 minutes (180 seconds)
    @State private var timeRemaining = 180
    let timer = Timer.publish(every: 1, on: .main, in: .common).autoconnect()
    
    var body: some View {
        ZStack {
            Color(UIColor.systemBackground)
                .ignoresSafeArea()
                .onTapGesture {
                    isOtpFocused = false
                    hideKeyboard()
                }
            
            VStack(spacing: 25) {
                Spacer()
                
                Image(systemName: "envelope.open")
                    .font(.system(size: 60))
                    .foregroundColor(.yellow)
                    .padding(.bottom, 10)
                
                Text("Kodu Doğrula")
                    .font(.title.bold())
                    .foregroundColor(.primary)
                
                Text("\(viewModel.forgotPasswordEmail) adresine 6 haneli bir kod gönderdik. Lütfen aşağıya girin.")
                    .font(.subheadline)
                    .foregroundColor(.gray)
                    .multilineTextAlignment(.center)
                    .padding(.horizontal, 20)
                
                // OTP INPUT
                VStack(alignment: .center, spacing: 16) {
                    Text("6 Haneli Kod")
                        .foregroundColor(.gray)
                        .font(.caption)
                        .frame(maxWidth: .infinity, alignment: .leading)
                    
                    ZStack {
                        TextField("", text: $viewModel.forgotPasswordOtp)
                            .keyboardType(.numberPad)
                            .textContentType(.oneTimeCode)
                            .focused($isOtpFocused)
                            .foregroundColor(.clear)
                            .accentColor(.clear)
                            .onChange(of: viewModel.forgotPasswordOtp) { newValue in
                                if newValue.count > 6 {
                                    viewModel.forgotPasswordOtp = String(newValue.prefix(6))
                                }
                            }
                        
                        HStack(spacing: 12) {
                            ForEach(0..<6, id: \.self) { index in
                                ZStack {
                                    RoundedRectangle(cornerRadius: 10)
                                        .stroke(isOtpFocused ? Color.yellow : Color.gray.opacity(0.5), lineWidth: 1)
                                        .frame(width: 45, height: 50)
                                        .background(Color(UIColor.secondarySystemBackground).cornerRadius(10))
                                    
                                    Text(getPin(at: index))
                                        .font(.title2)
                                        .foregroundColor(.primary)
                                }
                            }
                        }
                        .allowsHitTesting(false)
                    }
                }
                
                // TIMER OR RESEND
                if timeRemaining > 0 {
                    Text("Kodu tekrar göndermek için: \(timeString(time: timeRemaining))")
                        .font(.caption)
                        .foregroundColor(.gray)
                } else {
                    Button {
                        timeRemaining = 180
                        viewModel.sendOtp()
                    } label: {
                        Text("Kodu Tekrar Gönder")
                            .font(.footnote.bold())
                            .foregroundColor(.yellow)
                    }
                }
                
                // VERIFY BUTTON
                Button {
                    viewModel.verifyOtp()
                } label: {
                    if viewModel.isLoading {
                        ProgressView()
                            .tint(.black)
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .cornerRadius(12)
                    } else {
                        Text("Doğrula")
                            .font(.headline)
                            .foregroundColor(.black)
                            .frame(maxWidth: .infinity)
                            .padding()
                            .background(Color.yellow)
                            .cornerRadius(12)
                    }
                }
                .disabled(viewModel.isLoading || viewModel.forgotPasswordOtp.count != 6)
                .padding(.top, 10)
                
                Spacer()
                
                NavigationLink(destination: ResetPasswordView(viewModel: viewModel), isActive: $viewModel.navigateToReset) {
                    EmptyView()
                }
            }
            .padding(.horizontal, 30)
        }
        .onReceive(timer) { _ in
            if timeRemaining > 0 {
                timeRemaining -= 1
            }
        }
        .navigationBarTitleDisplayMode(.inline)
        .alert(viewModel.alertTitle, isPresented: $viewModel.showAlert) {
            Button("Tamam", role: .cancel) { }
        } message: {
            Text(viewModel.alertMessage)
        }
    }
    
    private func timeString(time: Int) -> String {
        let minutes = time / 60
        let seconds = time % 60
        return String(format: "%02d:%02d", minutes, seconds)
    }
    
    private func getPin(at index: Int) -> String {
        let pin = viewModel.forgotPasswordOtp
        if index < pin.count {
            let stringIndex = pin.index(pin.startIndex, offsetBy: index)
            return String(pin[stringIndex])
        }
        return ""
    }
}
