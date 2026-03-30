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
        Schema::table('domain_emails', function (Blueprint $table) {
            $table->boolean('retry1')->default(false)->after('sent_at');
            $table->boolean('retry2')->default(false)->after('retry1');
            $table->boolean('retry3')->default(false)->after('retry2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domain_emails', function (Blueprint $table) {
            $table->dropColumn(['retry1', 'retry2', 'retry3']);
        });
    }
};
