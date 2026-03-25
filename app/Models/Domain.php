<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('domains', timestamps: false)]
#[Fillable([
    'id',
    'domain',
    'user_id',
    'name_srv',
    'status',
    'dnssec',
    'tgl_reg',
    'tgl_exp',
    'tgl_upd',
    'dns_a',
    'website',
])]
class Domain extends Model
{
    public function casts(): array
    {
        return [
            'tgl_reg' => 'date',
            'tgl_exp' => 'date',
            'tgl_upd' => 'date',
            'dnssec' => 'boolean',
        ];
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
