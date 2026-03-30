<?php

namespace App\Observers;

use App\Models\Domain;
use App\Support\ActivityLogger;

class DomainObserver
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function updated(Domain $domain): void
    {
        $this->activityLogger->log(
            action: 'domain.updated',
            subject: $domain,
            description: 'Domain diperbarui.'
        );
    }

    public function created(Domain $domain): void
    {
        $this->activityLogger->log(
            action: 'domain.created',
            subject: $domain,
            description: 'Domain ditambahkan.'
        );
    }

    public function deleted(Domain $domain): void
    {
        $this->activityLogger->log(
            action: 'domain.deleted',
            subject: $domain,
            description: 'Domain dihapus.'
        );
    }
}
