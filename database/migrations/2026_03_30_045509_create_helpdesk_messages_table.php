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
        if (! Schema::hasTable('helpdesk_messages')) {
            Schema::create('helpdesk_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('helpdesk_ticket_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
                $table->longText('body');
                $table->boolean('is_staff')->default(false);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('helpdesk_messages');
    }
};
