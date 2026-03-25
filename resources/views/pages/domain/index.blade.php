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

<x-ui.admin-shell title="Dashboard Domain" subtitle="Auth">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="mx-auto" style="max-width: 560px;">
                <h2 class="h4 text-center mb-4">Cek domain</h2>
                <form wire:submit="submit" class="d-flex justify-content-center">
                    <div class="input-group" style="max-width: 560px;">
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Cari domain">
                        <button class="btn btn-primary" type="submit" aria-label="Cari domain">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            @if ($this->domain)
                <div class="mt-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <div class="text-secondary small">Hasil pencarian</div>
                            <h3 class="h5 mb-0 d-flex align-items-center gap-2">
                                <span>{{ $this->domain->domain }}</span>
                                <a href="/domain/{{ $this->domain->id }}" wire:navigate class="btn btn-outline-primary btn-sm">Detail</a>
                            </h3>
                        </div>
                    </div>
                    <div style="max-width: 720px;">
                        <div class="d-grid gap-2">
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">Nameserver</div>
                                <div>
                                    @foreach (preg_split('/\s*,\s*/', (string) ($this->domain->name_srv ?? '')) as $nameserver)
                                        @if ($nameserver !== '')
                                            <div class="text-break" style="overflow-wrap: anywhere;">{{ $nameserver }}</div>
                                        @endif
                                    @endforeach
                                    @if (blank($this->domain->name_srv))
                                        <div>-</div>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">Status</div>
                                <div>{{ $this->domain->status ?? '-' }}</div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">DNSSEC</div>
                                <div>{{ $this->domain->dnssec ? 'Aktif' : 'Non-aktif' }}</div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">Website</div>
                                <div class="text-break" style="overflow-wrap: anywhere;">{{ $this->domain->website ?? '-' }}</div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">IP Addr.</div>
                                <div>{{ $this->domain->dns_a ?? '-' }}</div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">Tgl. Registrasi</div>
                                <div>{{ $this->domain->tgl_reg?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">Tgl. Kadaluarsa</div>
                                <div>{{ $this->domain->tgl_exp?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            <div class="d-flex align-items-start">
                                <div class="text-secondary flex-shrink-0" style="width: 140px;">Email</div>
                                <div class="text-break" style="overflow-wrap: anywhere;">{{ $this->domain->user->email ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif ($this->submitted && $this->search)
                <div class="mt-5 alert alert-warning border-0 shadow-sm mb-0">'{{ $this->search }}' tidak ditemukan</div>
            @endif
        </div>
    </div>
</x-ui.admin-shell>
