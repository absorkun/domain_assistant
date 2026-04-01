<?php

namespace App\Models;

use App\Enums\HelpdeskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpdeskTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'domain',
        'reporter_name',
        'reporter_email',
        'reporter_phone',
        'received_by_user_id',
        'received_by_name',
        'report_body',
        'subject',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => HelpdeskStatus::class,
            'last_message_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(HelpdeskMessage::class);
    }
}
