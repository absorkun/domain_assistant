@props(['title' => '', 'subtitle' => ''])

<div class="min-vh-100 d-flex align-items-center py-4 py-lg-5">
    <div class="container position-relative" style="z-index: 1;">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 mb-lg-5">
            <x-ui.brand :subtitle="$subtitle" />
            <a href="{{ route('login') }}" wire:navigate class="btn btn-outline-secondary align-self-start align-self-md-center">Login</a>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5 text-center text-md-start">
                        <div class="text-primary small text-uppercase fw-semibold mb-3">Domain Assistant</div>
                        <h1 class="display-6 fw-bold mb-3 text-dark">{{ $title }}</h1>
                        <p class="lead text-secondary mb-4">{{ $slot }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
