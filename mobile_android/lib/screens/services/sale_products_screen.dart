import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:mobile_android/core/app_theme.dart';
import 'package:mobile_android/models/sale_product_model.dart';
import 'package:mobile_android/providers/cafe_provider.dart';
import 'package:mobile_android/providers/sale_products_provider.dart';

class SaleProductsScreen extends StatefulWidget {
  final SaleProductModel? initialProduct;

  const SaleProductsScreen({super.key, this.initialProduct});

  @override
  State<SaleProductsScreen> createState() => _SaleProductsScreenState();
}

class _SaleProductsScreenState extends State<SaleProductsScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final provider = Provider.of<SaleProductsProvider>(context, listen: false);
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

  void _showProductDetail(SaleProductModel product) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => _SaleProductDetailSheet(product: product),
    );
  }

  void _showFilterSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const _SaleFilterSheet(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final productsProvider = Provider.of<SaleProductsProvider>(context);

    return Scaffold(
      backgroundColor: AppTheme.backgroundColor,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new, color: Colors.black87),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Ürünlerimiz',
          style: TextStyle(
            color: Colors.black87,
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
              '${productsProvider.filteredProducts.length} ürün listeleniyor',
              style: const TextStyle(color: Colors.black54, fontSize: 13),
            ),
          ),

          // Arama çubuğu
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  const Icon(Icons.search, color: Colors.black45, size: 22),
                  const SizedBox(width: 8),
                  Expanded(
                    child: TextField(
                      controller: _searchController,
                      decoration: const InputDecoration(
                        hintText: 'Ürün ara...',
                        hintStyle: TextStyle(color: Colors.black38, fontSize: 14),
                        border: InputBorder.none,
                        enabledBorder: InputBorder.none,
                        focusedBorder: InputBorder.none,
                        contentPadding: EdgeInsets.symmetric(vertical: 12),
                      ),
                      onChanged: (val) {
                        productsProvider.setSearchText(val);
                      },
                    ),
                  ),
                  if (_searchController.text.isNotEmpty)
                    GestureDetector(
                      onTap: () {
                        _searchController.clear();
                        productsProvider.setSearchText('');
                      },
                      child: const Icon(Icons.cancel, color: Colors.black38, size: 20),
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
                      color: productsProvider.activeFilterCount > 0 ? AppTheme.secondaryColor.withOpacity(0.2) : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: productsProvider.activeFilterCount > 0 ? AppTheme.secondaryColor : Colors.grey.shade300,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.tune,
                          size: 16,
                          color: productsProvider.activeFilterCount > 0 ? Colors.black87 : Colors.black54,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          'Filtrele ${productsProvider.activeFilterCount > 0 ? '(${productsProvider.activeFilterCount})' : ''}',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: productsProvider.activeFilterCount > 0 ? Colors.black87 : Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                if (productsProvider.activeFilterCount > 0)
                  GestureDetector(
                    onTap: () {
                      _searchController.clear();
                      productsProvider.resetFilters();
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
            child: productsProvider.isLoading
                ? const Center(child: CircularProgressIndicator(color: AppTheme.secondaryColor))
                : productsProvider.filteredProducts.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.inventory_2_outlined, size: 64, color: Colors.black26),
                            SizedBox(height: 16),
                            Text(
                              'Ürün bulunamadı.',
                              style: TextStyle(color: Colors.black54, fontSize: 15),
                            ),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
                        itemCount: productsProvider.filteredProducts.length,
                        itemBuilder: (context, index) {
                          final product = productsProvider.filteredProducts[index];
                          return _buildSaleProductCard(product);
                        },
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildSaleProductCard(SaleProductModel product) {
    Color statusColor;
    switch (product.stockStatus) {
      case StockStatus.ok:
        statusColor = AppTheme.successColor;
        break;
      case StockStatus.low:
        statusColor = Colors.orange;
        break;
      case StockStatus.empty:
        statusColor = AppTheme.errorColor;
        break;
    }

    return GestureDetector(
      onTap: () => _showProductDetail(product),
      child: Container(
        margin: const EdgeInsets.only(bottom: 16),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
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
                color: Colors.grey.shade100,
                child: product.imageUrl.isNotEmpty
                    ? Image.network(
                        product.imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(Icons.shopping_bag, color: Colors.black26, size: 30),
                      )
                    : const Icon(Icons.shopping_bag, color: Colors.black26, size: 30),
              ),
            ),
            const SizedBox(width: 14),

            // Bilgiler
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          product.name,
                          style: const TextStyle(
                            color: Colors.black87,
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: statusColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          product.stockStatus.label,
                          style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    product.categoryName ?? 'Diğer',
                    style: const TextStyle(color: Colors.black45, fontSize: 12),
                  ),
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

            const Icon(Icons.chevron_right, color: Colors.black26),
          ],
        ),
      ),
    );
  }
}

// Detay Sheet
class _SaleProductDetailSheet extends StatelessWidget {
  final SaleProductModel product;

  const _SaleProductDetailSheet({required this.product});

  @override
  Widget build(BuildContext context) {
    Color statusColor;
    switch (product.stockStatus) {
      case StockStatus.ok:
        statusColor = AppTheme.successColor;
        break;
      case StockStatus.low:
        statusColor = Colors.orange;
        break;
      case StockStatus.empty:
        statusColor = AppTheme.errorColor;
        break;
    }

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
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
                color: Colors.grey.shade300,
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
                    errorBuilder: (_, __, ___) => Container(
                      color: Colors.grey.shade100,
                      child: const Icon(Icons.shopping_bag, color: Colors.black26, size: 60),
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
                  style: const TextStyle(
                    color: Colors.black87,
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
          Row(
            children: [
              Text(
                product.categoryName ?? 'Diğer',
                style: const TextStyle(color: Colors.black45, fontSize: 13),
              ),
              const SizedBox(width: 10),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  product.stockStatus.label,
                  style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          if (product.description.isNotEmpty) ...[
            const Text(
              'Ürün Açıklaması',
              style: TextStyle(color: Colors.black87, fontSize: 14, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 6),
            Text(
              product.description,
              style: const TextStyle(color: Colors.black54, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 16),
          ],

          if (product.barcode.isNotEmpty || product.sku.isNotEmpty) ...[
            Row(
              children: [
                if (product.sku.isNotEmpty)
                  Text('Kod: ${product.sku}   ', style: const TextStyle(color: Colors.black38, fontSize: 12)),
                if (product.barcode.isNotEmpty)
                  Text('Barkod: ${product.barcode}', style: const TextStyle(color: Colors.black38, fontSize: 12)),
              ],
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
class _SaleFilterSheet extends StatelessWidget {
  const _SaleFilterSheet();

  @override
  Widget build(BuildContext context) {
    final provider = Provider.of<SaleProductsProvider>(context);

    return Container(
      padding: const EdgeInsets.all(24),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
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
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'Filtrele & Sırala',
            style: TextStyle(color: Colors.black87, fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),

          // Stok durumu filtresi
          SwitchListTile(
            title: const Text('Sadece Stoktakiler', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
            value: provider.showInStockOnly,
            activeColor: AppTheme.secondaryColor,
            contentPadding: EdgeInsets.zero,
            onChanged: (val) {
              provider.setShowInStockOnly(val);
            },
          ),
          const SizedBox(height: 12),

          // Kategoriler
          const Text(
            'Kategoriler',
            style: TextStyle(color: Colors.black87, fontSize: 14, fontWeight: FontWeight.w600),
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
                selectedColor: AppTheme.secondaryColor.withOpacity(0.2),
                checkmarkColor: Colors.black87,
                labelStyle: TextStyle(
                  color: isSelected ? Colors.black87 : Colors.black54,
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
          const Text(
            'Sıralama',
            style: TextStyle(color: Colors.black87, fontSize: 14, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            children: [
              ChoiceChip(
                label: const Text('İsim (A-Z)'),
                selected: provider.sortOption == ProductSortOption.nameAsc,
                onSelected: (_) => provider.setSortOption(ProductSortOption.nameAsc),
              ),
              ChoiceChip(
                label: const Text('Fiyat (Artan)'),
                selected: provider.sortOption == ProductSortOption.priceAsc,
                onSelected: (_) => provider.setSortOption(ProductSortOption.priceAsc),
              ),
              ChoiceChip(
                label: const Text('Fiyat (Azalan)'),
                selected: provider.sortOption == ProductSortOption.priceDesc,
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
