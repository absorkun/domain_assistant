<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $map = [
            'open' => 'in_progress',
            'waiting_customer' => 'in_progress',
            'closed' => 'resolved',
        ];

        foreach ($map as $from => $to) {
            DB::table('helpdesk_tickets')
                ->where('status', $from)
                ->update([
                    'status' => $to,
                ]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};
