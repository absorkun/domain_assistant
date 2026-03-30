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
    'retry1',
    'retry2',
    'retry3',
])]
class DomainEmail extends Model
{
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'retry1' => 'boolean',
            'retry2' => 'boolean',
            'retry3' => 'boolean',
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
