@props(['subtitle' => config('app.name')])

<div class="d-flex align-items-center gap-2">
    <img
        src="{{ asset('logo.png') }}"
        alt="{{ config('app.name') }}"
        class="flex-shrink-0"
        style="width: 2.75rem; height: 2.75rem; object-fit: contain;"
    >
    <div class="lh-sm">
        <div class="fw-semibold text-dark">{{ config('app.name') }}</div>
        <small class="text-secondary">{{ $subtitle }}</small>
    </div>
</div>
