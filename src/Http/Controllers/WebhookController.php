<?php

namespace Aliziodev\Biteship\Http\Controllers;

use Aliziodev\Biteship\DTOs\Webhook\OrderPricePayload;
use Aliziodev\Biteship\DTOs\Webhook\OrderStatusPayload;
use Aliziodev\Biteship\DTOs\Webhook\OrderWaybillPayload;
use Aliziodev\Biteship\Events\OrderPriceUpdated;
use Aliziodev\Biteship\Events\OrderStatusUpdated;
use Aliziodev\Biteship\Events\OrderWaybillUpdated;
use Aliziodev\Biteship\Exceptions\InvalidWebhookEventException;
use Aliziodev\Biteship\Exceptions\WebhookSignatureException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        try {
            $this->verifySignature($request);
        } catch (WebhookSignatureException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        $event = $request->input('event');

        try {
            $payload = match ($event) {
                'order.status' => OrderStatusPayload::fromArray($request->all()),
                'order.price' => OrderPricePayload::fromArray($request->all()),
                'order.waybill_id' => OrderWaybillPayload::fromArray($request->all()),
                default => throw new InvalidWebhookEventException($event ?? ''),
            };
        } catch (InvalidWebhookEventException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        event(match (true) {
            $payload instanceof OrderStatusPayload => new OrderStatusUpdated($payload),
            $payload instanceof OrderPricePayload => new OrderPriceUpdated($payload),
            $payload instanceof OrderWaybillPayload => new OrderWaybillUpdated($payload),
        });

        return response()->json(['received' => true]);
    }

    private function verifySignature(Request $request): void
    {
        $key = config('biteship.webhook.signature_key');
        $secret = config('biteship.webhook.signature_secret');

        // Tidak dikonfigurasi — skip verification (backward compatible)
        if (! $key || ! $secret) {
            return;
        }

        $incoming = $request->header($key);

        if (! $incoming || ! hash_equals($secret, $incoming)) {
            throw new WebhookSignatureException;
        }
    }
}
