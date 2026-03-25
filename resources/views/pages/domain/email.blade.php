<?php

use App\Models\DomainEmail;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function emails()
    {
        return DomainEmail::query()
            ->with(['user:id,email', 'domainRecord:id,domain'])
            ->whereDate('sent_at', Carbon::today())
            ->where('status', 'sent')
            ->orderByDesc('sent_at')
            ->limit(10)
            ->get();
    }
};
?>

<x-ui.admin-shell title="Email" subtitle="Domain">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                <div class="text-secondary small">10 email yang diproses dan terkirim hari ini.</div>
                <a href="/api/domain/email/export" target="_blank" class="btn btn-outline-primary btn-sm">Unduh JSON</a>
            </div>

            <div class="d-grid gap-2">
                @forelse ($this->emails as $email)
                    <div class="border rounded-3 p-3">
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
