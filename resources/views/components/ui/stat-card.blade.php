@props(['icon' => 'circle', 'label' => '', 'value' => ''])

<div class="card border-0 shadow-sm h-100">
    <div class="card-body d-flex align-items-center gap-3 p-3 p-md-4">
        <div class="bg-body-tertiary text-primary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 3rem; height: 3rem;">
            <i class="bi bi-{{ $icon }}"></i>
        </div>
        <div>
            <div class="text-secondary small">{{ $label }}</div>
            <div class="fw-semibold text-dark">{{ $value }}</div>
        </div>
    </div>
</div>
