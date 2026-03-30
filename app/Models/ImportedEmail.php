<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'mailbox',
    'message_uid',
    'from_email',
    'to_email',
    'subject',
    'body',
    'sent_at',
    'imported_at',
])]
class ImportedEmail extends Model
{
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'imported_at' => 'datetime',
        ];
    }
}
