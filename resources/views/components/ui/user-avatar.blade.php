@props(['user' => null, 'size' => '2.5rem'])

<div
    class="rounded-circle bg-primary-subtle text-primary fw-semibold d-inline-flex align-items-center justify-content-center flex-shrink-0"
    style="width: {{ $size }}; height: {{ $size }};"
>
    {{ $user?->initials() ?? 'U' }}
</div>
