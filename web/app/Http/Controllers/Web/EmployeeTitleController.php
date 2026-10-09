<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTitle;
use Illuminate\Http\Request;

class EmployeeTitleController extends Controller
{
    public function index()
    {
        $branchId = session('active_branch_id', 1);
        $titles = EmployeeTitle::forBranch($branchId)->get();
        return response()->json($titles);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $branchId = session('active_branch_id', 1);

        $title = EmployeeTitle::create([
            'branch_id' => $branchId,
            'name' => $request->name,
        ]);

        return response()->json($title);
    }

    public function update(Request $request, EmployeeTitle $employeeTitle)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $branchId = session('active_branch_id', 1);

        if ($employeeTitle->branch_id !== $branchId) {
            abort(403);
        }

        $employeeTitle->update(['name' => $request->name]);

        return response()->json($employeeTitle);
    }

    public function destroy(EmployeeTitle $employeeTitle)
    {
        $branchId = session('active_branch_id', 1);

        if ($employeeTitle->branch_id !== $branchId) {
            abort(403);
        }

        $employeeTitle->delete();

        return response()->json(['success' => true]);
    }
}
