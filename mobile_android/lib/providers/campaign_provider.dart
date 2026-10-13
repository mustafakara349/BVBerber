import 'package:flutter/foundation.dart';
import 'package:mobile_android/models/campaign_model.dart';
import 'package:mobile_android/models/coupon_model.dart';
import 'package:mobile_android/services/api_service.dart';

class CampaignProvider with ChangeNotifier {
  List<CampaignModel> _campaigns = [];
  List<CouponModel> _coupons = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<CampaignModel> get campaigns => _campaigns;
  List<CouponModel> get coupons => _coupons;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  List<CouponModel> get activeCoupons => _coupons.where((c) => c.isValid).toList();
  List<CouponModel> get pastCoupons => _coupons.where((c) => !c.isValid).toList();

  Future<void> fetchCampaigns() async {
    try {
      final data = await ApiService.getCampaigns();
      _campaigns = data.map((item) => CampaignModel.fromMap(item as Map<String, dynamic>)).toList();
      notifyListeners();
    } catch (e) {
      debugPrint('Kampanyalar alınamadı: $e');
    }
  }

  Future<void> fetchCoupons() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final data = await ApiService.getCoupons();
      _coupons = data.map((item) => CouponModel.fromMap(item as Map<String, dynamic>)).toList();
    } catch (e) {
      _errorMessage = 'Kuponlar yüklenemedi: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}
