<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $branchId = session('active_branch_id', 1);
        $query = Employee::forBranch($branchId)->with(['user.role'])->withCount('appointments');

        if ($request->has('role_id') && $request->role_id != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role_id', $request->role_id);
            });
        }

        $employees = $query->paginate(15);
        $roles = Role::all();
        return view('employees.index', compact('employees', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('employees.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
            'title' => 'nullable|string|max:100',
            'salary_type' => 'required|in:fixed,commission,fixed_plus_commission,hourly',
            'salary_amount' => 'required|numeric',
            'commission_rate' => 'required|numeric',
        ]);

        $user = User::create([
            'uuid' => Str::uuid(),
            'role_id' => $validated['role_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
        ]);

        Employee::create([
            'branch_id' => session('active_branch_id', 1),
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . strtoupper(Str::random(6)),
            'title' => $validated['title'],
            'hire_date' => now(),
            'salary_type' => $validated['salary_type'],
            'salary_amount' => $validated['salary_amount'],
            'commission_rate' => $validated['commission_rate'],
            'is_active' => true,
            'is_visible' => true,
        ]);

        return redirect()->route('employees.index')->with('success', 'Çalışan başarıyla eklendi.');
    }

    public function show(Employee $employee)
    {
        if ($employee->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $employee->load(['user.role', 'appointments.customer', 'appointments.appointmentServices.service', 'reviews']);
        
        $totalRevenue = $employee->appointments()->where('status', 'completed')->sum('total_price');
        $completedAppointments = $employee->appointments()->where('status', 'completed')->count();
        
        return view('employees.show', compact('employee', 'totalRevenue', 'completedAppointments'));
    }

    public function edit(Employee $employee)
    {
        if ($employee->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $roles = Role::all();
        $employee->load('user');
        return view('employees.edit', compact('employee', 'roles'));
    }

    public function update(Request $request, Employee $employee)
    {
        if ($employee->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'phone' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'title' => 'nullable|string|max:100',
            'salary_type' => 'required|in:fixed,commission,fixed_plus_commission,hourly',
            'salary_amount' => 'required|numeric',
            'commission_rate' => 'required|numeric',
        ]);

        $employee->user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id'],
        ]);

        if ($request->filled('password')) {
            $employee->user->update(['password' => Hash::make($request->password)]);
        }

        $employee->update([
            'title' => $validated['title'],
            'salary_type' => $validated['salary_type'],
            'salary_amount' => $validated['salary_amount'],
            'commission_rate' => $validated['commission_rate'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('employees.index')->with('success', 'Çalışan başarıyla güncellendi.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->branch_id !== session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Çalışan silindi.');
    }
}
