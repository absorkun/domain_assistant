<?php

namespace App\Enums;

enum DomainEmailStatus: string
{
    case Sent = 'sent';
    case Pending = 'pending';
    case Failed = 'failed';
}
