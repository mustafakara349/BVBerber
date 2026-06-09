@extends('layouts.app')
@section('title', 'Çalışanlar - B&V Barber')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fs-3 mb-1">Çalışanlar</h1>
                <p class="text-muted">Personel listesini görüntüleyin ve yönetin.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <form action="{{ route('employees.index') }}" method="GET" class="d-inline-block shadow-sm" id="roleFilterForm">
                    <select name="role_id" class="form-select border-0" onchange="document.getElementById('roleFilterForm').submit()">
                        <option value="">Tüm Unvanlar</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('employees.create') }}" class="btn btn-success rounded-pill px-4 shadow-sm">
                    <i class="ti ti-plus me-1"></i> Yeni Çalışan Ekle
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0">
                        <thead class="table-light text-muted">
                            <tr>
                                <th class="ps-4 py-3 rounded-start">Çalışan</th>
                                <th class="py-3">Unvan</th>
                                <th class="py-3">İletişim</th>
                                <th class="py-3">Maaş Tipi</th>
                                <th class="py-3">Randevular</th>
                                <th class="py-3">Durum</th>
                                <th class="text-end pe-4 py-3 rounded-end">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        @if($employee->user && $employee->user->profile_photo)
                                            <img src="{{ asset($employee->user->profile_photo) }}" alt="{{ $employee->full_name }}" class="rounded-circle me-3 object-fit-cover shadow-sm" width="48" height="48">
                                        @else
                                            <div class="avatar bg-primary-subtle text-primary me-3 rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 shadow-sm" style="width: 48px; height: 48px;">
                                                @if($employee->user)
                                                    {{ mb_substr($employee->user->first_name, 0, 1) }}{{ mb_substr($employee->user->last_name, 0, 1) }}
                                                @else
                                                    <i class="ti ti-user"></i>
                                                @endif
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-dark">{{ $employee->full_name }}</h6>
                                            <small class="text-muted">Kod: {{ $employee->employee_code }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($employee->title)
                                        <span class="badge bg-info-subtle text-info px-2 py-1 border border-info-subtle">{{ $employee->title }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($employee->user)
                                    <div class="d-flex flex-column gap-1">
                                        <div class="d-flex align-items-center text-dark" title="{{ $employee->user->email }}">
                                            <i class="ti ti-mail text-muted me-2"></i>
                                            <span class="fs-7 text-truncate" style="max-width: 150px;">{{ $employee->user->email }}</span>
                                        </div>
                                        @if($employee->user->phone)
                                        <div class="d-flex align-items-center text-dark">
                                            <i class="ti ti-phone text-muted me-2"></i>
                                            <span class="fs-7">{{ $employee->user->phone }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($employee->salary_type == App\Enums\SalaryType::Fixed)
                                            <span class="fw-medium text-dark d-flex align-items-center"><i class="ti ti-cash text-success me-1"></i> Sabit Maaş</span>
                                        @elseif($employee->salary_type == App\Enums\SalaryType::Commission)
                                            <span class="fw-medium text-dark d-flex align-items-center"><i class="ti ti-percentage text-warning me-1"></i> Prim Usulü</span>
                                        @elseif($employee->salary_type == App\Enums\SalaryType::FixedPlusCommission)
                                            <span class="fw-medium text-dark d-flex align-items-center"><i class="ti ti-wallet text-info me-1"></i> Maaş + Prim</span>
                                        @elseif($employee->salary_type == App\Enums\SalaryType::Hourly)
                                            <span class="fw-medium text-dark d-flex align-items-center"><i class="ti ti-time text-primary me-1"></i> Saatlik</span>
                                        @endif
                                        <small class="text-muted mt-1">{{ number_format($employee->salary_amount, 2) }} ₺</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-semibold d-flex align-items-center">
                                            <i class="ti ti-calendar me-2"></i> {{ $employee->appointments_count }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($employee->is_active)
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2"><i class="ti ti-check-circle me-1"></i>Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-2"><i class="ti ti-x me-1"></i>Pasif</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group shadow-sm">
                                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-light text-info" title="Görüntüle">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-light text-primary" title="Düzenle">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bu çalışanı silmek istediğinize emin misiniz?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger" style="border-top-left-radius: 0; border-bottom-left-radius: 0;" title="Sil">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <div class="bg-light rounded-circle p-4 mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="ti ti-users fs-1 text-secondary"></i>
                                        </div>
                                        <h5 class="fw-medium text-dark">Kayıtlı çalışan bulunamadı</h5>
                                        <p class="mb-0">Sisteme henüz bir çalışan eklenmemiş.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($employees->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $employees->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
