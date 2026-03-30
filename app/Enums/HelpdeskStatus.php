<?php

namespace App\Enums;

enum HelpdeskStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingCustomer = 'waiting_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Masuk',
            self::InProgress => 'Diproses',
            self::Resolved => 'Selesai',
            self::Closed => 'Tutup',
            self::WaitingCustomer => 'Menunggu',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'text-bg-primary',
            self::InProgress => 'text-bg-warning',
            self::WaitingCustomer => 'text-bg-secondary',
            self::Resolved => 'text-bg-success',
            self::Closed => 'text-bg-dark',
        };
    }

    public static function quickActions(): array
    {
        return [
            self::InProgress,
            self::Resolved,
            self::Closed,
        ];
    }
}
