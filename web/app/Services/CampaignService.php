<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Enums\DiscountType;
use Illuminate\Support\Collection;

class CampaignService
{
    /**
     * Otomatik uygulanan kampanyalar arasından sepet için en avantajlısını (veya önceliklisini) bulur.
     */
    public function evaluateCart(User $user, int $branchId, float $subtotal, array $serviceIds = [], array $productIds = []): ?array
    {
        $campaigns = Campaign::active()
            ->where('branch_id', $branchId)
            ->where('type', 'auto_apply')
            ->orderBy('priority', 'desc') // Yüksek öncelikliler önce
            ->get();

        $bestCampaign = null;
        $maxDiscount = 0;

        foreach ($campaigns as $campaign) {
            if ($this->isCampaignApplicable($campaign, $user, $subtotal, $serviceIds, $productIds)) {
                $discount = $this->calculateDiscountAmount($campaign, $subtotal);
                
                // Birden fazla eşleşen varsa en yüksek indirimi vereni seçelim.
                if ($discount > $maxDiscount) {
                    $maxDiscount = $discount;
                    $bestCampaign = $campaign;
                }
            }
        }

        if ($bestCampaign) {
            return [
                'campaign' => $bestCampaign,
                'discount_amount' => $maxDiscount,
            ];
        }

        return null;
    }

    /**
     * Girilen kupon kodunu doğrular ve indirim tutarını hesaplar.
     */
    public function validateCoupon(User $user, int $branchId, string $code, float $subtotal, array $serviceIds = [], array $productIds = []): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon || !$coupon->isValid()) {
            return ['valid' => false, 'message' => 'Geçersiz veya süresi dolmuş kupon kodu.'];
        }

        if ($coupon->user_id && $coupon->user_id !== $user->id) {
            return ['valid' => false, 'message' => 'Bu kupon size ait değil.'];
        }

        // Müşteri kullanım limiti kontrolü
        $usageCount = CouponUsage::where('coupon_id', $coupon->id)
            ->where('customer_id', $user->id)
            ->count();

        if ($usageCount >= $coupon->per_customer_limit) {
            return ['valid' => false, 'message' => 'Bu kupon için kullanım limitinizi doldurdunuz.'];
        }

        $discount = 0;
        if ($coupon->discount_type === DiscountType::Fixed || $coupon->discount_type->value === 'fixed') {
            $discount = $coupon->discount_value;
        } elseif ($coupon->discount_type === DiscountType::Percentage || $coupon->discount_type->value === 'percentage') {
            $discount = ($subtotal * $coupon->discount_value) / 100;
        }

        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        return [
            'valid' => true,
            'coupon' => $coupon,
            'campaign' => null, // Kuponlar artık kampanyadan bağımsız
            'discount_amount' => round($discount, 2),
        ];
    }

    /**
     * Belirli bir kampanya ID'sini doğrular ve indirim tutarını hesaplar (Otomatik kampanyaların manuel seçilmesi için).
     */
    public function validateCampaign(User $user, int $branchId, string $campaignId, float $subtotal, array $serviceIds = [], array $productIds = []): array
    {
        $campaign = Campaign::find($campaignId);

        if (!$campaign || !$campaign->is_active || $campaign->branch_id !== $branchId) {
            return ['valid' => false, 'message' => 'Bu kampanya şu anda geçerli değil.'];
        }

        // Kullanıcı limit kontrolü
        if ($campaign->per_customer_limit) {
            $usageCount = \App\Models\CampaignUsage::where('campaign_id', $campaign->id)
                ->where('customer_id', $user->id)
                ->count();
            if ($usageCount >= $campaign->per_customer_limit) {
                return ['valid' => false, 'message' => 'Bu kampanya için kullanım limitinizi doldurdunuz.'];
            }
        }

        // Kampanya şartları kontrolü
        if (!$this->isCampaignApplicable($campaign, $user, $subtotal, $serviceIds, $productIds)) {
            return ['valid' => false, 'message' => 'Sepetiniz bu kampanyanın şartlarını sağlamıyor.'];
        }

        $discount = $this->calculateDiscountAmount($campaign, $subtotal);

        return [
            'valid' => true,
            'campaign' => $campaign,
            'discount_amount' => $discount,
        ];
    }

    /**
     * Randevu tamamlandığında veya onaylandığında kupon kullanımını kaydeder.
     */
    public function recordCouponUsage(Coupon $coupon, User $user, int $appointmentId): void
    {
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'customer_id' => $user->id,
            'appointment_id' => $appointmentId,
            'used_at' => now(),
        ]);

        $coupon->increment('used_count');
    }

    public function recordCampaignUsage(Campaign $campaign, User $user, int $appointmentId): void
    {
        \App\Models\CampaignUsage::create([
            'campaign_id' => $campaign->id,
            'customer_id' => $user->id,
            'appointment_id' => $appointmentId,
            'used_at' => now(),
        ]);
    }

    /**
     * Kampanyanın verilen kullanıcı ve sepet verileri için uygun olup olmadığını kontrol eder.
     */
    protected function isCampaignApplicable(Campaign $campaign, User $user, float $subtotal, array $serviceIds, array $productIds): bool
    {
        // 1. Minimum sepet tutarı kontrolü
        if ($subtotal < $campaign->min_order_amount) {
            return false;
        }

        // 2. Hedef kitle kontrolü
        if ($campaign->target_audience === 'new_customers') {
            // Kullanıcının daha önce tamamlanmış randevusu var mı? (Basit bir kontrol)
            $completedAppointmentsCount = \App\Models\Appointment::where('customer_id', $user->id)
                ->where('status', \App\Enums\AppointmentStatus::Completed)
                ->count();
            if ($completedAppointmentsCount > 0) {
                return false;
            }
        } elseif ($campaign->target_audience === 'loyalty_members') {
            // Örnek: Sadakat programı üyesi mi?
            $loyaltyAccount = \App\Models\LoyaltyAccount::where('customer_id', $user->id)->first();
            if (!$loyaltyAccount) {
                return false;
            }
        }

        // 3. Hizmet / Ürün bazlı kısıtlamalar
        $campaignServices = $campaign->services()->pluck('services.id')->toArray();
        if (!empty($campaignServices)) {
            // Sepetteki hizmetlerden en az biri kampanyaya dahil mi?
            $hasMatchingService = !empty(array_intersect($serviceIds, $campaignServices));
            if (!$hasMatchingService) {
                return false; // Sepette uygun hizmet yok
            }
        }

        $campaignProducts = $campaign->products()->pluck('products.id')->toArray();
        if (!empty($campaignProducts)) {
            $hasMatchingProduct = !empty(array_intersect($productIds, $campaignProducts));
            if (!$hasMatchingProduct) {
                return false;
            }
        }

        // Kategori kısıtlaması
        $campaignCategories = $campaign->categories()->pluck('service_categories.id')->toArray();
        if (!empty($campaignCategories)) {
            $cartCategories = \App\Models\Service::whereIn('id', $serviceIds)->pluck('category_id')->toArray();
            $hasMatchingCategory = !empty(array_intersect($cartCategories, $campaignCategories));
            if (!$hasMatchingCategory) {
                return false;
            }
        }

        return true;
    }

    /**
     * İndirim tutarını hesaplar (maksimum sınırları gözeterek).
     */
    protected function calculateDiscountAmount(Campaign $campaign, float $subtotal): float
    {
        $discountAmount = 0;

        if ($campaign->discount_type === DiscountType::Fixed) {
            $discountAmount = $campaign->discount_value;
        } elseif ($campaign->discount_type === DiscountType::Percentage) {
            $discountAmount = ($subtotal * $campaign->discount_value) / 100;
        }

        // Maksimum indirim sınırı var mı?
        if ($campaign->max_discount_amount > 0 && $discountAmount > $campaign->max_discount_amount) {
            $discountAmount = $campaign->max_discount_amount;
        }

        // İndirim sepet tutarını geçemez
        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }

        return round($discountAmount, 2);
    }
}
