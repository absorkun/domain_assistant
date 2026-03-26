<?php

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Mail\DomainExpiredMail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    public int $limit = 5;

    public ?string $from = null;

    public ?string $to = null;

    public string $statusFilter = 'all';

    public ?int $sentDomainId = null;
    public ?string $message = null;

    public function mount(): void
    {
        $this->limit = max(1, min(100, $this->limit));
        $this->from ??= now()->subMonth()->toDateString();
        $this->to ??= now()->toDateString();
    }

    public function updatedLimit(): void
    {
        $this->limit = max(1, min(100, $this->limit));
    }

    #[Computed]
    public function domains(): Collection
    {
        $from = filled($this->from)
            ? Carbon::parse($this->from)->startOfDay()
            : Carbon::today()->subMonth()->startOfDay();

        $to = filled($this->to)
            ? Carbon::parse($this->to)->endOfDay()
            : Carbon::today()->endOfDay();

        return Domain::query()
            ->with('user:id,email')
            ->whereBetween('tgl_exp', [$from, $to])
            ->when($this->statusFilter === 'unsent', function ($query) {
                $query->whereNotIn('id', $this->sentToday);
            })
            ->orderBy('tgl_exp')
            ->limit($this->limit)
            ->get();
    }

    #[Computed]
    public function sentToday(): array
    {
        return DomainEmail::query()
            ->whereDate('sent_at', Carbon::today())
            ->where('status', 'sent')
            ->pluck('domain_id')
            ->all();
    }

    public function sendEmail(int $domainId): void
    {
        $domain = Domain::query()->with('user:id,email')->findOrFail($domainId);

        $alreadySentToday = DomainEmail::query()
            ->where('domain_id', $domain->id)
            ->whereDate('sent_at', Carbon::today())
            ->where('status', 'sent')
            ->exists();

        if ($alreadySentToday) {
            $this->message = 'Email untuk domain ini sudah dikirim hari ini.';

            return;
        }

        Mail::to($domain->user->email)->send(new DomainExpiredMail($domain));

        DomainEmail::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $domain->user_id,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->sentDomainId = $domain->id;
        $this->message = 'Email berhasil dikirim.';
        $this->dispatch('domain-email-sent', domain: $domain->domain);
    }

    public function canSend(int $domainId): bool
    {
        return ! in_array($domainId, $this->sentToday, true);
    }
};
?>

<x-ui.admin-shell title="Expired" subtitle="Domain">
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
                <div class="col-12 col-md-3">
                    <label class="form-label">Status kirim</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="all">Semua</option>
                        <option value="unsent">Belum Terkirim</option>
                    </select>
                </div>
            </div>

            <div class="text-secondary small mb-4">Menampilkan domain yang expired pada rentang tanggal yang dipilih.</div>

            @if ($message)
                <div class="alert alert-light border mb-4">{{ $message }}</div>
            @endif

            <div class="d-grid gap-2">
                @forelse ($this->domains as $domain)
                    <div class="border rounded-3 p-3 p-md-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                            <div class="min-w-0">
                                <div class="fw-semibold text-dark text-break">{{ $domain->domain }}</div>
                                <div class="text-secondary small">
                                    Expired {{ $domain->tgl_exp?->format('d/m/Y') ?? '-' }} · {{ data_get($domain, 'user.email', '-') }}
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="sendEmail({{ $domain->id }})"
                                @disabled(! $this->canSend($domain->id))
                                class="btn btn-outline-primary btn-sm flex-shrink-0"
                            >
                                {{ $this->canSend($domain->id) ? 'Kirim' : 'Terkirim' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-light border mb-0">Tidak ada data.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-ui.admin-shell>
