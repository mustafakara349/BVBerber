<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Coupon;
use Illuminate\Http\Request;
use App\Enums\DiscountType;

class CampaignController extends Controller
{
    public function index()
    {
        $branchId = session('active_branch_id', 1);

        $campaigns = Campaign::where('branch_id', $branchId)->latest()->get();
        $coupons = Coupon::whereHas('campaign', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->with('campaign')->latest()->get();

        $stats = [
            'total_campaigns'  => $campaigns->count(),
            'active_campaigns' => $campaigns->where('is_active', true)->count(),
            'total_coupons'    => $coupons->count(),
            'active_coupons'   => $coupons->filter(fn($c) => $c->isValid())->count(),
        ];

        return view('campaigns.index', compact('campaigns', 'coupons', 'stats'));
    }

    public function store(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'discount_type'  => 'required|string|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
        ]);

        $validated['branch_id'] = $branchId;
        $validated['is_active'] = $request->has('is_active');

        Campaign::create($validated);

        return redirect()->route('campaigns.index')->with('success', 'Kampanya başarıyla oluşturuldu.');
    }

    public function toggleStatus(Campaign $campaign)
    {
        $this->authorizeBranchAccess($campaign->branch_id);

        $campaign->update(['is_active' => ! $campaign->is_active]);
        return redirect()->route('campaigns.index')->with('success', 'Kampanya durumu güncellendi.');
    }

    public function destroy(Campaign $campaign)
    {
        $this->authorizeBranchAccess($campaign->branch_id);

        $campaign->delete();
        return redirect()->route('campaigns.index')->with('success', 'Kampanya başarıyla silindi.');
    }

    public function storeCoupon(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'code'        => 'required|string|max:50|unique:coupons,code',
            'usage_limit' => 'required|integer|min:1',
            'expires_at'  => 'required|date|after:today',
        ]);

        // Kuponun bağlı olduğu kampanyanın aktif şubeye ait olduğunu doğrula
        $campaign = Campaign::findOrFail($validated['campaign_id']);
        $this->authorizeBranchAccess($campaign->branch_id);

        Coupon::create($validated);

        return redirect()->route('campaigns.index')->with('success', 'Kupon kodu başarıyla oluşturuldu.');
    }

    public function destroyCoupon(Coupon $coupon)
    {
        // Kuponun kampanyası üzerinden şube doğrulaması
        $this->authorizeBranchAccess($coupon->campaign->branch_id);

        $coupon->delete();
        return redirect()->route('campaigns.index')->with('success', 'Kupon kodu silindi.');
    }

    /**
     * Verilen branch_id'nin aktif oturumun şubesiyle eşleştiğini doğrular.
     * Uyuşmazlıkta 403 döner.
     */
    private function authorizeBranchAccess(int $resourceBranchId): void
    {
        if ($resourceBranchId !== (int) session('active_branch_id', 1)) {
            abort(403, 'Bu kaynağa erişim yetkiniz bulunmamaktadır.');
        }
    }
}
