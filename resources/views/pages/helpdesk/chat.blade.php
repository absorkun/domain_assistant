<?php

use App\Enums\HelpdeskStatus;
use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTicket;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $domain = '';

    public string $subject = '';

    public string $body = '';

    public string $status = HelpdeskStatus::Open->value;

    #[Computed]
    public function tickets(): Collection
    {
        return HelpdeskTicket::query()
            ->where('user_id', auth()->id())
            ->latest('last_message_at')
            ->latest('id')
            ->get();
    }

    public function statuses(): array
    {
        return HelpdeskStatus::cases();
    }

    public function createTicket(): void
    {
        $data = Validator::make([
            'domain' => $this->domain,
            'subject' => $this->subject,
            'status' => $this->status,
            'body' => $this->body,
        ], [
            'domain' => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::enum(HelpdeskStatus::class)],
            'body' => ['required', 'string', 'min:3'],
        ])->validate();

        $ticket = HelpdeskTicket::query()->create([
            'user_id' => auth()->id(),
            'domain' => $data['domain'] ?: null,
            'subject' => $data['subject'],
            'status' => $data['status'],
            'last_message_at' => now(),
        ]);

        HelpdeskMessage::query()->create([
            'helpdesk_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'sender_id' => auth()->id(),
            'receiver_id' => null,
            'body' => $data['body'],
            'is_staff' => false,
            'sent_at' => now(),
        ]);

        $this->reset(['domain', 'subject', 'body', 'status']);
        $this->status = HelpdeskStatus::Open->value;

        $this->dispatch('ticket-created');
    }
};
?>

<x-ui.admin-shell title="Chat" subtitle="Helpdesk">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column gap-4">
                <div>
                    <div class="fw-semibold mb-1">Catat Laporan</div>
                    <div class="text-secondary small mb-3">Input laporan manual dari WA lalu simpan ke tiket.</div>
                    <form wire:submit="createTicket" class="d-flex flex-column gap-3">
                        <div>
                            <label class="form-label">Domain</label>
                            <input wire:model="domain" type="text" class="form-control">
                            @error('domain') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Judul</label>
                            <input wire:model="subject" type="text" class="form-control">
                            @error('subject') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select">
                                @foreach ($this->statuses() as $ticketStatus)
                                    <option value="{{ $ticketStatus->value }}">{{ $ticketStatus->label() }}</option>
                                @endforeach
                            </select>
                            @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Pesan</label>
                            <textarea wire:model="body" rows="4" class="form-control"></textarea>
                            @error('body') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary align-self-start">Simpan</button>
                    </form>
                </div>

                <div>
                    <div class="fw-semibold mb-3">Tiket Masuk</div>
                    <div class="d-flex flex-column gap-2">
                        @forelse ($this->tickets as $ticket)
                            <a href="{{ route('helpdesk.chat.detail', $ticket->id) }}" wire:navigate class="text-decoration-none">
                                <div class="border rounded-3 p-3 bg-white">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div class="fw-semibold text-truncate text-dark">{{ $ticket->subject }}</div>
                                        @php($ticketStatus = $ticket->status instanceof HelpdeskStatus ? $ticket->status : HelpdeskStatus::tryFrom((string) $ticket->status))
                                        <span class="badge {{ $ticketStatus?->badgeClass() ?? 'text-bg-light' }}">{{ $ticketStatus?->label() ?? $ticket->status }}</span>
                                    </div>
                                    <div class="text-secondary small mt-1">
                                        {{ $ticket->domain ?: '-' }} · Dibuat {{ $ticket->created_at?->format('d/m/Y') }}
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="text-secondary small">Belum ada tiket.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
