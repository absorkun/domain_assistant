<?php

use Livewire\Component;

new class extends Component {
};
?>

<x-ui.admin-shell title="Backup" subtitle="Tools">
    <div class="card border-0 shadow-sm w-100">
        <div class="card-body p-3 p-md-5">
            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="fw-semibold text-dark">Backup database</div>
                    <div class="text-secondary small">
                        Unduh arsip ZIP berisi dump SQL yang siap dipakai untuk restore setelah migrate:fresh.
                    </div>
                </div>

                <div class="d-grid d-sm-flex">
                    <a href="{{ route('tools.backup.download') }}" class="btn btn-primary w-100 w-sm-auto">
                        Download ZIP
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-ui.admin-shell>
