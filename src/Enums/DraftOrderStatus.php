<?php

namespace Aliziodev\Biteship\Enums;

enum DraftOrderStatus: string
{
    case PLACED = 'placed';
    case READY = 'ready';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::PLACED => 'Draft dibuat',
            self::READY => 'Siap dikonfirmasi',
            self::CONFIRMED => 'Sudah dikonfirmasi',
        };
    }

    public function canBeDeleted(): bool
    {
        return $this === self::PLACED || $this === self::READY;
    }

    public function canBeConfirmed(): bool
    {
        return $this === self::READY;
    }
}
