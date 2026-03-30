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
    public int $ticketId;

    public string $domain = '';

    public string $subject = '';

    public string $status = HelpdeskStatus::Open->value;

    public string $body = '';

    #[Computed]
    public function ticket(): ?HelpdeskTicket
    {
        return HelpdeskTicket::query()
            ->whereKey($this->ticketId)
            ->first();
    }

    #[Computed]
    public function messages(): Collection
    {
        if (! $this->ticketId) {
            return collect();
        }

        return HelpdeskMessage::query()
            ->with(['sender:id,name,full_name', 'receiver:id,name,full_name'])
            ->where('helpdesk_ticket_id', $this->ticketId)
            ->oldest('sent_at')
            ->oldest('id')
            ->get();
    }

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
        $this->status = $status;
        $this->updateTicket();
    }

    public function updateTicket(): void
    {
        $ticket = HelpdeskTicket::query()
            ->whereKey($this->ticketId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

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

    public function sendMessage(): void
    {
        $data = Validator::make([
            'body' => $this->body,
        ], [
            'body' => ['required', 'string', 'min:3'],
        ])->validate();

        $ticket = HelpdeskTicket::query()
            ->whereKey($this->ticketId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        HelpdeskMessage::query()->create([
            'helpdesk_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'sender_id' => auth()->id(),
            'receiver_id' => null,
            'body' => $data['body'],
            'is_staff' => false,
            'sent_at' => now(),
        ]);

        $ticket->forceFill([
            'last_message_at' => now(),
        ])->save();

        $this->body = '';
    }
};
?>

<x-ui.admin-shell title="Detail Tiket" subtitle="Helpdesk">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column gap-4">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold mb-1">{{ $this->subject }}</div>
                        <div class="text-secondary small">{{ $this->domain ?: '-' }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @php($status = HelpdeskStatus::tryFrom($this->status))
                        <span class="badge {{ $status?->badgeClass() ?? 'text-bg-secondary' }}">{{ $status?->label() ?? $this->status }}</span>
                        <a href="{{ route('helpdesk.chat') }}" wire:navigate class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </div>

                <form wire:submit="updateTicket" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="form-select">
                            @foreach ($this->statuses() as $ticketStatus)
                                <option value="{{ $ticketStatus->value }}">{{ $ticketStatus->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Domain</label>
                        <input wire:model="domain" type="text" class="form-control">
                    </div>
                    <div>
                        <label class="form-label">Judul</label>
                        <input wire:model="subject" type="text" class="form-control">
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-outline-primary">Simpan Perubahan</button>
                        @foreach ($this->quickActions() as $ticketStatus)
                            <button type="button" wire:click="setStatus('{{ $ticketStatus->value }}')" class="btn {{ $ticketStatus->badgeClass() }}">
                                {{ $ticketStatus->label() }}
                            </button>
                        @endforeach
                    </div>
                </form>

                <div>
                    <div class="fw-semibold mb-3">Percakapan</div>
                    <div class="border rounded-3 p-3 bg-white d-flex flex-column gap-3" style="min-height: 320px;">
                        @forelse ($this->messages as $message)
                            <div class="w-100">
                                <div class="small text-secondary mb-1">
                                    Pengirim: {{ $message->sender?->full_name ?? $message->sender?->name ?? '-' }} · Penerima: {{ $message->receiver?->full_name ?? $message->receiver?->name ?? '-' }} · Dibuat {{ $message->created_at?->format('d/m/Y H:i') }}
                                </div>
                                <div class="p-3 rounded-3 {{ $message->is_staff ? 'bg-light' : 'bg-primary-subtle' }}">
                                    {{ $message->body }}
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary small">Belum ada pesan.</div>
                        @endforelse
                    </div>
                </div>

                <form wire:submit="sendMessage" class="d-flex flex-column gap-3">
                    <div>
                        <label class="form-label">Pesan baru</label>
                        <textarea wire:model="body" rows="4" class="form-control"></textarea>
                        @error('body') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary align-self-start">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
