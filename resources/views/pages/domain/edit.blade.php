<?php

use App\Models\Domain;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    public string|int $id;

    #[Validate]
    public string $name = '';

    #[Validate]
    public string $status = '';

    #[Validate]
    public string $name_srv = '';

    #[Validate]
    public ?string $website = null;

    #[Validate]
    public bool $dnssec = false;

    public function mount(string|int $id): void
    {
        $this->id = $id;

        $domain = $this->record();

        if ($domain) {
            $this->name = $domain->domain;
            $this->status = $domain->status;
            $this->name_srv = $domain->name_srv;
            $this->website = $domain->website;
            $this->dnssec = $domain->dnssec;
        }
    }

    #[Computed]
    public function record(): ?Domain
    {
        return Domain::query()->find($this->id);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', Rule::unique('domains', 'domain')->ignore($this->id)],
            'status' => ['required', 'string', 'max:255'],
            'name_srv' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'dnssec' => ['boolean'],
        ];
    }

    public function save(): void
    {
        $record = $this->record();

        if (! $record) {
            return;
        }

        $data = $this->validate();
        $data['domain'] = $data['name'];
        unset($data['name']);
        $record->update($data);

        $this->dispatch('domain-saved', domain: $record->domain);
    }
};
?>

<x-ui.admin-shell title="Edit Domain" subtitle="Auth">
    @if (! $this->record())
        <div class="alert alert-warning border-0 shadow-sm mb-0">Domain tidak ditemukan.</div>
    @else
        <form wire:submit="save" class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center gap-2">
                <div class="d-flex flex-column gap-1">
                    <div class="text-secondary small">Edit domain</div>
                    <h1 class="h4 mb-0">{{ $this->record()->domain }}</h1>
                </div>
                <div class="d-flex gap-2">
                    <a href="/domain" wire:navigate class="btn btn-outline-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label class="form-label">Domain</label>
                        <input wire:model="name" class="form-control">
                        @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Status</label>
                        <input wire:model="status" class="form-control">
                        @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Nameserver</label>
                        <input wire:model="name_srv" class="form-control">
                        @error('name_srv')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-lg-6">
                        <label class="form-label">Website</label>
                        <input wire:model="website" class="form-control">
                        @error('website')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input wire:model="dnssec" class="form-check-input" type="checkbox" role="switch">
                            <label class="form-check-label">DNSSEC</label>
                            @error('dnssec')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @endif
</x-ui.admin-shell>
