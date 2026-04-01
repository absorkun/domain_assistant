<?php

use App\Enums\HelpdeskStatus;
use App\Models\HelpdeskTicket;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public string $reporterName = '';

    public string $reporterEmail = '';

    public ?string $reporterPhone = null;

    public string $domain = '';

    public string $subject = '';

    public string $reportBody = '';

    public string $status = HelpdeskStatus::Open->value;

    public function mount(): void
    {
        $this->status = HelpdeskStatus::Open->value;
    }

    public function createTicket(): void
    {
        $user = auth()->user();

        $data = Validator::make([
            'reporterName' => $this->reporterName,
            'reporterEmail' => $this->reporterEmail,
            'reporterPhone' => $this->reporterPhone,
            'domain' => $this->domain,
            'subject' => $this->subject,
            'reportBody' => $this->reportBody,
            'status' => $this->status,
        ], [
            'reporterName' => ['required', 'string', 'max:120'],
            'reporterEmail' => ['required', 'email', 'max:255'],
            'reporterPhone' => ['nullable', 'string', 'max:30'],
            'domain' => ['nullable', 'string', 'max:120'],
            'subject' => ['required', 'string', 'max:120'],
            'reportBody' => ['required', 'string', 'min:3'],
            'status' => ['required', Rule::enum(HelpdeskStatus::class)],
        ])->validate();

        HelpdeskTicket::query()->create([
            'user_id' => auth()->id(),
            'domain' => $data['domain'] ?: null,
            'reporter_name' => $data['reporterName'],
            'reporter_email' => $data['reporterEmail'],
            'reporter_phone' => $data['reporterPhone'] ?: null,
            'received_by_user_id' => auth()->id(),
            'received_by_name' => $user?->full_name ?? $user?->name,
            'report_body' => $data['reportBody'],
            'subject' => $data['subject'],
            'status' => $data['status'],
            'last_message_at' => now(),
        ]);

        $this->reset(['reporterName', 'reporterEmail', 'reporterPhone', 'domain', 'subject', 'reportBody', 'status']);
        $this->status = HelpdeskStatus::Open->value;

        $this->redirectRoute('helpdesk.chat', navigate: true);
    }
};
?>

<x-ui.admin-shell title="Tambah Laporan" subtitle="Helpdesk">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                <div>
                    <div class="text-secondary small text-uppercase fw-semibold mb-1">Helpdesk</div>
                    <h2 class="h4 mb-0">Masukkan laporan masuk</h2>
                    <div class="text-secondary small mt-2">Laporan tersimpan atas nama staff penerima.</div>
                </div>
                <a href="{{ route('helpdesk.chat') }}" wire:navigate class="btn btn-outline-secondary">Kembali</a>
            </div>

            <form wire:submit="createTicket" class="d-flex flex-column gap-3">
                <div>
                    <label class="form-label">Diterima oleh</label>
                    <input type="text" value="{{ auth()->user()?->full_name ?? auth()->user()?->name ?? '-' }}" class="form-control" readonly>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label">Nama</label>
                        <input type="text" wire:model="reporterName" class="form-control">
                        @error('reporterName')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" wire:model="reporterEmail" class="form-control">
                        @error('reporterEmail')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label">Nomor Telp</label>
                        <input type="text" wire:model="reporterPhone" class="form-control">
                        @error('reporterPhone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div>
                    <label class="form-label">Domain</label>
                    <input type="text" wire:model="domain" class="form-control">
                    @error('domain')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Subjek</label>
                    <input type="text" wire:model="subject" class="form-control">
                    @error('subject')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Pesan</label>
                    <textarea wire:model="reportBody" rows="5" class="form-control"></textarea>
                    @error('reportBody')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Status Laporan</label>
                    <div class="d-flex flex-wrap gap-2">
                        <input type="radio" class="btn-check" name="helpdesk_status" id="status-open" value="{{ HelpdeskStatus::Open->value }}" wire:model="status">
                        <label class="btn btn-outline-primary btn-sm" for="status-open">{{ HelpdeskStatus::Open->label() }}</label>

                        <input type="radio" class="btn-check" name="helpdesk_status" id="status-progress" value="{{ HelpdeskStatus::InProgress->value }}" wire:model="status">
                        <label class="btn btn-outline-primary btn-sm" for="status-progress">{{ HelpdeskStatus::InProgress->label() }}</label>

                        <input type="radio" class="btn-check" name="helpdesk_status" id="status-resolved" value="{{ HelpdeskStatus::Resolved->value }}" wire:model="status">
                        <label class="btn btn-outline-primary btn-sm" for="status-resolved">{{ HelpdeskStatus::Resolved->label() }}</label>
                    </div>
                    @error('status')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('helpdesk.chat') }}" wire:navigate class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</x-ui.admin-shell>
