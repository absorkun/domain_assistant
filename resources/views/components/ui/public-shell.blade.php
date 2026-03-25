@props(['title' => '', 'subtitle' => ''])

<div class="min-vh-100">
    <div class="position-relative" style="z-index: 1;">
        <div class="container py-4 py-lg-5">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-3 mb-4 mb-md-5">
                <x-ui.brand :subtitle="$subtitle" />
                <a href="/login" wire:navigate class="btn btn-outline-secondary">Login</a>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-8 text-center">
                    <div class="text-primary small text-uppercase fw-semibold mb-3">Domain Assistant</div>
                    <h1 class="display-5 fw-bold mb-3 text-dark">{{ $title }}</h1>
                    <p class="lead text-secondary mb-4">{{ $slot }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
