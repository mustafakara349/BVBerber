@props(['name', 'label' => null, 'value' => null, 'pill' => false])

<div class="d-flex flex-column">
    @if($label)
        <label class="form-label text-secondary small fw-medium mb-1">{{ $label }}</label>
    @endif
    <input type="date" name="{{ $name }}" value="{{ $value ?? request($name) }}" {{ $attributes->merge(['class' => 'form-control ' . ($pill ? 'form-control-pill ' : '') . 'bg-white shadow-sm']) }}>
</div>
