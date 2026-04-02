<?php

namespace App\Enums;

enum HelpdeskStatus: string
{
    case InProgress = 'in_progress';
    case Resolved = 'resolved';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'Proses',
            self::Resolved => 'Selesai',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::InProgress => 'text-bg-warning',
            self::Resolved => 'text-bg-success',
        };
    }

    public static function quickActions(): array
    {
        return [self::Resolved];
    }
}
