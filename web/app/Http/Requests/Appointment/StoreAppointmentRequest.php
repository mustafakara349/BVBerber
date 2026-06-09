<?php

namespace App\Http\Requests\Appointment;

use App\Enums\AppointmentSource;
use App\Enums\PaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id'               => ['required', 'exists:branches,id'],
            'customer_id'             => ['required', 'exists:users,id'],
            'employee_id'             => ['required', 'exists:employees,id'],
            'start_at'                => ['required', 'date', 'after:now'],
            'source'                  => ['sometimes', Rule::enum(AppointmentSource::class)],
            'payment_method'          => ['nullable', Rule::enum(PaymentMethod::class)],
            'customer_note'           => ['nullable', 'string', 'max:1000'],
            'internal_note'           => ['nullable', 'string', 'max:1000'],
            'discount_amount'         => ['nullable', 'numeric', 'min:0'],
            'services'                => ['required', 'array', 'min:1'],
            'services.*.service_id'   => ['required', 'exists:services,id'],
            'services.*.quantity'     => ['sometimes', 'integer', 'min:1'],
            // unit_price ve duration_minutes client'tan kabul edilmez;
            // backend'de veritabanından hesaplanır.
        ];
    }
}
