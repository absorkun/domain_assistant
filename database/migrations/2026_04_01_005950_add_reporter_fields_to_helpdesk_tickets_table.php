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
            if (! Schema::hasColumn('helpdesk_tickets', 'reporter_name')) {
                $table->string('reporter_name')
                    ->nullable()
                    ->after('domain');
            }

            if (! Schema::hasColumn('helpdesk_tickets', 'reporter_email')) {
                $table->string('reporter_email')
                    ->nullable()
                    ->after('reporter_name');
            }

            if (! Schema::hasColumn('helpdesk_tickets', 'reporter_phone')) {
                $table->string('reporter_phone')
                    ->nullable()
                    ->after('reporter_email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helpdesk_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('helpdesk_tickets', 'reporter_phone')) {
                $table->dropColumn('reporter_phone');
            }

            if (Schema::hasColumn('helpdesk_tickets', 'reporter_email')) {
                $table->dropColumn('reporter_email');
            }

            if (Schema::hasColumn('helpdesk_tickets', 'reporter_name')) {
                $table->dropColumn('reporter_name');
            }
        });
    }
};
