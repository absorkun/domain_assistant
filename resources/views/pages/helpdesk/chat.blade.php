<?php

use App\Models\HelpdeskMessage;
use App\Models\HelpdeskTicket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $subject = '';

    public string $body = '';

    public ?int $ticketId = null;

    #[Computed]
    public function tickets(): Collection
    {
        return HelpdeskTicket::query()
            ->with(['messages' => fn ($query) => $query->latest('sent_at')->latest('id')->limit(5)])
            ->where('user_id', auth()->id())
            ->latest('last_message_at')
            ->latest('id')
            ->get();
    }

    #[Computed]
    public function messages(): Collection
    {
        if (! $this->ticketId) {
            return collect();
        }

        return HelpdeskMessage::query()
            ->with('user:id,name,full_name')
            ->where('helpdesk_ticket_id', $this->ticketId)
            ->oldest('sent_at')
            ->oldest('id')
            ->get();
    }

    public function openTicket(int $ticketId): void
    {
        $this->ticketId = $ticketId;
    }

    public function send(): void
    {
        $rules = $this->ticketId
            ? ['body' => ['required', 'string', 'min:3']]
            : [
                'subject' => ['required', 'string', 'max:120'],
                'body' => ['required', 'string', 'min:3'],
            ];

        $data = Validator::make([
            'subject' => $this->subject,
            'body' => $this->body,
        ], $rules)->validate();

        $ticket = $this->ticketId
            ? HelpdeskTicket::query()
                ->whereKey($this->ticketId)
                ->where('user_id', auth()->id())
                ->firstOrFail()
            : HelpdeskTicket::query()->create([
                'user_id' => auth()->id(),
                'subject' => $data['subject'],
                'status' => 'open',
                'last_message_at' => now(),
            ]);

        if (! $this->ticketId) {
            $this->ticketId = $ticket->id;
        }

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

<x-ui.admin-shell title="Chat" subtitle="Helpdesk">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column gap-4">
                <div>
                    <div class="fw-semibold mb-1">Chat baru</div>
                    <div class="text-secondary small">Buat percakapan bantuan dan lanjutkan di satu tempat.</div>
                    <form wire:submit="send" class="mt-3 d-flex flex-column gap-3">
                        @if (! $this->ticketId)
                            <div>
                                <label class="form-label">Judul</label>
                                <input wire:model="subject" type="text" class="form-control">
                                @error('subject') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        @endif
                        <div>
                            <label class="form-label">Pesan</label>
                            <textarea wire:model="body" rows="5" class="form-control"></textarea>
                            @error('body') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary align-self-start">Kirim</button>
                    </form>
                </div>

                <div>
                    <div class="fw-semibold mb-3">Daftar Chat</div>
                    <div class="d-flex flex-column gap-2">
                        @forelse ($this->tickets as $ticket)
                            <button type="button" wire:click="openTicket({{ $ticket->id }})" class="btn text-start border {{ $ticketId === $ticket->id ? 'border-primary bg-primary-subtle' : 'bg-white' }}">
                                <div class="fw-semibold text-truncate">{{ $ticket->subject }}</div>
                                <div class="text-secondary small">{{ $ticket->status }} · Dibuat {{ $ticket->created_at?->format('d/m/Y') }} · {{ optional($ticket->last_message_at)->format('d/m/Y H:i') }}</div>
                            </button>
                        @empty
                            <div class="text-secondary small">Belum ada chat.</div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <div class="fw-semibold mb-3">Percakapan</div>
                    <div class="border rounded-3 p-3 bg-white d-flex flex-column gap-3" style="min-height: 320px;">
                        @forelse ($this->messages as $message)
                            <div class="{{ $message->is_staff ? 'ms-0 me-auto' : 'ms-auto me-0' }} w-100">
                                <div class="small text-secondary mb-1">
                                    Pengirim: {{ $message->sender?->full_name ?? $message->sender?->name ?? '-' }} · Penerima: {{ $message->receiver?->full_name ?? $message->receiver?->name ?? '-' }} · Dibuat {{ $message->created_at?->format('d/m/Y H:i') }}
                                </div>
                                <div class="p-3 rounded-3 {{ $message->is_staff ? 'bg-light' : 'bg-primary-subtle' }}">
                                    {{ $message->body }}
                                </div>
                            </div>
                        @empty
                            <div class="text-secondary small">Pilih chat atau buat chat baru.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
