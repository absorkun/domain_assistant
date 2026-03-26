@props(['icon' => 'circle', 'label' => '', 'value' => ''])

<div class="card border-0 shadow-sm h-100">
    <div class="card-body d-flex align-items-center gap-3 p-3 p-md-4">
        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 3rem; height: 3rem;">
            <i class="bi bi-{{ $icon }}"></i>
        </div>
        <div class="min-w-0">
            <div class="text-secondary small text-truncate">{{ $label }}</div>
            <div class="fw-semibold text-dark text-truncate">{{ $value }}</div>
        </div>
    </div>
</div>
