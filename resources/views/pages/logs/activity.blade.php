<?php

use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function activities(): Collection
    {
        return ActivityLog::query()
            ->with('user:id,name,full_name')
            ->latest('occurred_at')
            ->limit(100)
            ->get();
    }
};
?>

<x-ui.admin-shell title="Aktivitas" subtitle="Logs">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 p-md-4">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th>Perubahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->activities as $activity)
                            <tr>
                                <td class="text-nowrap">{{ $activity->occurred_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $activity->user?->full_name ?? $activity->user?->name ?? '-' }}</div>
                                </td>
                                <td class="text-nowrap">{{ $activity->action }}</td>
                                <td>{{ $activity->description ?? '-' }}</td>
                                <td class="small">
                                    @php($changes = data_get($activity->properties, 'changes', []))
                                    @if ($changes)
                                        <div class="d-flex flex-column gap-1">
                                            @foreach ($changes as $field => $change)
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $field }}</div>
                                                    <div class="text-secondary">
                                                        <span class="text-danger">{{ $change['old'] ?? '-' }}</span>
                                                        <span class="mx-1">→</span>
                                                        <span class="text-success">{{ $change['new'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-4">Belum ada log aktivitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
