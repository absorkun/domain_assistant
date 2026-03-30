<?php

namespace App\Enums;

enum DomainStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case AddPeriod = 'add period';
    case AutoRenewPeriod = 'auto renew period';
    case RenewPeriod = 'renew period';
    case ClientHold = 'client hold';
    case ClientTransferProhibited = 'client transfer prohibited';
    case Reserved = 'reserved';
    case ServerDeleteProhibited = 'server delete prohibited';
    case ServerHold = 'server hold';
    case ServerTransferProhibited = 'server transfer prohibited';
    case ServerUpdateProhibited = 'server update prohibited';
}
