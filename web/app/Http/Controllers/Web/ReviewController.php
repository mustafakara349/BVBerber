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

        $query = Review::whereHas('appointment', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })->with(['customer', 'employee.user', 'appointment']);

        if ($request->filled('rating')) {
            $query->where('rating', $request->get('rating'));
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->get('employee_id'));
        }

        $reviews = $query->latest('id')->get();
        $barbers = Employee::forBranch($branchId)->with('user')->get();

        $totalReviews = $reviews->count();
        $avgRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 5.0;

        $stats = [
            'total' => $totalReviews,
            'avg' => $avgRating,
            'stars' => [
                5 => $reviews->where('rating', 5)->count(),
                4 => $reviews->where('rating', 4)->count(),
                3 => $reviews->where('rating', 3)->count(),
                2 => $reviews->where('rating', 2)->count(),
                1 => $reviews->where('rating', 1)->count(),
            ],
        ];

        return view('reviews.index', compact('reviews', 'barbers', 'stats'));
    }

    public function destroy(Review $review)
    {
        // Review'ın bağlı olduğu randevu üzerinden şube kontrolü
        $review->loadMissing('appointment');
        $branchId = session('active_branch_id', 1);

        if ($review->appointment && $review->appointment->branch_id !== $branchId) {
            abort(403, 'Bu değerlendirmeye erişim yetkiniz bulunmamaktadır.');
        }

        $review->delete();
        return redirect()->route('reviews.index')->with('success', 'Değerlendirme başarıyla silindi.');
    }
}
