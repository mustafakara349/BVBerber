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
            'cancellation_limit_hours' => 'required|integer|min:0|max:168',
            'google_review_url' => ['nullable', 'url:https', 'max:500'],
        ]);

        $googleReviewUrl = filled($validated['google_review_url'] ?? null)
            ? trim($validated['google_review_url'])
            : null;

        BranchSetting::updateOrCreate(
            ['branch_id' => $branchId],
            [
                'cancellation_limit_hours' => $validated['cancellation_limit_hours'],
                'google_review_url'        => $googleReviewUrl,
                'appointment_interval'     => 30,    // Standart 30 dk
                'currency'                 => 'TRY', // Sabit TL
                'loyalty_enabled'          => true,  // Sadakat sistemi daima aktif
                'review_enabled'           => true,  // Değerlendirme sistemi daima aktif
                'online_payment_enabled'   => false, // Online ödeme devre dışı
            ]
        );

        return redirect()->route('settings.index')->with('success', 'Şube ayarları başarıyla güncellendi.');
    }
}
