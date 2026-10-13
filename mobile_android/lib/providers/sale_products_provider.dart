import 'package:flutter/foundation.dart';
import 'package:mobile_android/models/sale_product_model.dart';
import 'package:mobile_android/providers/cafe_provider.dart';
import 'package:mobile_android/services/api_service.dart';

class SaleProductsProvider with ChangeNotifier {
  List<SaleProductModel> _allProducts = [];
  bool _isLoading = false;
  String? _errorMessage;

  final Set<String> _selectedCategoryIds = {};
  String _searchText = '';
  ProductSortOption _sortOption = ProductSortOption.nameAsc;
  bool _showInStockOnly = false;

  List<SaleProductModel> get allProducts => _allProducts;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Set<String> get selectedCategoryIds => _selectedCategoryIds;
  String get searchText => _searchText;
  ProductSortOption get sortOption => _sortOption;
  bool get showInStockOnly => _showInStockOnly;

  int get activeFilterCount {
    int count = 0;
    if (_showInStockOnly) count += 1;
    if (_selectedCategoryIds.isNotEmpty) count += _selectedCategoryIds.length;
    if (_sortOption != ProductSortOption.nameAsc) count += 1;
    return count;
  }

  List<SaleProductModel> get filteredProducts {
    var result = List<SaleProductModel>.from(_allProducts);

    // 1. Stok filtresi
    if (_showInStockOnly) {
      result = result.where((p) => p.isInStock).toList();
    }

    // 2. Kategori filtresi
    if (_selectedCategoryIds.isNotEmpty) {
      result = result.where((p) => _selectedCategoryIds.contains(p.categoryId ?? 'other')).toList();
    }

    // 3. Arama filtresi
    if (_searchText.trim().isNotEmpty) {
      final query = _searchText.toLowerCase().trim();
      result = result.where((p) {
        return p.name.toLowerCase().contains(query) ||
            p.description.toLowerCase().contains(query) ||
            p.sku.toLowerCase().contains(query);
      }).toList();
    }

    // 4. Sıralama
    switch (_sortOption) {
      case ProductSortOption.nameAsc:
        result.sort((a, b) => a.name.compareTo(b.name));
        break;
      case ProductSortOption.nameDesc:
        result.sort((a, b) => b.name.compareTo(a.name));
        break;
      case ProductSortOption.priceAsc:
        result.sort((a, b) => a.sellPrice.compareTo(b.sellPrice));
        break;
      case ProductSortOption.priceDesc:
        result.sort((a, b) => b.sellPrice.compareTo(a.sellPrice));
        break;
    }

    return result;
  }

  List<DynamicSaleCategory> get availableCategories {
    final Map<String, DynamicSaleCategory> uniqueCategories = {};
    for (final p in _allProducts) {
      if (p.categoryId != null && p.categoryName != null) {
        uniqueCategories[p.categoryId!] = DynamicSaleCategory(id: p.categoryId!, label: p.categoryName!);
      }
    }
    final list = uniqueCategories.values.toList();
    list.sort((a, b) => a.label.compareTo(b.label));
    return list;
  }

  Future<void> fetchProducts() async {
    if (_allProducts.isEmpty) _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final data = await ApiService.getSaleProducts();
      _allProducts = data.map((item) => SaleProductModel.fromMap(item as Map<String, dynamic>)).toList();
    } catch (e) {
      _errorMessage = 'Satış ürünleri yüklenemedi: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setSearchText(String text) {
    _searchText = text;
    notifyListeners();
  }

  void toggleCategory(String catId) {
    if (_selectedCategoryIds.contains(catId)) {
      _selectedCategoryIds.remove(catId);
    } else {
      _selectedCategoryIds.add(catId);
    }
    notifyListeners();
  }

  void setSortOption(ProductSortOption option) {
    _sortOption = option;
    notifyListeners();
  }

  void setShowInStockOnly(bool value) {
    _showInStockOnly = value;
    notifyListeners();
  }

  void resetFilters() {
    _selectedCategoryIds.clear();
    _searchText = '';
    _sortOption = ProductSortOption.nameAsc;
    _showInStockOnly = false;
    notifyListeners();
  }
}
