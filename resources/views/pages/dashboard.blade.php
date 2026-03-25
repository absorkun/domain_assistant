<?php

use App\Models\Domain;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function user(): ?User
    {
        return auth()->user();
    }

    #[Computed]
    public function totalDomains(): int
    {
        return Domain::query()->count();
    }

    #[Computed]
    public function activeDomains(): int
    {
        return Domain::query()->where('status', 'active')->count();
    }

    #[Computed]
    public function dnssecDomains(): int
    {
        return Domain::query()->where('dnssec', true)->count();
    }

    #[Computed]
    public function expiredDomains(): int
    {
        return Domain::query()->where('tgl_exp', '<', today())->count();
    }
};
?>

<x-ui.admin-shell title="Dashboard" subtitle="Overview">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body d-flex align-items-center gap-3">
            <x-ui.user-avatar :user="$this->user()" size="3rem" />
            <div class="lh-sm">
                <div class="fw-semibold text-dark">{{ $this->user()?->full_name }}</div>
                <small class="text-secondary">{{ $this->user()?->name }}</small>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <x-ui.stat-card icon="globe2" label="Total Domain" value="{{ $this->totalDomains() }}" />
        </div>
        <div class="col-md-4">
            <x-ui.stat-card icon="check-circle" label="Domain Active" value="{{ $this->activeDomains() }}" />
        </div>
        <div class="col-md-4">
            <x-ui.stat-card icon="shield-check" label="DNSSEC Aktif" value="{{ $this->dnssecDomains() }}" />
        </div>
    </div>
    <div class="row g-3 mt-3">
        <div class="col-md-4">
            <x-ui.stat-card icon="search" label="Domain Kadaluarsa" value="{{ $this->expiredDomains() }}" />
        </div>
    </div>
</x-ui.admin-shell>
