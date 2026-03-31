<?php

use App\Models\Domain;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url]
    public ?string $search = null;

    public bool $submitted = false;

    #[Computed]
    public function domain(): ?Domain
    {
        if (! $this->search) {
            return null;
        }

        return Domain::query()
            ->with('user:id,name,full_name,email')
            ->where('domain', strtolower(trim($this->search)))
            ->first();
    }

    public function submit(): void
    {
        $this->submitted = true;
    }
};
?>

<x-ui.admin-shell title="Kontak" subtitle="User">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="mx-auto" style="max-width: 640px;">
                <h2 class="h4 text-center mb-2">Kontak pengguna domain</h2>
                <p class="text-secondary text-center mb-4">Cari berdasarkan domain yang terhubung ke user.</p>

                <form wire:submit="submit" class="d-flex justify-content-center">
                    <div class="input-group">
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Masukkan domain">
                        <button class="btn btn-primary" type="submit" aria-label="Cari kontak">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            @if ($this->domain)
                <div class="mt-5 mx-auto" style="max-width: 640px;">
                    <div class="card border-0 bg-body-tertiary">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-start gap-3 mb-4">
                                <x-ui.user-avatar :user="$this->domain->user" size="2.75rem" />
                                <div class="min-w-0">
                                    <div class="fw-semibold text-dark">{{ $this->domain->user->full_name ?? $this->domain->user->name }}</div>
                                    <div class="text-secondary small text-break">{{ $this->domain->domain }}</div>
                                </div>
                            </div>

                            <dl class="row g-2 mb-0">
                                <dt class="col-4 col-md-3 text-secondary fw-normal">Username</dt>
                                <dd class="col-8 col-md-9 mb-0 text-break">{{ $this->domain->user->name ?? '-' }}</dd>

                                <dt class="col-4 col-md-3 text-secondary fw-normal">Nama</dt>
                                <dd class="col-8 col-md-9 mb-0 text-break">{{ $this->domain->user->full_name ?? '-' }}</dd>

                                <dt class="col-4 col-md-3 text-secondary fw-normal">Email</dt>
                                <dd class="col-8 col-md-9 mb-0 text-break">{{ $this->domain->user->email ?? '-' }}</dd>

                                <dt class="col-4 col-md-3 text-secondary fw-normal">Domain</dt>
                                <dd class="col-8 col-md-9 mb-0 text-break">{{ $this->domain->domain }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @elseif ($this->submitted && $this->search)
                <div class="mt-5 mx-auto alert alert-warning border-0 shadow-sm mb-0" style="max-width: 640px;">
                    '{{ $this->search }}' tidak ditemukan
                </div>
            @endif
        </div>
    </div>
</x-ui.admin-shell>
