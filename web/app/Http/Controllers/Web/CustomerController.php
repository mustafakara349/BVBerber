<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::customers()->withCount('appointments')->paginate(15);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'password' => 'nullable|string|min:6',
        ]);

        $customerRole = Role::where('slug', 'customer')->first();

        User::create([
            'uuid' => Str::uuid(),
            'role_id' => $customerRole->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'password' => Hash::make($validated['password'] ?? Str::random(10)),
            'status' => 'active',
        ]);

        return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla eklendi.');
    }

    public function show($id)
    {
        $customer = User::customers()->with(['appointments.employee.user', 'appointments.appointmentServices.service'])->findOrFail($id);
        
        $totalSpent = $customer->appointments()->where('status', 'completed')->sum('total_price');
        $completedAppointments = $customer->appointments()->where('status', 'completed')->count();
        
        return view('customers.show', compact('customer', 'totalSpent', 'completedAppointments'));
    }

    public function edit($id)
    {
        $customer = User::customers()->findOrFail($id);
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = User::customers()->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $customer->id,
            'phone' => 'nullable|string',
            'gender' => 'nullable|in:male,female,other',
            'birth_date' => 'nullable|date',
            'status' => 'required|in:active,inactive,blocked',
        ]);

        $customer->update($validated);

        if ($request->filled('password')) {
            $customer->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('customers.index')->with('success', 'Müşteri başarıyla güncellendi.');
    }

    public function destroy($id)
    {
        $customer = User::customers()->findOrFail($id);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Müşteri silindi.');
    }
}
