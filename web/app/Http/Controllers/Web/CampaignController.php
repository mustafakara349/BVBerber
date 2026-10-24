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
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $campaignsQuery = Campaign::where('branch_id', $branchId)
            ->with(['categories', 'services', 'usages.customer', 'usages.appointment.employee'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $campaignsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $campaignsQuery->active();
            } else {
                $campaignsQuery->where(function ($q) {
                    $q->where('is_active', false)
                      ->orWhereDate('start_date', '>', today())
                      ->orWhereDate('end_date', '<', today());
                });
            }
        }

        $campaigns = $campaignsQuery->get();
            
        // Coupons
        $couponsQuery = Coupon::with(['users', 'usages.customer', 'usages.appointment.employee'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $couponsQuery->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $now = now();
            if ($request->input('status') === 'active') {
                $couponsQuery->where('expires_at', '>=', $now);
            } elseif ($request->input('status') === 'inactive') {
                $couponsQuery->where('expires_at', '<', $now);
            }
        }

        $coupons = $couponsQuery->get();

        $categories = ServiceCategory::where('branch_id', $branchId)->get();
        $services = \App\Models\Service::where('branch_id', $branchId)->get();
        $products = \App\Models\Product::where('branch_id', $branchId)->get();
        $cafeProducts = \App\Models\CafeProduct::where('branch_id', $branchId)->get();
        $users = User::all();

        $stats = [
            'total_campaigns'  => Campaign::where('branch_id', $branchId)->count(),
            'active_campaigns' => Campaign::where('branch_id', $branchId)->active()->count(),
            'total_coupons'    => Coupon::count(),
            'active_coupons'   => Coupon::where('expires_at', '>=', now())->count(),
        ];

        return view('campaigns.index', compact('campaigns', 'coupons', 'stats', 'categories', 'services', 'products', 'cafeProducts', 'users'));
    }

    public function create()
    {
        $branchId = session('active_branch_id', 1);

        $categories = ServiceCategory::where('branch_id', $branchId)->orderBy('name')->get();
        $services = \App\Models\Service::where('branch_id', $branchId)->orderBy('name')->get();
        $products = \App\Models\Product::where('branch_id', $branchId)->orderBy('name')->get();
        $cafeProducts = \App\Models\CafeProduct::where('branch_id', $branchId)->orderBy('name')->get();

        return view('campaigns.create', compact('categories', 'services', 'products', 'cafeProducts'));
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
            'discount_type'  => 'nullable|string|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'terms'          => 'nullable|string',
            'trigger_type'   => 'required|in:all,categories,services',
            'reward_type'    => 'required|in:discount,gift_product,gift_cafe',
            'reward_product_id' => 'nullable|exists:products,id',
            'reward_cafe_product_id' => 'nullable|exists:cafe_products,id',
            'categories'     => 'nullable|array',
            'categories.*'   => 'exists:service_categories,id',
            'services'       => 'nullable|array',
            'services.*'     => 'exists:services,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('campaigns', 'public');
        }

        $validated['branch_id'] = $branchId;
        $validated['is_active'] = $request->has('is_active');
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['priority'] = $validated['priority'] ?? 0;
        $validated['discount_value'] = $validated['discount_value'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? 'percentage';

        $campaign = Campaign::create($validated);

        if ($request->has('categories') && $validated['trigger_type'] === 'categories') {
            $campaign->categories()->sync($request->categories);
        } else {
            $campaign->categories()->detach();
        }

        if ($request->has('services') && $validated['trigger_type'] === 'services') {
            $campaign->services()->sync($request->services);
        } else {
            $campaign->services()->detach();
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
            'discount_type'  => 'nullable|string|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'terms'          => 'nullable|string',
            'trigger_type'   => 'required|in:all,categories,services',
            'reward_type'    => 'required|in:discount,gift_product,gift_cafe',
            'reward_product_id' => 'nullable|exists:products,id',
            'reward_cafe_product_id' => 'nullable|exists:cafe_products,id',
            'categories'     => 'nullable|array',
            'categories.*'   => 'exists:service_categories,id',
            'services'       => 'nullable|array',
            'services.*'     => 'exists:services,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('campaigns', 'public');
        }

        $validated['is_active'] = $request->has('is_active');
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['priority'] = $validated['priority'] ?? 0;
        $validated['discount_value'] = $validated['discount_value'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? 'percentage';

        $campaign->update($validated);

        if ($request->has('categories') && $validated['trigger_type'] === 'categories') {
            $campaign->categories()->sync($request->categories);
        } else {
            $campaign->categories()->detach();
        }

        if ($request->has('services') && $validated['trigger_type'] === 'services') {
            $campaign->services()->sync($request->services);
        } else {
            $campaign->services()->detach();
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

    public function createCoupon()
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get();

        return view('campaigns.coupons.create', compact('users'));
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
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $coupon = Coupon::create($validated);

        if ($request->has('user_ids')) {
            $coupon->users()->sync($request->user_ids);
        }

        return redirect()->to(route('campaigns.index') . '#couponsPanel')->with('success', 'Kupon kodu başarıyla oluşturuldu.');
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
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $coupon->update($validated);

        if ($request->has('user_ids')) {
            $coupon->users()->sync($request->user_ids);
        } else {
            $coupon->users()->detach();
        }

        return redirect()->to(route('campaigns.index') . '#couponsPanel')->with('success', 'Kupon kodu başarıyla güncellendi.');
    }

    public function destroyCoupon(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->to(route('campaigns.index') . '#couponsPanel')->with('success', 'Kupon kodu silindi.');
    }

    public function usages(Request $request, Campaign $campaign)
    {
        $this->authorizeBranchAccess($campaign->branch_id);
        
        $query = $campaign->usages()->with(['customer', 'appointment.employee'])->latest('used_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('used_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('used_at', '<=', $request->input('date_to'));
        }

        $usages = $query->paginate(20)->withQueryString();
        
        return view('campaigns.usages', compact('campaign', 'usages'));
    }

    public function couponUsages(Request $request, Coupon $coupon)
    {
        $query = $coupon->usages()->with(['customer', 'appointment.employee'])->latest('used_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('used_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('used_at', '<=', $request->input('date_to'));
        }

        $usages = $query->paginate(20)->withQueryString();
        
        return view('campaigns.coupon_usages', compact('coupon', 'usages'));
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
