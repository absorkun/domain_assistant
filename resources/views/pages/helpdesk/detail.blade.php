<?php

use App\Enums\HelpdeskStatus;
use App\Models\HelpdeskTicket;
use App\Services\HelpdeskTicketService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public int $ticketId;

    public string $domain = '';

    public string $subject = '';

    public string $status = HelpdeskStatus::InProgress->value;

    protected HelpdeskTicketService $service;

    public function mount(int $ticket): void
    {
        $record = HelpdeskTicket::query()
            ->whereKey($ticket)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->ticketId = $record->id;
        $this->domain = (string) $record->domain;
        $this->subject = $record->subject;
        $this->status = $record->status instanceof HelpdeskStatus
            ? $record->status->value
            : (string) $record->status;
    }

    public function boot(HelpdeskTicketService $service): void
    {
        $this->service = $service;
    }

    public function ticket(): HelpdeskTicket
    {
        return HelpdeskTicket::query()
            ->whereKey($this->ticketId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    public function statuses(): array
    {
        return HelpdeskStatus::cases();
    }

    public function quickActions(): array
    {
        return HelpdeskStatus::quickActions();
    }

    public function setStatus(string $status): void
    {
        $ticket = $this->service->updateStatus($this->ticket(), $status);
        $this->status = $ticket->status instanceof HelpdeskStatus
            ? $ticket->status->value
            : (string) $ticket->status;
    }

    public function updateTicket(): void
    {
        $ticket = $this->ticket();

        $data = Validator::make([
            'domain' => $this->domain,
            'subject' => $this->subject,
            'status' => $this->status,
        ], [
            'domain' => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::enum(HelpdeskStatus::class)],
        ])->validate();

        $ticket->update([
            'domain' => $data['domain'] ?: null,
            'subject' => $data['subject'],
            'status' => $data['status'],
        ]);
    }
};
?>

<x-ui.admin-shell title="Detail Tiket" subtitle="Helpdesk">
    <div class="d-flex flex-column gap-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Helpdesk</div>
                            <h2 class="h4 mb-0">{{ $this->subject }}</h2>
                        </div>
                        <div class="d-flex flex-column gap-1">
                            <div class="text-dark fw-semibold">{{ $this->domain ?: '-' }}</div>
                            <div class="text-secondary small">
                                Dibuat {{ $this->ticket()->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @php($status = HelpdeskStatus::tryFrom($this->status))
                        <span class="badge {{ $status?->badgeClass() ?? 'text-bg-secondary' }}">{{ $status?->label() ?? $this->status }}</span>
                        <a href="{{ route('helpdesk.chat') }}" wire:navigate class="btn btn-outline-secondary">
                            Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-column gap-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                        <div>
                            <div class="text-secondary small text-uppercase fw-semibold mb-1">Penanganan</div>
                            <h3 class="h5 mb-0">Data Intake</h3>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nama Pelapor</label>
                            <input value="{{ $this->ticket()->reporter_name ?: '-' }}" type="text" class="form-control" readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Email Pelapor</label>
                            <input value="{{ $this->ticket()->reporter_email ?: '-' }}" type="text" class="form-control" readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nomor Telp</label>
                            <input value="{{ $this->ticket()->reporter_phone ?: '-' }}" type="text" class="form-control" readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Staff Penerima</label>
                            <input value="{{ $this->ticket()->received_by_name ?: '-' }}" type="text" class="form-control" readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Status</label>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($this->statuses() as $ticketStatus)
                                    <input type="radio" class="btn-check" name="helpdesk_detail_status" id="detail-status-{{ $ticketStatus->value }}" value="{{ $ticketStatus->value }}" wire:model="status">
                                    <label class="btn btn-outline-primary btn-sm" for="detail-status-{{ $ticketStatus->value }}">
                                        {{ $ticketStatus->label() }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" wire:click="updateTicket" class="btn btn-outline-primary">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold mb-1">Pesan</div>
                        <h3 class="h5 mb-0">Isi Laporan</h3>
                    </div>
                </div>

                <div class="border rounded-3 p-3 p-md-4 bg-white">
                    {{ $this->ticket()->report_body ?: '-' }}
                </div>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
