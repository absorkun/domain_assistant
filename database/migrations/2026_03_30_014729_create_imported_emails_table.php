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
        Schema::create('imported_emails', function (Blueprint $table) {
            $table->id();
            $table->string('mailbox');
            $table->unsignedBigInteger('message_uid');
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->text('subject')->nullable();
            $table->longText('body')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('imported_at');
            $table->timestamps();

            $table->unique(['mailbox', 'message_uid']);
            $table->index(['sent_at', 'imported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_emails');
    }
};
