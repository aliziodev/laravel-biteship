# Laravel Biteship

[![Tests](https://github.com/aliziodev/laravel-biteship/workflows/Tests/badge.svg)](https://github.com/aliziodev/laravel-biteship/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/aliziodev/laravel-biteship.svg)](https://packagist.org/packages/aliziodev/laravel-biteship)
[![Total Downloads](https://img.shields.io/packagist/dt/aliziodev/laravel-biteship.svg)](https://packagist.org/packages/aliziodev/laravel-biteship)
[![PHP Version](https://img.shields.io/packagist/php-v/aliziodev/laravel-biteship.svg)](https://packagist.org/packages/aliziodev/laravel-biteship)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.0%2B-orange.svg)](https://laravel.com/)
[![Ask DeepWiki](https://deepwiki.com/badge.svg)](https://deepwiki.com/aliziodev/laravel-biteship)

Laravel package untuk integrasi [Biteship API](https://biteship.com/id/docs/intro). Mendukung Rates, Orders, Tracking, Couriers, Location Search, Label Print, Webhook Events, dan optional DB layer via `HasBiteship` trait.

---

## Requirements

- PHP 8.3+
- Laravel 12 atau 13

---

## Installation

```bash
composer require aliziodev/laravel-biteship
```

```bash
php artisan biteship:install
```

Command ini akan mempublish config dan menanyakan apakah ingin menggunakan optional DB layer.

### Environment

```env
BITESHIP_API_KEY=biteship_live.xxxxxxxxxxxx

# Sandbox — gunakan key dengan prefix biteship_test.*
# BITESHIP_API_KEY=biteship_test.xxxxxxxxxxxx

# Optional: default origin (dipakai oleh ->defaultOrigin())
BITESHIP_ORIGIN_AREA_ID=IDNP6...
BITESHIP_ORIGIN_CONTACT_NAME=Nama Pengirim
BITESHIP_ORIGIN_CONTACT_PHONE=0812xxxxxxxx
BITESHIP_ORIGIN_ADDRESS=Jl. Contoh No. 1

# Optional: default courier (dipakai oleh ->defaultCourier())
BITESHIP_COURIER_COMPANY=jne           # kurir untuk order
BITESHIP_COURIER_TYPE=reg              # tipe layanan untuk order
BITESHIP_COURIER_INSURANCE=true        # opsional
BITESHIP_COURIER_FILTER=jne,sicepat    # kurir untuk rate check (CSV), override COMPANY

# Optional: default shipper branding (nama di label cetak)
BITESHIP_SHIPPER_CONTACT_NAME=Nama Toko
BITESHIP_SHIPPER_CONTACT_PHONE=021xxxxxxx
BITESHIP_SHIPPER_ORGANIZATION=PT Nama Perusahaan

# Optional: webhook signature verification
BITESHIP_WEBHOOK_SIGNATURE_KEY=X-My-Header
BITESHIP_WEBHOOK_SIGNATURE_SECRET=my-secret-value
```

---

## Usage

Semua fitur diakses via Facade `Biteship::`.

```php
use Aliziodev\Biteship\Facades\Biteship;
```

---

## Location Search

Autocomplete area pengiriman berdasarkan teks input. Gunakan `area_id` dari hasil ini untuk Rates dan Orders.

```php
$areas = Biteship::locations()->search('Menteng');
// $areas → Collection of array

foreach ($areas as $area) {
    // $area['id'], $area['name'], $area['postal_code']
}
```

---

## Rates (Ongkir)

Rates API menerima multi-kurir — hasilnya adalah daftar harga dari semua kurir yang diminta, untuk ditampilkan sebagai pilihan ke user.

```php
use Aliziodev\Biteship\DTOs\Rate\RateRequest;

$request = (new RateRequest)
    ->defaultOrigin()                          // dari config BITESHIP_ORIGIN_*
    ->destinationAreaId('IDNP6...')
    ->destinationContact('Budi', '08123456789')
    ->destinationAddress('Jl. Merdeka No. 10')
    ->addItem([
        'name'     => 'Sepatu',
        'value'    => 350000,
        'weight'   => 500,    // gram
        'quantity' => 1,
        'length'   => 30, 'width' => 20, 'height' => 15,
    ]);

$response = Biteship::rates()->check($request);

// $response->pricing → Collection<CourierRate>
foreach ($response->pricing as $rate) {
    // $rate->courier_name, $rate->courier_code, $rate->price, $rate->etd
}
```

**Filter kurir** — default cek semua kurir aktif di akun Biteship (`'biteship'`):

```php
// Eksplisit per-request
$request->couriers(['jne', 'sicepat', 'jnt']);

// Atau dari config BITESHIP_COURIER_FILTER=jne,sicepat,jnt
$request->defaultCourier();
```

**Cache & fresh:**

```php
// Bypass cache — selalu hit API
$response = Biteship::rates()->fresh()->check($request);

// Invalidate cache untuk payload tertentu
Biteship::rates()->forget($request);
```

> **Cache**: hasil ongkir di-cache otomatis (default 15 menit). Konfigurasikan via `config/biteship.php`.

---

## Orders

### Create Order

Order hanya bisa dibuat dengan **1 kurir spesifik** (`company` + `type`). Kurir biasanya didapat dari pilihan user di checkout berdasarkan hasil Rates.

```php
use Aliziodev\Biteship\DTOs\Order\OrderRequest;

$request = (new OrderRequest)
    ->defaultOrigin()
    ->destinationAreaId('IDNP6...')
    ->destinationContact('Budi', '08123456789')
    ->destinationAddress('Jl. Merdeka No. 10')
    ->courier('jne', 'reg')                    // company + type dari pilihan user
    ->addItem([
        'name'     => 'Sepatu',
        'value'    => 350000,
        'weight'   => 500,
        'quantity' => 1,
    ])
    ->referenceId('INV-2025-001')              // optional: ID order dari sistem kamu
    ->notes('Jangan dilipat');                 // optional

// Dengan asuransi
$request->courier('jne', 'reg', 'true');

// COD
$request->cashOnDelivery(350000);             // jumlah dalam rupiah

// Kurir dari config — cocok kalau toko sudah lock ke satu kurir (misal kontrak eksklusif)
$request->defaultCourier();                   // dari BITESHIP_COURIER_COMPANY + BITESHIP_COURIER_TYPE

// Shipper branding (override .env default)
$request->shipper('Toko Elektronik', '021xxxxxxx', organization: 'PT Toko Jaya');

$response = Biteship::orders()->create($request);

// $response->id              → Biteship order ID
// $response->status          → OrderStatus enum
// $response->waybill_id       → resi kurir (mungkin null saat baru dibuat)
// $response->price           → ongkir final
// $response->raw             → raw array dari API
```

### Find, Update, Cancel

```php
// Get detail
$order = Biteship::orders()->find($orderId);

// Cancel
$order = Biteship::orders()->cancel($orderId, 'Pembeli membatalkan');

// Update (misal koreksi alamat sebelum pickup)
$order = Biteship::orders()->update($orderId, [
    'destination_address' => 'Jl. Baru No. 5',
]);
```

### OrderStatus Enum

```php
use Aliziodev\Biteship\Enums\OrderStatus;

$status = $response->status;

$status->label();        // label Bahasa Indonesia
$status->isFinal();      // sudah terminal (delivered/cancelled/dll)
$status->isSuccess();    // delivered
$status->isInTransit();  // sedang dalam perjalanan
$status->isProblem();    // on_hold / return_in_transit
$status->canCancel();    // masih bisa dicancel via API
```

**14 values:** `confirmed`, `scheduled`, `allocated`, `picking_up`, `picked`, `dropping_off`, `delivered`, `on_hold`, `return_in_transit`, `returned`, `rejected`, `disposed`, `courier_not_found`, `cancelled`

---

## Tracking

```php
// Track via Biteship order ID
$tracking = Biteship::tracking()->trackByOrderId($orderId);

// Track via resi + kode kurir (public tracking — tidak perlu order dari Biteship)
$tracking = Biteship::tracking()->trackByWaybill('JD000000000', 'jne');

// $tracking->status     → TrackingStatus enum
// $tracking->waybill_id
// $tracking->history    → Collection<TrackingHistory>

foreach ($tracking->history as $h) {
    // $h->status, $h->note, $h->updatedAt
}
```

**TrackingStatus** memiliki 13 values (sama dengan OrderStatus minus `cancelled` — tracking tidak expose cancelled karena order sudah tidak ada di kurir).

---

## Couriers

```php
$couriers = Biteship::couriers()->all();
// Collection of array: 
//   available_collection_method
//   available_for_cash_on_delivery
//   available_for_proof_of_delivery
//   available_for_instant_waybill_id
//   courier_name
//   courier_code
//   courier_service_name
//   courier_service_code
//   tier
//   description
//   service_type
//   shipping_type
//   shipment_duration_range
//   shipment_duration_unit
```

---

## Label Print

Buat label pengiriman dari raw response order (zero API call tambahan).

```php
$label = Biteship::label()->fromRaw($response->raw);

// Render HTML string
$html = Biteship::label()->render($label);

// Return HTTP response siap di-print di browser
return Biteship::label()->response($label);
```

**Catatan:** `sender_name` dan `sender_phone` di label menggunakan data `shipper` (branding toko) jika tersedia, dengan fallback ke `origin`. `sender_address` selalu dari `origin`.

---

## Webhook & Events

Biteship mengirim webhook saat status order berubah. Package ini menangani routing, verifikasi signature, dan dispatch Laravel Event secara otomatis.

### Route

Webhook URL sudah di-register otomatis:

```
POST /biteship/webhook
```

Daftarkan URL ini di [Biteship Dashboard → Webhook Settings](https://beta.biteship.com/id/main/integration/api-key).

### Signature Verification

Set di Biteship Dashboard pada bagian **Custom Headers**:

```env
BITESHIP_WEBHOOK_SIGNATURE_KEY=X-Biteship-Signature
BITESHIP_WEBHOOK_SIGNATURE_SECRET=rahasia-kamu
```

Jika kedua nilai ini kosong, verifikasi dilewati (cocok untuk development).

### Listen Events

Daftarkan listener di `AppServiceProvider` atau `EventServiceProvider`:

```php
use Aliziodev\Biteship\Events\OrderStatusUpdated;
use Aliziodev\Biteship\Events\OrderWaybillUpdated;
use Aliziodev\Biteship\Events\OrderPriceUpdated;

Event::listen(OrderStatusUpdated::class, HandleBiteshipStatus::class);
Event::listen(OrderWaybillUpdated::class, HandleBiteshipWaybill::class);
Event::listen(OrderPriceUpdated::class, HandleBiteshipPrice::class);
```

### Event Payloads

**`OrderStatusUpdated`** — dipicu saat status order berubah (`order.status`):
```php
public function handle(OrderStatusUpdated $event): void
{
    $payload = $event->payload;

    $payload->order_id;            // string
    $payload->status;             // OrderStatus enum
    $payload->waybill_id;          // ?string
    $payload->courier_tracking_id;  // ?string
    $payload->raw;                // array mentah dari webhook
}
```

**`OrderWaybillUpdated`** — dipicu saat resi diterbitkan (`order.waybill_id`):
```php
public function handle(OrderWaybillUpdated $event): void
{
    $payload = $event->payload;

    $payload->order_id;            // string
    $payload->waybill_id;          // string
    $payload->courier_tracking_id;  // ?string
}
```

**`OrderPriceUpdated`** — dipicu saat harga final dikonfirmasi (`order.price`):
```php
public function handle(OrderPriceUpdated $event): void
{
    $payload = $event->payload;

    $payload->order_id;            // string
    $payload->price;              // int (rupiah)
    $payload->insurance_fee;       // int (rupiah)
}
```

Respons **401** dikembalikan jika signature tidak valid. Respons **422** dikembalikan jika event tidak dikenal.

---

## HasBiteship Trait (Optional DB Layer)

Simpan state pengiriman langsung di model Order kamu tanpa perlu menulis query manual.

### Setup

Saat `biteship:install`, pilih **Yes** untuk optional DB layer. Ini akan mempublish migration:

```bash
php artisan migrate
```

Tambahkan trait ke model Order:

```php
use Aliziodev\Biteship\Support\HasBiteship;

class Order extends Model
{
    use HasBiteship;
}
```

### Methods

```php
// Simpan response Biteship setelah order dibuat
$order->createBiteshipOrder($response);

// Sync status terbaru dari Tracking API ke DB (idempoten — skip jika status sama)
$order->syncBiteshipStatus();

// Cancel order via API + update status di DB
$order->cancelBiteship('Pembeli request cancel');

// Generate label dari raw_response di DB (zero API call)
$label = $order->generateLabel();
return Biteship::label()->response($label);

// Relasi ke tabel biteship_orders
$order->biteshipOrder;          // BiteshipOrder model
```

### Integrasi Webhook + HasBiteship

Pattern umum: update status DB langsung dari webhook tanpa API call tambahan.

```php
class HandleBiteshipStatus
{
    public function handle(OrderStatusUpdated $event): void
    {
        BiteshipOrder::where('biteship_order_id', $event->payload->order_id)
            ->update(['biteship_status' => $event->payload->status->value]);
    }
}
```

Atau gunakan `syncBiteshipStatus()` jika ingin selalu pull dari Tracking API (lebih akurat tapi ada API call tambahan):

```php
class HandleBiteshipStatus
{
    public function handle(OrderStatusUpdated $event): void
    {
        $order = Order::whereHas('biteshipOrder', fn ($q) =>
            $q->where('biteship_order_id', $event->payload->order_id)
        )->first();

        $order?->syncBiteshipStatus();
    }
}
```

---

## Testing / Mock Mode

Gunakan `MockBiteshipClient` untuk testing tanpa hit API:

```php
use Aliziodev\Biteship\Http\MockBiteshipClient;

// Bind di AppServiceProvider (untuk env testing) atau test setUp
$this->app->bind(
    \Aliziodev\Biteship\Contracts\BiteshipClientInterface::class,
    MockBiteshipClient::class,
);
```

Mock tersedia untuk semua endpoint: rates, orders, tracking, couriers, dan locations.

---

## Configuration

```bash
php artisan vendor:publish --tag=biteship-config
```

Key konfigurasi di `config/biteship.php`:

| Key | Default | Keterangan |
|-----|---------|------------|
| `api_key` | `env('BITESHIP_API_KEY')` | API key Biteship |
| `cache.enabled` | `true` | Cache hasil ongkir |
| `cache.ttl` | `900` | TTL dalam detik (15 menit) |
| `cache.store` | `null` | Cache store (null = default) |
| `cache.prefix` | `'biteship'` | Prefix cache key |
| `webhook.path` | `'biteship/webhook'` | Path endpoint webhook |
| `webhook.signature_key` | `null` | Nama custom header signature |
| `webhook.signature_secret` | `null` | Nilai secret untuk verifikasi |
| `default_origin` | `[...]` | Data origin default (dari env) |
| `default_courier` | `[...]` | Kurir default untuk order & rates (dari env) |
| `default_shipper` | `[...]` | Data shipper branding (dari env) |
| `label.view` | `'biteship::label'` | Blade view untuk label print |

---

## Exceptions

| Exception | Keterangan |
|-----------|------------|
| `AuthenticationException` | API key salah atau tidak aktif (401) |
| `ValidationException` | Request tidak valid (422) |
| `RateLimitException` | Rate limit tercapai (429) |
| `ApiException` | Error lain dari Biteship API |
| `WebhookSignatureException` | Signature header tidak cocok |
| `InvalidWebhookEventException` | Event webhook tidak dikenal |

---

## License

MIT — [Alizio](https://github.com/aliziodev)
