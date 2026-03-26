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
        if (!$this->search) {
            return null;
        }

        return Domain::query()
            ->with('user:id,email')
            ->where('domain', strtolower($this->search))
            ->first();
    }

    public function submit(): void
    {
        $this->submitted = true;
    }
};
?>

<x-ui.admin-shell title="Dashboard Domain" subtitle="Domain">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="mx-auto" style="max-width: 640px;">
                <h2 class="h4 text-center mb-4">Cek domain</h2>
                <form wire:submit="submit" class="d-flex justify-content-center">
                    <div class="input-group">
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari domain">
                        <button class="btn btn-primary" type="submit" aria-label="Cari domain">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            @if ($this->domain)
                <div class="mt-5" style="max-width: 760px;">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <div class="text-secondary small">Hasil pencarian</div>
                            <h3 class="h5 mb-0">{{ $this->domain->domain }}</h3>
                        </div>
                        <a href="{{ route('domain.edit', $this->domain->id) }}" wire:navigate class="btn btn-outline-primary btn-sm">Detail</a>
                    </div>

                    <dl class="row g-2 mb-0">
                        <dt class="col-4 col-md-3 text-secondary fw-normal">Nameserver</dt>
                        <dd class="col-8 col-md-9 mb-0">
                            @forelse (preg_split('/\s*,\s*/', (string) ($this->domain->name_srv ?? '')) as $nameserver)
                                @if ($nameserver !== '')
                                    <div class="text-break">{{ $nameserver }}</div>
                                @endif
                            @empty
                                <div>-</div>
                            @endforelse
                        </dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">Status</dt>
                        <dd class="col-8 col-md-9 mb-0">{{ $this->domain->status ?? '-' }}</dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">DNSSEC</dt>
                        <dd class="col-8 col-md-9 mb-0">{{ $this->domain->dnssec ? 'Aktif' : 'Non-aktif' }}</dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">Website</dt>
                        <dd class="col-8 col-md-9 mb-0 text-break">{{ $this->domain->website ?? '-' }}</dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">IP Addr.</dt>
                        <dd class="col-8 col-md-9 mb-0">{{ $this->domain->dns_a ?? '-' }}</dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">Tgl. Registrasi</dt>
                        <dd class="col-8 col-md-9 mb-0">{{ $this->domain->tgl_reg?->format('d/m/Y') ?? '-' }}</dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">Tgl. Kadaluarsa</dt>
                        <dd class="col-8 col-md-9 mb-0">{{ $this->domain->tgl_exp?->format('d/m/Y') ?? '-' }}</dd>
                        <dt class="col-4 col-md-3 text-secondary fw-normal">Email</dt>
                        <dd class="col-8 col-md-9 mb-0 text-break">{{ $this->domain->user->email ?? '-' }}</dd>
                    </dl>
                </div>
            @elseif ($this->submitted && $this->search)
                <div class="mt-5 alert alert-warning border-0 shadow-sm mb-0">'{{ $this->search }}' tidak ditemukan</div>
            @endif
        </div>
    </div>
</x-ui.admin-shell>
