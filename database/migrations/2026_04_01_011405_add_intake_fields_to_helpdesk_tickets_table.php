<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('helpdesk_tickets', 'received_by_user_id')) {
                $table->foreignId('received_by_user_id')
                    ->nullable()
                    ->after('reporter_phone')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('helpdesk_tickets', 'received_by_name')) {
                $table->string('received_by_name')
                    ->nullable()
                    ->after('received_by_user_id');
            }

            if (! Schema::hasColumn('helpdesk_tickets', 'report_body')) {
                $table->longText('report_body')
                    ->nullable()
                    ->after('subject');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('helpdesk_tickets', 'report_body')) {
                $table->dropColumn('report_body');
            }

            if (Schema::hasColumn('helpdesk_tickets', 'received_by_name')) {
                $table->dropColumn('received_by_name');
            }

            if (Schema::hasColumn('helpdesk_tickets', 'received_by_user_id')) {
                $table->dropConstrainedForeignId('received_by_user_id');
            }
        });
    }
};
