/// Mobil değerlendirme API yanıt modeli
class ReviewResultModel {
  final int reviewId;
  final int rating;
  final bool shouldPromptGoogle;
  final String? googleReviewUrl;
  final String message;

  ReviewResultModel({
    required this.reviewId,
    required this.rating,
    required this.shouldPromptGoogle,
    this.googleReviewUrl,
    required this.message,
  });

  factory ReviewResultModel.fromMap(Map<String, dynamic> map, [String defaultMessage = 'Değerlendirmeniz kaydedildi.']) {
    final data = map['data'] as Map<String, dynamic>? ?? {};
    return ReviewResultModel(
      reviewId: data['review_id'] is int 
          ? data['review_id'] 
          : (int.tryParse(data['review_id']?.toString() ?? '0') ?? 0),
      rating: data['rating'] is int 
          ? data['rating'] 
          : (int.tryParse(data['rating']?.toString() ?? '5') ?? 5),
      shouldPromptGoogle: data['should_prompt_google'] == true,
      googleReviewUrl: data['google_review_url']?.toString(),
      message: map['message']?.toString() ?? defaultMessage,
    );
  }
}
