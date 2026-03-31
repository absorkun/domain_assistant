<?php

namespace App\Enums;

enum DomainEmailStatus: string
{
    case Sent = 'sent';
    case Error = 'error';
    case Retry1 = 'retry1';
    case Retry2 = 'retry2';
    case Retry3 = 'retry3';
}
