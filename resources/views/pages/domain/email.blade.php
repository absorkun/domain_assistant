<?php

use App\Models\DomainEmail;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $limit = 10;
    public ?string $from = null;
    public ?string $to = null;

    public function mount(): void
    {
        $this->limit = max(1, min(100, $this->limit));
        $this->from ??= now()->toDateString();
        $this->to ??= now()->toDateString();
    }

    public function updatedLimit(): void
    {
        $this->limit = max(1, min(100, $this->limit));
    }

    #[Computed]
    public function emails()
    {
        $from = filled($this->from)
            ? Carbon::parse($this->from)->startOfDay()
            : Carbon::today()->startOfDay();

        $to = filled($this->to)
            ? Carbon::parse($this->to)->endOfDay()
            : Carbon::today()->endOfDay();

        return DomainEmail::query()
            ->with(['user:id,email', 'domainRecord:id,domain'])
            ->whereBetween('sent_at', [$from, $to])
            ->where('status', 'sent')
            ->orderByDesc('sent_at')
            ->limit($this->limit)
            ->get();
    }
};
?>

<x-ui.admin-shell title="Email" subtitle="Domain">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="row g-3 align-items-end mb-4">
                <div class="col-12 col-md-3">
                    <label class="form-label">Jumlah data</label>
                    <input type="number" min="1" max="100" wire:model.live="limit" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Dari tanggal</label>
                    <input type="date" wire:model.live="from" class="form-control">
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label">Sampai tanggal</label>
                    <input type="date" wire:model.live="to" class="form-control">
                </div>
                <div class="col-12 col-md-3"></div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-end align-items-start align-items-sm-center gap-3 mb-4">
                <a href="{{ url('/api/domain/email/export') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat API</a>
            </div>

            <div class="d-grid gap-2">
                @forelse ($this->emails as $email)
                    <div class="border rounded-3 p-3 p-md-4">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-semibold text-dark text-truncate">{{ data_get($email, 'domainRecord.domain', '-') }}</div>
                                <div class="text-secondary small text-truncate">{{ data_get($email, 'user.email', '-') }}</div>
                                <div class="text-secondary small">
                                    Terkirim {{ $email->sent_at?->format('d/m/Y H:i') ?? '-' }}
                                </div>
                            </div>
                            <span class="badge text-bg-success">Sent</span>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border mb-0">Tidak ada email terkirim hari ini.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-ui.admin-shell>
