<?php

namespace Aliziodev\Biteship\Enums;

enum TrackingStatus: string
{
    case Confirmed = 'confirmed';
    case Scheduled = 'scheduled';
    case Allocated = 'allocated';
    case PickingUp = 'picking_up';
    case Picked = 'picked';
    case DroppingOff = 'dropping_off';
    case Delivered = 'delivered';
    case OnHold = 'on_hold';
    case ReturnInTransit = 'return_in_transit';
    case Returned = 'returned';
    case Rejected = 'rejected';
    case Disposed = 'disposed';
    case CourierNotFound = 'courier_not_found';

    /**
     * Status terminal untuk tracking (tidak termasuk cancelled — tracking
     * tidak expose cancelled karena order sudah tidak ada di kurir).
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::Delivered,
            self::Returned,
            self::Rejected,
            self::Disposed,
            self::CourierNotFound,
        ]);
    }

    public function isSuccess(): bool
    {
        return $this === self::Delivered;
    }

    public function isProblem(): bool
    {
        return in_array($this, [self::OnHold, self::ReturnInTransit]);
    }

    public function label(): string
    {
        return match ($this) {
            self::Confirmed => 'Terkonfirmasi',
            self::Scheduled => 'Terjadwal',
            self::Allocated => 'Teralokasi',
            self::PickingUp => 'Dalam Penjemputan',
            self::Picked => 'Berhasil Dijemput',
            self::DroppingOff => 'Dalam Pengantaran',
            self::Delivered => 'Berhasil Dikirim',
            self::OnHold => 'Ditahan',
            self::ReturnInTransit => 'Dalam Pengembalian',
            self::Returned => 'Dikembalikan',
            self::Rejected => 'Paket Ditolak',
            self::Disposed => 'Dihancurkan',
            self::CourierNotFound => 'Kurir Tidak Ditemukan',
        };
    }

    /**
     * Cast dari OrderStatus jika dibutuhkan (misal saat sync dari DB ke tracking context).
     */
    public static function fromOrderStatus(OrderStatus $status): ?static
    {
        return self::tryFrom($status->value);
    }
}
