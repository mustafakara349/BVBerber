<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Employee;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);

        $baseQuery = Review::whereHas('appointment', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        });

        $allBranchReviews = (clone $baseQuery)->get(['rating', 'redirected_to_google_at']);
        $totalReviews = $allBranchReviews->count();
        $avgRating = $totalReviews > 0 ? round($allBranchReviews->avg('rating'), 1) : 5.0;

        $stats = [
            'total' => $totalReviews,
            'avg' => $avgRating,
            'low_rating_count' => $allBranchReviews->where('rating', '<=', 3)->count(),
            'google_redirected_count' => $allBranchReviews->whereNotNull('redirected_to_google_at')->count(),
            'stars' => [
                5 => $allBranchReviews->where('rating', 5)->count(),
                4 => $allBranchReviews->where('rating', 4)->count(),
                3 => $allBranchReviews->where('rating', 3)->count(),
                2 => $allBranchReviews->where('rating', 2)->count(),
                1 => $allBranchReviews->where('rating', 1)->count(),
            ],
        ];

        $query = (clone $baseQuery)->with(['customer', 'employee.user', 'appointment.appointmentServices.service']);

        if ($request->filled('rating')) {
            $query->where('rating', $request->get('rating'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->get('employee_id'));
        }

        if ($request->filled('nps_status')) {
            if ($request->get('nps_status') === 'low_rating') {
                $query->where('rating', '<=', 3);
            } elseif ($request->get('nps_status') === 'google_redirected') {
                $query->whereNotNull('redirected_to_google_at');
            } elseif ($request->get('nps_status') === 'promoter') {
                $query->where('rating', '>=', 4);
            }
        }

        $perPage = max(5, min(100, (int) $request->get('per_page', 10)));
        $reviews = $query->latest('id')->paginate($perPage)->withQueryString();
        $barbers = Employee::forBranch($branchId)->with('user')->get();

        return view('reviews.index', compact('reviews', 'barbers', 'stats'));
    }

    public function show(Review $review)
    {
        $review->loadMissing([
            'customer',
            'employee.user',
            'appointment.appointmentServices.service.category',
            'appointment.branch',
            'appointment.payments',
            'appointment.campaign',
            'appointment.coupon',
        ]);

        $branchId = session('active_branch_id', 1);

        if ($review->appointment && $review->appointment->branch_id !== $branchId) {
            abort(403, 'Bu değerlendirmeye erişim yetkiniz bulunmamaktadır.');
        }

        return view('reviews.show', compact('review'));
    }

    public function destroy(Review $review)
    {
        $review->loadMissing('appointment');
        $branchId = session('active_branch_id', 1);

        if ($review->appointment && $review->appointment->branch_id !== $branchId) {
            abort(403, 'Bu değerlendirmeye erişim yetkiniz bulunmamaktadır.');
        }

        $review->delete();
        return redirect()->route('reviews.index')->with('success', 'Değerlendirme başarıyla silindi.');
    }
}
