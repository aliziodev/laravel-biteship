<?php

namespace Aliziodev\Biteship\Enums;

enum OrderStatus: string
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
    case Cancelled = 'cancelled';

    /**
     * Status terminal — tidak ada transisi lagi setelah ini.
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::Delivered,
            self::Returned,
            self::Rejected,
            self::Disposed,
            self::CourierNotFound,
            self::Cancelled,
        ]);
    }

    /**
     * Satu-satunya status yang berarti pengiriman berhasil.
     */
    public function isSuccess(): bool
    {
        return $this === self::Delivered;
    }

    /**
     * Bermasalah — butuh perhatian tapi belum terminal.
     */
    public function isProblem(): bool
    {
        return in_array($this, [self::OnHold, self::ReturnInTransit]);
    }

    /**
     * Sedang dalam proses pengiriman (sudah dijemput, belum sampai).
     */
    public function isInTransit(): bool
    {
        return in_array($this, [
            self::Allocated,
            self::PickingUp,
            self::Picked,
            self::DroppingOff,
        ]);
    }

    /**
     * Cancel via API masih bisa dilakukan pada status ini.
     */
    public function canCancel(): bool
    {
        return in_array($this, [
            self::Confirmed,
            self::Scheduled,
            self::Allocated,
        ]);
    }

    /**
     * Label nama status dalam Bahasa Indonesia.
     */
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
            self::Cancelled => 'Dibatalkan',
        };
    }
}
