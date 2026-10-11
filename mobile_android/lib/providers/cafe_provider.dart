import 'package:flutter/foundation.dart';
import 'package:mobile_android/models/cafe_product_model.dart';
import 'package:mobile_android/services/api_service.dart';

enum ProductSortOption {
  nameAsc,
  nameDesc,
  priceAsc,
  priceDesc,
}

class CafeProvider with ChangeNotifier {
  List<CafeProductModel> _allProducts = [];
  bool _isLoading = false;
  String? _errorMessage;

  final Set<String> _selectedCategoryIds = {};
  String _searchText = '';
  ProductSortOption _sortOption = ProductSortOption.nameAsc;

  List<CafeProductModel> get allProducts => _allProducts;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Set<String> get selectedCategoryIds => _selectedCategoryIds;
  String get searchText => _searchText;
  ProductSortOption get sortOption => _sortOption;

  int get activeFilterCount {
    int count = 0;
    if (_selectedCategoryIds.isNotEmpty) count += _selectedCategoryIds.length;
    if (_sortOption != ProductSortOption.nameAsc) count += 1;
    return count;
  }

  List<CafeProductModel> get filteredProducts {
    var result = List<CafeProductModel>.from(_allProducts);

    // 1. Kategori filtresi
    if (_selectedCategoryIds.isNotEmpty) {
      result = result.where((p) => _selectedCategoryIds.contains(p.categoryId ?? 'other')).toList();
    }

    // 2. Arama filtresi
    if (_searchText.trim().isNotEmpty) {
      final query = _searchText.toLowerCase().trim();
      result = result.where((p) {
        return p.name.toLowerCase().contains(query) ||
            p.description.toLowerCase().contains(query) ||
            p.ingredients.toLowerCase().contains(query);
      }).toList();
    }

    // 3. Sıralama
    switch (_sortOption) {
      case ProductSortOption.nameAsc:
        result.sort((a, b) => a.name.compareTo(b.name));
        break;
      case ProductSortOption.nameDesc:
        result.sort((a, b) => b.name.compareTo(a.name));
        break;
      case ProductSortOption.priceAsc:
        result.sort((a, b) => a.price.compareTo(b.price));
        break;
      case ProductSortOption.priceDesc:
        result.sort((a, b) => b.price.compareTo(a.price));
        break;
    }

    return result;
  }

  List<DynamicCafeCategory> get availableCategories {
    final Map<String, DynamicCafeCategory> uniqueCategories = {};
    for (final p in _allProducts) {
      if (p.categoryId != null && p.categoryName != null) {
        uniqueCategories[p.categoryId!] = DynamicCafeCategory(id: p.categoryId!, label: p.categoryName!);
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
      final data = await ApiService.getCafeProducts();
      _allProducts = data.map((item) => CafeProductModel.fromMap(item as Map<String, dynamic>)).toList();
    } catch (e) {
      _errorMessage = 'Kafe ürünleri yüklenemedi: $e';
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

  void resetFilters() {
    _selectedCategoryIds.clear();
    _searchText = '';
    _sortOption = ProductSortOption.nameAsc;
    notifyListeners();
  }
}
