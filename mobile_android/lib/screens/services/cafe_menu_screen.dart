import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/cafe_product_model.dart';
import 'package:mobile_android/providers/cafe_provider.dart';

class CafeMenuScreen extends StatefulWidget {
  final CafeProductModel? initialProduct;

  const CafeMenuScreen({super.key, this.initialProduct});

  @override
  State<CafeMenuScreen> createState() => _CafeMenuScreenState();
}

class _CafeMenuScreenState extends State<CafeMenuScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = Provider.of<CafeProvider>(context, listen: false);
      provider.fetchProducts().then((_) {
        if (widget.initialProduct != null && mounted) {
          _showProductDetail(widget.initialProduct!);
        }
      });
    });
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  void _showProductDetail(CafeProductModel product) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => _CafeProductDetailSheet(product: product),
    );
  }

  void _showFilterSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const _CafeFilterSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cafeProvider = Provider.of<CafeProvider>(context);
    final isDark = AppTheme.isDark(context);

    return Scaffold(
      backgroundColor: AppTheme.bg(context),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(Icons.arrow_back_ios_new, color: isDark ? Colors.white : Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Kafe Menüsü',
          style: TextStyle(
            color: isDark ? Colors.white : Colors.black87,
            fontWeight: FontWeight.bold,
            fontSize: 20,
          ),
        ),
        centerTitle: true,
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Subheader
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
            child: Text(
              '${cafeProvider.filteredProducts.length} ürün listeleniyor',
              style: TextStyle(color: isDark ? Colors.white54 : Colors.black54, fontSize: 13),
            ),
          ),

          // Arama çubuğu
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E1E22) : const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  Icon(Icons.search, color: isDark ? Colors.white38 : Colors.black45, size: 22),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextField(
                      controller: _searchController,
                      style: TextStyle(color: isDark ? Colors.white : Colors.black87),
                      decoration: InputDecoration(
                        hintText: 'Menüde ara...',
                        hintStyle: TextStyle(color: isDark ? Colors.white38 : Colors.black38, fontSize: 14),
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        contentPadding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                      onChanged: (val) {
                        cafeProvider.setSearchText(val);
                      },
                    ),
                  ),
                  if (_searchController.text.isNotEmpty)
                    GestureDetector(
                      onTap: () {
                        _searchController.clear();
                        cafeProvider.setSearchText('');
                      },
                      child: Icon(Icons.cancel, color: isDark ? Colors.white38 : Colors.black38, size: 20),
                    ),
                ],
              ),
            ),
          ),

          // Filtreleme barı
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                GestureDetector(
                  onTap: _showFilterSheet,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: cafeProvider.activeFilterCount > 0
                          ? AppTheme.secondaryColor.withOpacity(isDark ? 0.3 : 0.2)
                          : (isDark ? const Color(0xFF1E1E22) : Colors.white),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: cafeProvider.activeFilterCount > 0
                            ? AppTheme.secondaryColor
                            : (isDark ? const Color(0xFF333338) : Colors.grey.shade300),
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.tune,
                          size: 16,
                          color: cafeProvider.activeFilterCount > 0
                              ? (isDark ? AppTheme.secondaryColor : Colors.black87)
                              : (isDark ? Colors.white70 : Colors.black54),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          'Filtrele ${cafeProvider.activeFilterCount > 0 ? '(${cafeProvider.activeFilterCount})' : ''}',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: cafeProvider.activeFilterCount > 0
                                ? (isDark ? AppTheme.secondaryColor : Colors.black87)
                                : (isDark ? Colors.white70 : Colors.black54),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                if (cafeProvider.activeFilterCount > 0)
                  GestureDetector(
                    onTap: () {
                      _searchController.clear();
                      cafeProvider.resetFilters();
                    },
                    child: const Text(
                      'Sıfırla',
                      style: TextStyle(color: AppTheme.errorColor, fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 8),

          // Ürün listesi
          Expanded(
            child: cafeProvider.isLoading
                ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
                : cafeProvider.filteredProducts.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.coffee_outlined, size: 64, color: isDark ? Colors.white24 : Colors.black26),
                            const SizedBox(height: 16),
                            Text(
                              'Ürün bulunamadı.',
                              style: TextStyle(color: isDark ? Colors.white54 : Colors.black54, fontSize: 15),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                        itemCount: cafeProvider.filteredProducts.length,
                        itemBuilder: (context, index) {
                          final product = cafeProvider.filteredProducts[index];
                          return _buildCafeProductCard(product);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildCafeProductCard(CafeProductModel product) {
    final isDark = AppTheme.isDark(context);

    return GestureDetector(
      onTap: () => _showProductDetail(product),
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1C1C1E) : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? const Color(0xFF2C2C30) : Colors.grey.shade200,
            width: 1,
          ),
          boxShadow: isDark
              ? []
              : [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.04),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                  ),
                ],
        ),
        child: Row(
          children: [
            // Resim
            ClipRRect(
              borderRadius: BorderRadius.circular(14),
              child: Container(
                width: 75,
                height: 75,
                color: isDark ? const Color(0xFF26262B) : Colors.grey.shade100,
                child: product.imageUrl.isNotEmpty
                    ? Image.network(
                        product.imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => Icon(Icons.coffee, color: isDark ? Colors.white30 : Colors.black26, size: 30),
                      )
                    : Icon(Icons.coffee, color: isDark ? Colors.white30 : Colors.black26, size: 30),
              ),
            ),
            const SizedBox(width: 14),

            // Bilgiler
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    product.name,
                    style: TextStyle(
                      color: isDark ? Colors.white : Colors.black87,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product.categoryName ?? 'Diğer',
                    style: TextStyle(color: isDark ? Colors.white54 : Colors.black45, fontSize: 12),
                  ),
                  if (product.ingredients.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      product.ingredients,
                      style: TextStyle(color: isDark ? Colors.white38 : Colors.black38, fontSize: 11),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 6),
                  Text(
                    product.formattedPrice,
                    style: const TextStyle(
                      color: AppTheme.secondaryColor,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),

            Icon(Icons.chevron_right, color: isDark ? Colors.white30 : Colors.black26),
          ],
        ),
      ),
    );
  }
}

// Detay Sheet
class _CafeProductDetailSheet extends StatelessWidget {
  final CafeProductModel product;

  const _CafeProductDetailSheet({required this.product});

  @override
  Widget build(BuildContext context) {
    final isDark = AppTheme.isDark(context);

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF18181B) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Tutamaç
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF333338) : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Ürün Resmi
          if (product.imageUrl.isNotEmpty)
            Center(
              child: ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: SizedBox(
                  width: double.infinity,
                  height: 200,
                  child: Image.network(
                    product.imageUrl,
                    fit: BoxFit.cover,
                    errorBuilder: (_, _, _) => Container(
                      color: isDark ? const Color(0xFF26262B) : Colors.grey.shade100,
                      child: Icon(Icons.coffee, color: isDark ? Colors.white30 : Colors.black26, size: 60),
                    ),
                  ),
                ),
              ),
            ),
          const SizedBox(height: 18),

          // Başlık ve Fiyat
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  product.name,
                  style: TextStyle(
                    color: isDark ? Colors.white : Colors.black87,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              Text(
                product.formattedPrice,
                style: const TextStyle(
                  color: AppTheme.secondaryColor,
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            product.categoryName ?? 'Diğer',
            style: TextStyle(color: isDark ? Colors.white54 : Colors.black45, fontSize: 13),
          ),
          const SizedBox(height: 16),

          if (product.description.isNotEmpty) ...[
            Text(
              'Açıklama',
              style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              product.description,
              style: TextStyle(color: isDark ? Colors.white70 : Colors.black54, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 16),
          ],

          if (product.ingredients.isNotEmpty) ...[
            Text(
              'İçindekiler',
              style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              product.ingredients,
              style: TextStyle(color: isDark ? Colors.white70 : Colors.black54, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 20),
          ],

          // Kapat butonu
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: const Text('Kapat', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(height: 10),
        ],
      ),
    );
  }
}

// Filtre Sheet
class _CafeFilterSheet extends StatelessWidget {
  const _CafeFilterSheet();

  @override
  Widget build(BuildContext context) {
    final provider = Provider.of<CafeProvider>(context);
    final isDark = AppTheme.isDark(context);

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF18181B) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF333338) : Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'Filtrele & Sırala',
            style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),

          // Kategoriler
          Text(
            'Kategoriler',
            style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontSize: 14, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: provider.availableCategories.map((cat) {
              final isSelected = provider.selectedCategoryIds.contains(cat.id);
              return FilterChip(
                label: Text(cat.label),
                selected: isSelected,
                selectedColor: AppTheme.secondaryColor.withOpacity(isDark ? 0.3 : 0.2),
                backgroundColor: isDark ? const Color(0xFF222226) : null,
                checkmarkColor: isDark ? AppTheme.secondaryColor : Colors.black87,
                labelStyle: TextStyle(
                  color: isSelected
                      ? (isDark ? AppTheme.secondaryColor : Colors.black87)
                      : (isDark ? Colors.white70 : Colors.black54),
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                ),
                onSelected: (_) {
                  provider.toggleCategory(cat.id);
                },
              );
            }).toList(),
          ),
          const SizedBox(height: 20),

          // Sıralama
          Text(
            'Sıralama',
            style: TextStyle(color: isDark ? Colors.white : Colors.black87, fontSize: 14, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            children: [
              ChoiceChip(
                label: const Text('İsim (A-Z)'),
                selected: provider.sortOption == ProductSortOption.nameAsc,
                selectedColor: AppTheme.secondaryColor.withOpacity(isDark ? 0.3 : 0.2),
                backgroundColor: isDark ? const Color(0xFF222226) : null,
                labelStyle: TextStyle(
                  color: provider.sortOption == ProductSortOption.nameAsc
                      ? (isDark ? AppTheme.secondaryColor : Colors.black87)
                      : (isDark ? Colors.white70 : Colors.black54),
                ),
                onSelected: (_) => provider.setSortOption(ProductSortOption.nameAsc),
              ),
              ChoiceChip(
                label: const Text('Fiyat (Artan)'),
                selected: provider.sortOption == ProductSortOption.priceAsc,
                selectedColor: AppTheme.secondaryColor.withOpacity(isDark ? 0.3 : 0.2),
                backgroundColor: isDark ? const Color(0xFF222226) : null,
                labelStyle: TextStyle(
                  color: provider.sortOption == ProductSortOption.priceAsc
                      ? (isDark ? AppTheme.secondaryColor : Colors.black87)
                      : (isDark ? Colors.white70 : Colors.black54),
                ),
                onSelected: (_) => provider.setSortOption(ProductSortOption.priceAsc),
              ),
              ChoiceChip(
                label: const Text('Fiyat (Azalan)'),
                selected: provider.sortOption == ProductSortOption.priceDesc,
                selectedColor: AppTheme.secondaryColor.withOpacity(isDark ? 0.3 : 0.2),
                backgroundColor: isDark ? const Color(0xFF222226) : null,
                labelStyle: TextStyle(
                  color: provider.sortOption == ProductSortOption.priceDesc
                      ? (isDark ? AppTheme.secondaryColor : Colors.black87)
                      : (isDark ? Colors.white70 : Colors.black54),
                ),
                onSelected: (_) => provider.setSortOption(ProductSortOption.priceDesc),
              ),
            ],
          ),
          const SizedBox(height: 24),

          // Uygula Butonu
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(context),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.secondaryColor,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
              child: const Text('Sonuçları Göster', style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }
}
