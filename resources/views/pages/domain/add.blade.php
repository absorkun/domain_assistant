<?php

use App\Models\Domain;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $limit = 5;
    public string $domain = '';
    public ?string $name_srv = null;
    public ?string $status = null;
    public bool $dnssec = false;
    public ?string $tgl_reg = null;
    public ?string $tgl_exp = null;
    public ?string $dns_a = null;
    public ?string $website = null;

    public ?int $highlightedId = null;

    public function mount(): void
    {
        $this->limit = 5;
    }

    #[Computed]
    public function latestDomains(): Collection
    {
        return Domain::query()
            ->with('user:id,email')
            ->orderByDesc('id')
            ->limit(max(5, min(50, (int) $this->limit)))
            ->get();
    }

    public function rules(): array
    {
        return [
            'domain' => ['required', 'string', 'max:255', Rule::unique('domains', 'domain')],
            'name_srv' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:40'],
            'dnssec' => ['boolean'],
            'tgl_reg' => ['nullable', 'date'],
            'tgl_exp' => ['nullable', 'date'],
            'dns_a' => ['nullable', 'string', 'max:40'],
            'website' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        $record = Domain::query()->create([
            'domain' => strtolower($data['domain']),
            'user_id' => auth()->id(),
            'name_srv' => $data['name_srv'],
            'status' => $data['status'],
            'dnssec' => $data['dnssec'],
            'tgl_reg' => $data['tgl_reg'],
            'tgl_exp' => $data['tgl_exp'],
            'dns_a' => $data['dns_a'],
            'website' => $data['website'],
        ]);

        $this->highlightedId = $record->id;
        $this->reset(['domain', 'name_srv', 'status', 'dnssec', 'tgl_reg', 'tgl_exp', 'dns_a', 'website']);
        $this->dispatch('domain-created', domain: $record->domain);
    }
};
?>

<x-ui.admin-shell title="Featured List Domain" subtitle="Domain">
    <div class="card border-0 shadow-sm w-100 mb-4">
        <div class="card-body p-3 p-md-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                <div class="d-flex flex-column gap-1">
                    <div class="text-secondary small text-uppercase fw-semibold">Featured list</div>
                    <h2 class="h4 mb-0">Domain terbaru</h2>
                    <div class="mt-3 mb-2 d-flex align-items-center gap-2">
                        <label class="form-label small text-secondary mb-0">Jumlah data</label>
                        <select wire:model.live="limit" class="form-select form-select-sm w-auto">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                            <option value="25">25</option>
                            <option value="30">30</option>
                            <option value="35">35</option>
                            <option value="40">40</option>
                            <option value="45">45</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <button type="button" class="btn btn-primary mt-lg-4" data-bs-toggle="modal" data-bs-target="#addDomainModal">
                        Tambah
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Domain</th>
                            <th>Status</th>
                            <th>DNSSEC</th>
                            <th>Nameserver</th>
                            <th>Tgl Regis</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->latestDomains as $record)
                            <tr class="{{ $highlightedId === $record->id ? 'table-primary' : '' }}">
                                <td class="fw-semibold text-dark">
                                    <a href="https://{{ $record->domain }}" target="_blank" rel="noopener" class="text-decoration-none text-dark text-truncate d-inline-block w-100">
                                        {{ $record->domain }}
                                    </a>
                                </td>
                                <td>{{ $record->status ?? '-' }}</td>
                                <td>
                                    @if ($record->dnssec)
                                        <i class="bi bi-shield-check text-success"></i>
                                    @else
                                        <i class="bi bi-shield-x text-secondary"></i>
                                    @endif
                                </td>
                                <td class="text-break">{{ $record->name_srv ?? '-' }}</td>
                                <td>{{ $record->tgl_reg?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addDomainModal" tabindex="-1" aria-labelledby="addDomainModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold mb-1">Tambah domain</div>
                        <h3 class="h5 mb-0" id="addDomainModalLabel">Masukkan domain baru</h3>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-md-5">
                    <form id="domain-form" wire:submit="save" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Domain</label>
                            <input type="text" wire:model="domain" class="form-control">
                            @error('domain')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Nameserver</label>
                            <input type="text" wire:model="name_srv" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Status</label>
                            <input type="text" wire:model="status" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal Registrasi</label>
                            <input type="date" wire:model="tgl_reg" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal Expired</label>
                            <input type="date" wire:model="tgl_exp" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">DNS A</label>
                            <input type="text" wire:model="dns_a" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Website</label>
                            <input type="text" wire:model="website" class="form-control">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="dnssec" class="form-check-input" role="switch" id="dnssec">
                                <label class="form-check-label" for="dnssec">DNSSEC</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="domain-form" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
