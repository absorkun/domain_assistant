<?php

namespace App\Mail;

use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DomainExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Domain $domain)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Domain {$this->domain->domain} Expired",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.domain-expired',
            with: [
                'domain' => $this->domain,
            ],
        );
    }
}
