@props(['title' => '', 'subtitle' => ''])

<div>
    @php($user = auth()->user())
    <div class="container-fluid">
        <div class="row g-0">
            <aside class="col-lg-2 d-none d-lg-flex flex-column min-vh-100 bg-white border-end p-0">
                <div class="px-3 py-4 border-bottom">
                    <a href="/dashboard" wire:navigate class="text-decoration-none d-flex align-items-center gap-2">
                        <x-ui.brand subtitle="{{ $subtitle }}" />
                    </a>
                </div>

                <div class="flex-grow-1 px-3 py-3">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-3 border-bottom">
                        <x-ui.user-avatar :user="$user" />
                        <div class="lh-sm">
                            <div class="fw-semibold text-dark">{{ $user?->name }}</div>
                        </div>
                    </div>

                    <div class="small text-uppercase text-secondary fw-semibold mb-2">Menu</div>
                    <div class="nav nav-pills flex-column gap-1">
                        <a
                            href="/dashboard"
                            wire:navigate
                            class="nav-link {{ request()->is('dashboard') ? 'active' : 'text-secondary-emphasis' }}"
                        >
                            Dashboard
                        </a>

                        <div class="mt-2">
                            <div class="small text-uppercase text-secondary fw-semibold mb-2">Domain</div>
                            <div class="nav nav-pills flex-column gap-1 ps-2">
                                <a
                                    href="/domain/add"
                                    wire:navigate
                                    class="nav-link {{ request()->is('domain/add') ? 'active' : 'text-secondary-emphasis' }}"
                                >
                                    Add
                                </a>
                                <a
                                    href="/domain"
                                    wire:navigate
                                    class="nav-link {{ request()->is('domain') ? 'active' : 'text-secondary-emphasis' }}"
                                >
                                    Search
                                </a>
                                <a
                                    href="/domain/expired"
                                    wire:navigate
                                    class="nav-link {{ request()->is('domain/expired') ? 'active' : 'text-secondary-emphasis' }}"
                                >
                                    Expired
                                </a>
                                <a
                                    href="/domain/email"
                                    wire:navigate
                                    class="nav-link {{ request()->is('domain/email') ? 'active' : 'text-secondary-emphasis' }}"
                                >
                                    Email
                                </a>
                            </div>
                        </div>

                        <div class="mt-2">
                            <div class="small text-uppercase text-secondary fw-semibold mb-2">Tools</div>
                            <div class="nav nav-pills flex-column gap-1 ps-2">
                                <a
                                    href="{{ route('tools.backup') }}"
                                    wire:navigate
                                    class="nav-link {{ request()->is('tools/backup') ? 'active' : 'text-secondary-emphasis' }}"
                                >
                                    Backup
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-3 py-3 border-top">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="col-12 col-lg-10 px-0">
                <div class="border-bottom bg-white d-lg-none px-3 py-3">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <x-ui.brand :subtitle="$subtitle" />
                        <button
                            class="btn btn-outline-secondary"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#mobileSidebar"
                            aria-controls="mobileSidebar"
                        >
                            <i class="bi bi-list"></i>
                        </button>
                    </div>
                </div>

                <div class="p-3 p-lg-4">
                    <x-ui.page-header :title="$title" :subtitle="$subtitle" />
                    <div class="d-flex d-lg-none align-items-center gap-2 mb-3">
                        <x-ui.user-avatar :user="$user" size="2rem" />
                        <div class="lh-sm">
                            <div class="fw-semibold text-dark">{{ $user?->name }}</div>
                        </div>
                    </div>
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header border-bottom">
            <a href="/dashboard" wire:navigate class="text-decoration-none" id="mobileSidebarLabel">
                <x-ui.brand subtitle="{{ $subtitle }}" />
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column gap-3">
            <div class="d-flex align-items-center gap-2 pb-3 border-bottom">
                <x-ui.user-avatar :user="$user" />
                <div class="lh-sm">
                    <div class="fw-semibold text-dark">{{ $user?->name }}</div>
                </div>
            </div>
            <div>
                <div class="small text-uppercase text-secondary fw-semibold mb-2">Menu</div>
                <div class="nav nav-pills flex-column gap-1">
                    <a href="/dashboard" wire:navigate class="nav-link {{ request()->is('dashboard') ? 'active' : 'text-secondary-emphasis' }}">Dashboard</a>
                </div>
            </div>
            <div>
                <div class="small text-uppercase text-secondary fw-semibold mb-2">Domain</div>
                <div class="nav nav-pills flex-column gap-1 ps-2">
                    <a href="/domain/add" wire:navigate class="nav-link {{ request()->is('domain/add') ? 'active' : 'text-secondary-emphasis' }}">Add</a>
                    <a href="/domain" wire:navigate class="nav-link {{ request()->is('domain') ? 'active' : 'text-secondary-emphasis' }}">Search</a>
                    <a href="/domain/expired" wire:navigate class="nav-link {{ request()->is('domain/expired') ? 'active' : 'text-secondary-emphasis' }}">Expired</a>
                    <a href="/domain/email" wire:navigate class="nav-link {{ request()->is('domain/email') ? 'active' : 'text-secondary-emphasis' }}">Email</a>
                </div>
            </div>
            <div>
                <div class="small text-uppercase text-secondary fw-semibold mb-2">Tools</div>
                <div class="nav nav-pills flex-column gap-1 ps-2">
                    <a href="{{ route('tools.backup') }}" wire:navigate class="nav-link {{ request()->is('tools/backup') ? 'active' : 'text-secondary-emphasis' }}">Backup</a>
                </div>
            </div>
            <div class="mt-auto pt-3 border-top">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>
