<?php

use App\Enums\HelpdeskStatus;
use App\Services\HelpdeskTicketService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    protected HelpdeskTicketService $service;

    public string $day = '';

    public function boot(HelpdeskTicketService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->day = today()->toDateString();
    }

    #[Computed]
    public function tickets(): Collection
    {
        return $this->service->listForUser(auth()->id(), $this->day !== '' ? $this->day : null);
    }

    public function updateStatus(int $ticketId, string $status): void
    {
        $ticket = $this->service->listForUser(auth()->id(), $this->day !== '' ? $this->day : null)
            ->firstWhere('id', $ticketId);

        if (! $ticket) {
            abort(404);
        }

        $this->service->updateStatus($ticket, $status);
        unset($this->tickets);
    }

    public function clearDay(): void
    {
        $this->day = today()->toDateString();
        unset($this->tickets);
    }
};
?>

<x-ui.admin-shell title="Chat" subtitle="Helpdesk">
    <div class="d-flex flex-column gap-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold mb-1">Helpdesk</div>
                        <h2 class="h4 mb-0">Catat Laporan</h2>
                        <div class="text-secondary small mt-2">Laporan manual dari WA user ke staff.</div>
                    </div>
                    <a href="{{ route('helpdesk.chat.create') }}" wire:navigate class="btn btn-primary">Tambah</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                    <div>
                        <div class="text-secondary small text-uppercase fw-semibold mb-1">Tiket</div>
                        <h3 class="h5 mb-0">Daftar Laporan</h3>
                    </div>
                    <div class="d-flex align-items-end gap-2">
                        <div>
                            <label class="form-label small text-secondary mb-1">Hari</label>
                            <input type="date" wire:model.live="day" class="form-control form-control-sm">
                        </div>
                        <button type="button" wire:click="clearDay" class="btn btn-outline-secondary btn-sm">Hari ini</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Domain</th>
                                <th>Pelapor</th>
                                <th>Penerima</th>
                                <th>Tanggal/Waktu</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->tickets as $record)
                                <tr wire:key="ticket-{{ $record->id }}">
                                    <td class="fw-semibold text-dark text-break">{{ $record->domain ?: '-' }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <div class="fw-semibold text-dark">{{ $record->reporter_name ?: '-' }}</div>
                                            <div class="text-secondary small">{{ $record->reporter_email ?: '-' }}</div>
                                            <div class="text-secondary small">{{ $record->reporter_phone ?: '-' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $record->received_by_name ?: '-' }}</div>
                                    </td>
                                    <td class="text-secondary small text-nowrap">{{ $record->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                    <td>
                                        <select wire:change="updateStatus({{ $record->id }}, $event.target.value)" class="form-select form-select-sm w-auto">
                                            @foreach (HelpdeskStatus::cases() as $ticketStatus)
                                                <option value="{{ $ticketStatus->value }}" @selected($record->status?->value === $ticketStatus->value)>
                                                    {{ $ticketStatus->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('helpdesk.chat.detail', $record) }}" wire:navigate class="btn btn-outline-primary btn-sm">
                                            Buka
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="alert alert-light border mb-0">Belum ada tiket.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
