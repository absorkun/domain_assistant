<?php

namespace App\Models;

use App\Models\Domain;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'domain_id',
    'user_id',
    'status',
    'sent_at',
])]
class DomainEmail extends Model
{
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function domainRecord(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
