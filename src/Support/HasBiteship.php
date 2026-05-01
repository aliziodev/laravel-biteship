<?php

namespace Aliziodev\Biteship\Support;

use Aliziodev\Biteship\DTOs\Label\Label;
use Aliziodev\Biteship\DTOs\Order\OrderResponse;
use Aliziodev\Biteship\Enums\OrderStatus;
use Aliziodev\Biteship\Enums\TrackingStatus;
use Aliziodev\Biteship\Models\BiteshipOrder;
use Aliziodev\Biteship\Services\OrderService;
use Aliziodev\Biteship\Services\TrackingService;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Tambahkan trait ini ke model Order milik user.
 *
 * @example
 *   use Aliziodev\Biteship\Support\HasBiteship;
 *
 *   class Order extends Model {
 *       use HasBiteship;
 *   }
 */
trait HasBiteship
{
    // --- Relation ---

    public function biteshipOrder(): MorphOne
    {
        return $this->morphOne(BiteshipOrder::class, 'orderable');
    }

    // --- Core Methods ---

    /**
     * Simpan response dari Biteship ke tabel biteship_orders.
     * Panggil setelah OrderService::create() berhasil.
     */
    public function createBiteshipOrder(OrderResponse $response): BiteshipOrder
    {
        return $this->biteshipOrder()->updateOrCreate(
            ['biteship_order_id' => $response->id],
            [
                'biteship_status' => $response->status->value,
                'waybill_id' => $response->waybillId,
                'courier_company' => $response->courierCompany,
                'courier_type' => $response->courierType,
                'courier_tracking_id' => $response->courierTrackingId,
                'shipping_cost' => $response->price,
                'insurance_cost' => $response->insuranceFee,
                'cod_amount' => $response->codFee,
                'raw_response' => $response->raw,
                'confirmed_at' => now(),
            ]
        );
    }

    /**
     * Sync status terbaru dari Tracking API ke DB.
     * Idempoten — skip update jika status tidak berubah.
     */
    public function syncBiteshipStatus(): BiteshipOrder
    {
        $biteship = $this->biteshipOrder
            ?? throw new \RuntimeException('No biteship order found for this model.');

        $tracking = app(TrackingService::class)->trackByOrderId($biteship->biteship_order_id);
        $newStatus = $tracking->status->value;

        // Skip DB write jika status tidak berubah — aman untuk re-fire webhook
        if ($biteship->biteship_status === $newStatus) {
            return $biteship;
        }

        $attributes = ['biteship_status' => $newStatus];

        // Isi milestone timestamp saat pertama kali status tersebut tercapai
        if ($tracking->status === TrackingStatus::Picked && $biteship->picked_at === null) {
            $attributes['picked_at'] = now();
        }

        if ($tracking->status === TrackingStatus::Delivered && $biteship->delivered_at === null) {
            $attributes['delivered_at'] = now();
        }

        $biteship->update($attributes);

        return $biteship->refresh();
    }

    /**
     * Cancel order via Biteship API lalu update status di DB.
     */
    public function cancelBiteship(?string $reason = null): BiteshipOrder
    {
        $biteship = $this->biteshipOrder
            ?? throw new \RuntimeException('No biteship order found for this model.');

        if (! $biteship->canCancel()) {
            throw new \RuntimeException(
                "Cannot cancel order in status: {$biteship->biteship_status}"
            );
        }

        app(OrderService::class)->cancel($biteship->biteship_order_id, $reason);

        $biteship->update(['biteship_status' => OrderStatus::Cancelled->value]);

        return $biteship->refresh();
    }

    /**
     * Generate label pengiriman dari raw_response yang tersimpan.
     * Zero API call — semua data sudah ada di DB.
     */
    public function generateLabel(): Label
    {
        $biteship = $this->biteshipOrder
            ?? throw new \RuntimeException('No biteship order found for this model.');

        if (empty($biteship->raw_response)) {
            throw new \RuntimeException('raw_response is empty. Cannot generate label.');
        }

        return Label::fromOrderResponse($biteship->raw_response);
    }
}
