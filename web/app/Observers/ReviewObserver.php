<?php

namespace App\Observers;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use App\Models\Review;
use App\Services\AdminNotificationService;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

/**
 * Mobil uygulamadan gelen yeni müşteri değerlendirmelerini yönetime bildirir.
 */
class ReviewObserver implements ShouldHandleEventsAfterCommit
{
    private const LOW_RATING_THRESHOLD = 2;

    public function __construct(private readonly AdminNotificationService $notifications)
    {
    }

    public function created(Review $review): void
    {
        $review->loadMissing(['customer', 'employee.user']);

        $rating = (int) $review->rating;
        $isLow = $rating <= self::LOW_RATING_THRESHOLD;

        $body = sprintf(
            '%s, %s için %d/5 puan verdi.',
            $review->customer?->full_name ?: 'Bir müşteri',
            $review->employee?->full_name ?: 'personel',
            $rating
        );

        $this->notifications->send(new AdminAlert(
            category: AdminNotificationCategory::Review,
            event: $isLow ? 'review.low_rating' : 'review.created',
            level: $isLow ? AdminNotificationLevel::Warning : AdminNotificationLevel::Info,
            title: $isLow ? 'Düşük Puanlı Değerlendirme' : 'Yeni Değerlendirme',
            body: $body,
            roles: AdminNotificationService::MANAGEMENT_ROLES,
            actionUrl: route('reviews.index', [], false),
            actionLabel: 'Değerlendirmeleri Gör',
            subject: $review,
        ));
    }
}
