import 'package:flutter/material.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/routes/app_routes.dart';

class OnboardingPageData {
  final String title;
  final String description;
  final String imageUrl;

  OnboardingPageData({
    required this.title,
    required this.description,
    required this.imageUrl,
  });
}

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<OnboardingPageData> _pages = [
    OnboardingPageData(
      title: 'Berber ve Bakım',
      description: 'Modern beyefendiler için özel olarak tasarlanmış profesyonel bakım deneyimini yaşayın.',
      imageUrl: 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?q=80&w=800',
    ),
    OnboardingPageData(
      title: 'Tarzını Keşfet',
      description: 'Size en çok yakışan kesimi uzman berberlerimizle birlikte belirleyin ve eşsiz tarzınızı yansıtın.',
      imageUrl: 'https://images.unsplash.com/photo-1593085512500-5d55148d6f0d?q=80&w=800',
    ),
    OnboardingPageData(
      title: 'Kafe Keyfi',
      description: 'Sıranızı beklerken veya tıraş sonrasında, özenle hazırladığımız kahvelerimizin tadını çıkarın.',
      imageUrl: 'https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=800',
    ),
    OnboardingPageData(
      title: 'Ürünlerimiz',
      description: 'Saç ve sakal bakımınız için özenle seçilmiş, en kaliteli profesyonel ürünlerimizi keşfedin.',
      imageUrl: 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=800',
    ),
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _skipOrLogin() {
    Navigator.pushReplacementNamed(context, AppRoutes.welcome);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.primaryColor,
      body: Stack(
        children: [
          // Background Images inside PageView
          PageView.builder(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() {
                _currentPage = index;
              });
            },
            itemCount: _pages.length,
            itemBuilder: (context, index) {
              return Stack(
                fit: StackFit.expand,
                children: [
                  // Image
                  Image.network(
                    _pages[index].imageUrl,
                    fit: BoxFit.cover,
                    loadingBuilder: (context, child, loadingProgress) {
                      if (loadingProgress == null) return child;
                      return const Center(
                        child: CircularProgressIndicator(
                          color: AppTheme.secondaryColor,
                        ),
                      );
                    },
                  ),
                  // Dark Gradient Overlay to make text readable
                  Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.transparent,
                          Color(0xE6000000), // Siyah %90 opacity
                        ],
                        stops: [0.4, 1.0],
                      ),
                    ),
                  ),
                ],
              );
            },
          ),
          
          // Texts and Indicators
          Positioned(
            bottom: 48,
            left: 24,
            right: 24,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  _pages[_currentPage].title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 32,
                    fontWeight: FontWeight.bold,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 16),
                Text(
                  _pages[_currentPage].description,
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 16,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 48),
                // Custom Dot Indicators
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(
                    _pages.length,
                    (index) => AnimatedContainer(
                      duration: const Duration(milliseconds: 300),
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      width: _currentPage == index ? 24 : 8,
                      height: 8,
                      decoration: BoxDecoration(
                        color: _currentPage == index ? AppTheme.secondaryColor : Colors.white38,
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // Top Right Skip/Login Button
          Positioned(
            top: 0,
            right: 0,
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                child: ElevatedButton(
                  onPressed: _skipOrLogin,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppTheme.secondaryColor,
                    foregroundColor: Colors.black,
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(24),
                    ),
                    elevation: 4,
                  ),
                  child: Text(
                    _currentPage == _pages.length - 1 ? 'Giriş Yap' : 'Atla',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
