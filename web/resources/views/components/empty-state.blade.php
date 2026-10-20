@props(['icon' => 'ti ti-database-off', 'title' => 'Kayıt bulunamadı', 'message' => null, 'colspan' => 10])

<tr>
    <td colspan="{{ $colspan }}" class="text-center py-5">
        <div class="d-flex flex-column align-items-center justify-content-center text-muted py-4">
            <div class="rounded-circle d-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 72px; height: 72px; background-color: #f8fafc; border: 1px solid #e2e8f0;">
                <i class="{{ $icon }} fs-1 text-secondary opacity-75"></i>
            </div>
            <h6 class="fw-bold text-dark mb-1">{{ $title }}</h6>
            @if($message)
                <p class="small text-muted mb-3" style="max-width: 380px;">{{ $message }}</p>
            @endif
            @if($slot->isNotEmpty())
                <div class="mt-2">
                    {{ $slot }}
                </div>
            @endif
        </div>
    </td>
</tr>
