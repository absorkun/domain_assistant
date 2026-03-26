@props(['title' => '', 'subtitle' => ''])

<div class="min-vh-100 d-flex align-items-center py-4 py-lg-5 bg-body-tertiary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-7">
                <div class="d-flex justify-content-center justify-content-md-start mb-4">
                    <x-ui.brand :subtitle="$subtitle" />
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4">
                            <div class="text-primary small text-uppercase fw-semibold mb-2">{{ $subtitle }}</div>
                            <h1 class="h3 mb-1 text-dark">{{ $title }}</h1>
                        </div>
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
