<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpdeskMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'helpdesk_ticket_id',
        'user_id',
        'sender_id',
        'receiver_id',
        'body',
        'is_staff',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_staff' => 'boolean',
            'sent_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(HelpdeskTicket::class, 'helpdesk_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
