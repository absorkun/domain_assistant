@props(['subtitle' => config('app.name')])

<div class="d-flex align-items-center gap-2 min-w-0">
    <img
        src="{{ asset('logo.png') }}"
        alt="{{ config('app.name') }}"
        class="flex-shrink-0"
        style="width: 2.75rem; height: 2.75rem; object-fit: contain;"
    >
    <div class="lh-sm min-w-0">
        <div class="fw-semibold text-dark text-truncate">{{ config('app.name') }}</div>
        <small class="text-secondary text-truncate d-block">{{ $subtitle }}</small>
    </div>
</div>
