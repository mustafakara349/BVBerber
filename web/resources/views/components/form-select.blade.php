@props(['name', 'label' => null, 'value' => null, 'placeholder' => null, 'pill' => true])

<div class="d-flex flex-column">
    @if($label)
        <label class="form-label text-secondary small fw-medium mb-1">{{ $label }}</label>
    @endif
    <select name="{{ $name }}" {{ $attributes->merge(['class' => 'form-select ' . ($pill ? 'form-select-pill ' : '') . 'bg-white shadow-sm']) }}>
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
</div>
