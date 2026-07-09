<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\DiscountType;

class CampaignController extends Controller
{
    public function index()
    {
        $branchId = session('active_branch_id', 1);

        $campaigns = Campaign::where('branch_id', $branchId)
            ->with(['categories', 'usages.customer', 'usages.appointment.employee'])
            ->latest()
            ->get();
            
        // Since coupons are independent, we just fetch all coupons for now. 
        // Or if we want branch specific coupons, we'd need branch_id on coupons, but we didn't add it.
        $coupons = Coupon::with(['user', 'usages.customer', 'usages.appointment.employee'])
            ->latest()
            ->get();

        $categories = ServiceCategory::where('branch_id', $branchId)->get();
        $users = User::all();

        $stats = [
            'total_campaigns'  => $campaigns->count(),
            'active_campaigns' => $campaigns->where('is_active', true)->count(),
            'total_coupons'    => $coupons->count(),
            'active_coupons'   => $coupons->filter(fn($c) => $c->isValid())->count(),
        ];

        return view('campaigns.index', compact('campaigns', 'coupons', 'stats', 'categories', 'users'));
    }

    public function store(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'type'           => 'required|string|in:auto_apply,coupon_required',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'target_audience' => 'required|string|in:all,new_customers,loyalty_members',
            'priority'       => 'nullable|integer|min:0',
            'per_customer_limit' => 'nullable|integer|min:1',
            'discount_type'  => 'required|string|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categories'     => 'nullable|array',
            'categories.*'   => 'exists:service_categories,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('campaigns', 'public');
        }

        $validated['branch_id'] = $branchId;
        $validated['is_active'] = $request->has('is_active');
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['priority'] = $validated['priority'] ?? 0;

        $campaign = Campaign::create($validated);

        if ($request->has('categories')) {
            $campaign->categories()->sync($request->categories);
        }

        return redirect()->route('campaigns.index')->with('success', 'Kampanya başarıyla oluşturuldu.');
    }

    public function update(Request $request, Campaign $campaign)
    {
        $this->authorizeBranchAccess($campaign->branch_id);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'type'           => 'required|string|in:auto_apply,coupon_required',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'target_audience' => 'required|string|in:all,new_customers,loyalty_members',
            'priority'       => 'nullable|integer|min:0',
            'per_customer_limit' => 'nullable|integer|min:1',
            'discount_type'  => 'required|string|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'categories'     => 'nullable|array',
            'categories.*'   => 'exists:service_categories,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('campaigns', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['priority'] = $validated['priority'] ?? 0;

        $campaign->update($validated);

        if ($request->has('categories')) {
            $campaign->categories()->sync($request->categories);
        } else {
            $campaign->categories()->detach();
        }

        return redirect()->route('campaigns.index')->with('success', 'Kampanya başarıyla güncellendi.');
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
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'code'        => 'required|string|max:50|unique:coupons,code',
            'discount_type' => 'required|string|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'usage_limit' => 'required|integer|min:1',
            'per_customer_limit' => 'required|integer|min:1',
            'expires_at'  => 'required|date|after:today',
            'user_id'     => 'nullable|exists:users,id',
        ]);

        Coupon::create($validated);

        return redirect()->route('campaigns.index')->with('success', 'Kupon kodu başarıyla oluşturuldu.');
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'code'        => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|string|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'usage_limit' => 'required|integer|min:1',
            'per_customer_limit' => 'required|integer|min:1',
            'expires_at'  => 'required|date',
            'user_id'     => 'nullable|exists:users,id',
        ]);

        $coupon->update($validated);

        return redirect()->route('campaigns.index')->with('success', 'Kupon kodu başarıyla güncellendi.');
    }

    public function destroyCoupon(Coupon $coupon)
    {
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
