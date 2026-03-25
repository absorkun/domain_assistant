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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('name_srv')->nullable();
            $table->string('status', 40)->nullable()->index();
            $table->tinyInteger('dnssec')->nullable();
            $table->date('tgl_reg')->nullable();
            $table->date('tgl_exp')->nullable();
            $table->date('tgl_upd')->nullable();
            $table->string('dns_a', 40)->nullable();
            $table->string('website', 40)->nullable();
            $table->timestamps();

            $table->unique(['domain', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
