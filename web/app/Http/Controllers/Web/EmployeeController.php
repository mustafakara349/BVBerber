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
        $query = Employee::forBranch($branchId)->with(['user.role', 'employeeTitle'])->withCount('appointments');

        if ($request->has('role_id') && $request->role_id != '') {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('role_id', $request->role_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(15)->withQueryString();
        $roles = Role::all();
        $titles = \App\Models\EmployeeTitle::forBranch($branchId)->get();
        return view('employees.index', compact('employees', 'roles', 'titles'));
    }

    public function create()
    {
        $roles = Role::all();
        $titles = \App\Models\EmployeeTitle::forBranch(session('active_branch_id', 1))->get();
        return view('employees.create', compact('roles', 'titles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults()],
            'employee_title_id' => 'nullable|exists:employee_titles,id',
            'salary_type' => 'required|in:fixed,commission,fixed_plus_commission,hourly',
            'salary_amount' => 'required|numeric',
            'commission_rate' => 'required|numeric',
            'hire_date' => 'required|date',
            'profile_photo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048',
        ]);

        $photoFile = null;
        $safePhotoFilename = null;

        if ($request->hasFile('profile_photo')) {
            $photoFile = $request->file('profile_photo');
            $realPath = $photoFile->getRealPath();

            $imageInfo = @getimagesize($realPath);
            if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                return back()->withErrors(['profile_photo' => 'Yüklenen dosya geçerli bir raster görsel (JPEG, PNG veya WebP) formatında olmalıdır.'])->withInput();
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($realPath);
            if (!in_array($detectedMime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return back()->withErrors(['profile_photo' => 'Geçersiz görsel türü tespit edildi.'])->withInput();
            }

            $safeExt = match ($imageInfo[2]) {
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
            };

            $safePhotoFilename = Str::uuid()->toString() . '.' . $safeExt;
        }

        $storedPhotoPath = null;
        if ($photoFile && $safePhotoFilename) {
            $storedPhotoPath = $photoFile->storeAs('profile_photos', $safePhotoFilename, 'public');
        }

        $user = User::create([
            'uuid' => Str::uuid(),
            'role_id' => $validated['role_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'profile_photo' => $storedPhotoPath,
        ]);

        Employee::create([
            'branch_id' => session('active_branch_id', 1),
            'user_id' => $user->id,
            'employee_code' => 'EMP-' . strtoupper(Str::random(6)),
            'employee_title_id' => $validated['employee_title_id'] ?? null,
            'hire_date' => $validated['hire_date'],
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
        $titles = \App\Models\EmployeeTitle::forBranch(session('active_branch_id', 1))->get();
        $employee->load('user');
        return view('employees.edit', compact('employee', 'roles', 'titles'));
    }

    public function update(Request $request, Employee $employee)
    {
        if ((int)$employee->branch_id !== (int)session('active_branch_id', 1)) {
            abort(403, 'Yetkisiz işlem.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $employee->user_id,
            'phone' => 'nullable|string',
            'role_id' => 'required|exists:roles,id',
            'employee_title_id' => 'nullable|exists:employee_titles,id',
            'salary_type' => 'required|in:fixed,commission,fixed_plus_commission,hourly',
            'salary_amount' => 'required|numeric',
            'commission_rate' => 'required|numeric',
            'hire_date' => 'required|date',
            'profile_photo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|mimetypes:image/jpeg,image/png,image/webp|max:2048',
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $userData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role_id' => $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        $employee->user->update($userData);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $realPath = $file->getRealPath();

            $imageInfo = @getimagesize($realPath);
            if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
                return back()->withErrors(['profile_photo' => 'Yüklenen dosya geçerli bir raster görsel (JPEG, PNG veya WebP) formatında olmalıdır.'])->withInput();
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->file($realPath);
            if (!in_array($detectedMime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                return back()->withErrors(['profile_photo' => 'Geçersiz görsel türü tespit edildi.'])->withInput();
            }

            $safeExt = match ($imageInfo[2]) {
                IMAGETYPE_JPEG => 'jpg',
                IMAGETYPE_PNG => 'png',
                IMAGETYPE_WEBP => 'webp',
            };

            $safeFilename = Str::uuid()->toString() . '.' . $safeExt;
            $oldPath = $employee->user->profile_photo;
            $path = $file->storeAs('profile_photos', $safeFilename, 'public');
            $employee->user->update(['profile_photo' => $path]);

            if ($oldPath && $oldPath !== $path && str_starts_with($oldPath, 'profile_photos/') && !str_contains($oldPath, '..')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
        }

        $employee->update([
            'employee_title_id' => $validated['employee_title_id'] ?? null,
            'hire_date' => $validated['hire_date'],
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

        if ($employee->user) {
            $employee->user->forceDelete();
        }
        $employee->forceDelete();
        return redirect()->route('employees.index')->with('success', 'Çalışan silindi.');
    }
}
