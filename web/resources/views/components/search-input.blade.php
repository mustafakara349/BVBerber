@props(['name' => 'search', 'placeholder' => 'Ara...', 'value' => null, 'resetUrl' => null])
@php
    $currentVal = $value ?? request($name);
@endphp

<div {{ $attributes->merge(['class' => 'input-group-search flex-grow-1']) }}>
    <span class="input-group-text ps-3 pe-1">
        <i class="ti ti-search fs-5"></i>
    </span>
    <input type="text" name="{{ $name }}" class="form-control ps-2 py-2" placeholder="{{ $placeholder }}" value="{{ $currentVal }}">
    @if(!empty($currentVal))
        <a href="{{ $resetUrl ?? url()->current() }}" class="btn btn-link text-muted border-0 pe-3 d-flex align-items-center text-decoration-none" title="Temizle">
            <i class="ti ti-x"></i>
        </a>
    @endif
</div>
