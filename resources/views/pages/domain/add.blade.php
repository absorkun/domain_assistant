<?php

use App\Models\Domain;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $domain = '';
    public ?string $name_srv = null;
    public ?string $status = null;
    public bool $dnssec = false;
    public ?string $tgl_reg = null;
    public ?string $tgl_exp = null;
    public ?string $dns_a = null;
    public ?string $website = null;

    public ?int $highlightedId = null;

    #[Computed]
    public function latestDomains(): Collection
    {
        return Domain::query()
            ->with('user:id,email')
            ->orderByDesc('id')
            ->limit(5)
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

<x-ui.admin-shell title="Add" subtitle="Domain">
    <div class="card border-0 shadow-sm w-100 mb-4">
        <div class="card-body p-3 p-md-5">
            <form wire:submit="save" class="d-grid gap-3">
                <div>
                    <label class="form-label">Domain</label>
                    <input type="text" wire:model="domain" class="form-control">
                    @error('domain')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Nameserver</label>
                    <input type="text" wire:model="name_srv" class="form-control">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <input type="text" wire:model="status" class="form-control">
                </div>
                <div class="form-check form-switch">
                    <input type="checkbox" wire:model="dnssec" class="form-check-input" role="switch" id="dnssec">
                    <label class="form-check-label" for="dnssec">DNSSEC</label>
                </div>
                <div>
                    <label class="form-label">Tanggal Registrasi</label>
                    <input type="date" wire:model="tgl_reg" class="form-control">
                </div>
                <div>
                    <label class="form-label">Tanggal Expired</label>
                    <input type="date" wire:model="tgl_exp" class="form-control">
                </div>
                <div>
                    <label class="form-label">DNS A</label>
                    <input type="text" wire:model="dns_a" class="form-control">
                </div>
                <div>
                    <label class="form-label">Website</label>
                    <input type="text" wire:model="website" class="form-control">
                </div>
                <div class="d-grid d-sm-flex">
                    <button type="submit" class="btn btn-primary w-100 w-sm-auto">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-3 p-md-5">
            <div class="text-secondary small mb-3">5 domain terbaru</div>
            <div class="d-grid gap-2">
                @foreach ($this->latestDomains as $record)
                    <div class="border rounded-3 p-3 {{ $highlightedId === $record->id ? 'border-primary' : '' }}">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-2 gap-sm-3">
                            <div>
                                <div class="fw-semibold">{{ $record->domain }}</div>
                                <div class="text-secondary small">{{ data_get($record, 'user.email', '-') }}</div>
                            </div>
                            @if ($highlightedId === $record->id)
                                <span class="badge text-bg-primary">Baru</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-ui.admin-shell>
