import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';

class SupportScreen extends StatelessWidget {
  const SupportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB), // Light background like in the image
      appBar: AppBar(
        backgroundColor: const Color(0xFFF9FAFB),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.black, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Destek ve Yardım',
          style: TextStyle(
            color: Colors.black,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Sıkça Sorulan Sorular Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.secondaryColor.withOpacity(0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.chat_bubble_outline, color: AppTheme.secondaryColor),
                ),
                const SizedBox(width: 12),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Sıkça Sorulan Sorular',
                        style: TextStyle(
                          color: Colors.black87,
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'Merak ettiğiniz soruların cevapları',
                        style: TextStyle(
                          color: Colors.black54,
                          fontSize: 13,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // FAQ Items
            _buildFaqItem(
              question: 'Randevu nasıl alabilirim?',
              answer: "Ana sayfadaki 'Hemen Randevu Al' butonuna veya alt menüdeki Randevular sekmesindeki '+' ikonuna tıklayarak tarih, personel ve saat seçerek kolayca randevu oluşturabilirsiniz.",
            ),
            _buildFaqItem(
              question: 'Randevumu nasıl iptal edebilirim?',
              answer: "Randevular sekmesindeki yaklaşan randevunuzun altındaki 'İptal Et' butonuna tıklayarak randevunuzu iptal edebilirsiniz.",
            ),
            _buildFaqItem(
              question: 'Şifremi nasıl değiştirebilirim?',
              answer: "Profil sayfanızdaki 'Güvenlik ve Giriş' bölümünden şifrenizi güvenli bir şekilde değiştirebilirsiniz.",
            ),
            _buildFaqItem(
              question: 'Hangi hizmetleri sunuyorsunuz?',
              answer: "Saç kesimi, sakal tıraşı, cilt bakımı, saç boyama ve daha birçok profesyonel erkek bakım hizmeti sunuyoruz. Detaylı listeyi 'Hizmetler' sekmesinden inceleyebilirsiniz.",
            ),
            _buildFaqItem(
              question: 'Ödeme yöntemleri nelerdir?',
              answer: "Hizmet bedelini randevu sonrasında nakit veya kredi/banka kartı ile ödeyebilirsiniz. Uygulama üzerinden online ödeme şu an aktif değildir.",
            ),
            _buildFaqItem(
              question: 'Çalışma saatleriniz nedir?',
              answer: "Haftanın 6 günü 09:00 - 22:00 saatleri arasında hizmet vermekteyiz. Pazar günleri kapalıyız.",
            ),

            const SizedBox(height: 40),

            // İletişim Header
            const Center(
              child: Text(
                'Farklı destek ve yardım ihtiyacı için\nbizimle iletişime geçin',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Colors.black87,
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  height: 1.4,
                ),
              ),
            ),
            const SizedBox(height: 24),

            // E-posta Card
            _buildContactCard(
              icon: Icons.email_outlined,
              title: 'E-posta',
              value: 'mustafakara200533@gmail.com',
              valueColor: AppTheme.secondaryColor,
            ),
            const SizedBox(height: 12),
            // Telefon Card
            _buildContactCard(
              icon: Icons.phone_outlined,
              title: 'Telefon',
              value: '+90 552 812 0412',
              valueColor: Colors.black87,
            ),
            const SizedBox(height: 100), // Bottom navbar space
          ],
        ),
      ),
    );
  }

  Widget _buildFaqItem({required String question, required String answer}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFF3F4F6),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Theme(
        data: ThemeData().copyWith(dividerColor: Colors.transparent),
        child: ExpansionTile(
          iconColor: AppTheme.secondaryColor,
          collapsedIconColor: AppTheme.secondaryColor,
          title: Text(
            question,
            style: const TextStyle(
              color: Colors.black87,
              fontSize: 15,
              fontWeight: FontWeight.w600,
            ),
          ),
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Text(
                answer,
                style: const TextStyle(
                  color: Colors.black54,
                  fontSize: 14,
                  height: 1.5,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContactCard({
    required IconData icon,
    required String title,
    required String value,
    required Color valueColor,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF3F4F6),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppTheme.secondaryColor.withOpacity(0.15),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: AppTheme.secondaryColor),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: Colors.black54,
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  value,
                  style: TextStyle(
                    color: valueColor,
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
