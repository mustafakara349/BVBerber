<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function __construct(private LoyaltyService $loyaltyService) {}

    public function show(User $customer)
    {
        // Yalnızca müşteri rolü olanları göster
        if ($customer->role->name !== 'customer') {
            abort(404);
        }

        $account = LoyaltyAccount::where('customer_id', $customer->id)->first();
        
        $transactions = collect();
        if ($account) {
            $transactions = LoyaltyTransaction::where('loyalty_account_id', $account->id)
                ->latest()
                ->paginate(15);
        } else {
            $transactions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        }

        return view('customers.loyalty', compact('customer', 'account', 'transactions'));
    }

    public function store(Request $request, User $customer)
    {
        $validated = $request->validate([
            'points' => 'required|integer|not_in:0',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $desc = $validated['description'] ?? 'Yönetici tarafından manuel işlem';
            $this->loyaltyService->manualAdjustment($customer->id, $validated['points'], $desc);
            return back()->with('success', 'Sadakat puanı başarıyla güncellendi.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
