<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\BranchSetting;
use App\Models\Branch;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $branchId = session('active_branch_id', 1);

        $globalSettings = Setting::all()->pluck('setting_value', 'setting_key')->toArray();
        $branchSetting = BranchSetting::where('branch_id', $branchId)->first();
        $branches = Branch::all();

        return view('settings.index', compact('globalSettings', 'branchSetting', 'branches'));
    }

    public function updateGlobal(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value);
        }

        return redirect()->route('settings.index')->with('success', 'Global ayarlar başarıyla güncellendi.');
    }

    public function updateBranch(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $validated = $request->validate([
            'appointment_interval' => 'required|integer|in:15,30,45,60',
            'cancellation_limit_hours' => 'required|integer|min:0',
            'currency' => 'required|string|max:5',
        ]);

        $validated['loyalty_enabled'] = $request->has('loyalty_enabled');
        $validated['review_enabled'] = $request->has('review_enabled');
        $validated['online_payment_enabled'] = $request->has('online_payment_enabled');

        BranchSetting::updateOrCreate(
            ['branch_id' => $branchId],
            $validated
        );

        return redirect()->route('settings.index')->with('success', 'Şube ayarları başarıyla güncellendi.');
    }
}
