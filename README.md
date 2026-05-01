# Laravel Biteship

<p align="center">
  <!-- Baris 1 -->
  <a href="https://github.com/aliziodev/laravel-biteship/actions"><img src="https://github.com/aliziodev/laravel-biteship/workflows/Tests/badge.svg" alt="Tests"></a>
  <a href="https://packagist.org/packages/aliziodev/laravel-biteship"><img src="https://img.shields.io/packagist/v/aliziodev/laravel-biteship.svg" alt="Latest Version on Packagist"></a>
  <a href="https://packagist.org/packages/aliziodev/laravel-biteship"><img src="https://img.shields.io/packagist/dt/aliziodev/laravel-biteship.svg" alt="Total Downloads"></a>
</br>
  <!-- Baris 2 -->
  <a href="https://packagist.org/packages/aliziodev/laravel-biteship"><img src="https://img.shields.io/packagist/php-v/aliziodev/laravel-biteship.svg" alt="PHP Version"></a>
  <a href="https://laravel.com/"><img src="https://img.shields.io/badge/Laravel-12.0%2B-orange.svg" alt="Laravel Version"></a>
  <a href="https://deepwiki.com/aliziodev/laravel-biteship"><img src="https://deepwiki.com/badge.svg" alt="Ask DeepWiki"></a>
</p>

Package Laravel untuk integrasi API [Biteship](https://biteship.com/) - aggregator pengiriman. Mempermudah perhitungan ongkir, pembuatan order, pelacakan, dan penanganan webhook di aplikasi Laravel Anda.

## Fitur

- ✅ **Rates API** - Cek ongkir dengan caching cerdas
- ✅ **Orders API** - Buat, ambil, dan batalkan order
- ✅ **Tracking API** - Lacak pengiriman berdasarkan order ID atau resi
- ✅ **Couriers API** - Daftar kurir yang tersedia
- ✅ **Locations API** - Kelola lokasi tersimpan
- ✅ **Webhook Handler** - Terima dan dispatch event Laravel untuk webhook Biteship
- ✅ **Label Generator** - Generate label pengiriman dari data order
- ✅ **Rate Caching** - Caching cerdas untuk mengurangi panggilan API
- ✅ **Type Safety** - Type hints lengkap dengan DTOs dan Enums
- ✅ **Exception Handling** - Exception terstruktur untuk error API


## Instalasi

Install package via Composer:

```bash
composer require aliziodev/laravel-biteship
```

Jalankan perintah install untuk publish konfigurasi dan migrasi opsional:

```bash
php artisan biteship:install
```

Perintah install akan:
- Publish file konfigurasi ke `config/biteship.php`
- Opsional publish migrasi database (jika Anda memilih menggunakan layer DB)
- Cek apakah `BITESHIP_API_KEY` sudah di-set di `.env`

## Konfigurasi

Tambahkan API key Biteship Anda ke file `.env`:

```env
BITESHIP_API_KEY=biteship_live.your_api_key_here
```

Untuk mode sandbox/testing, gunakan test key:

```env
BITESHIP_API_KEY=biteship_test.your_test_key_here
```

### Konfigurasi Lengkap

File `config/biteship.php` diorganisir dalam 7 bagian. Berikut penjelasan dan contoh penggunaannya:

```php
return [

    /*
     * 1. KONEKSI & AUTENTIKASI
     * =========================
     * Wajib diisi. Prefix key menentukan mode:
     * - biteship_test.*  → sandbox
     * - biteship_live.*  → production
     */
    'api_key'  => env('BITESHIP_API_KEY'),
    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
    'timeout'  => env('BITESHIP_TIMEOUT', 30),

    /*
     * 2. WEBHOOK
     * =========================
     * Konfigurasi endpoint untuk menerima notifikasi dari Biteship.
     * Signature verification sangat disarankan untuk production.
     */
    'webhook' => [
        'path'             => env('BITESHIP_WEBHOOK_PATH', 'biteship/webhook'),
        'middleware'       => ['api'],
        'signature_key'    => env('BITESHIP_WEBHOOK_SIGNATURE_KEY'),
        'signature_secret' => env('BITESHIP_WEBHOOK_SIGNATURE_SECRET'),
    ],

    /*
     * 3. ORIGIN PENGIRIMAN (DEFAULT)
     * =========================
     * Lokasi pickup default — gudang, toko, atau titik pengambilan paket.
     * Wajib set salah satu: area_id ATAU postal_code.
     */
    'default_origin' => [
        'area_id'       => env('BITESHIP_ORIGIN_AREA_ID'),
        'postal_code'   => env('BITESHIP_ORIGIN_POSTAL_CODE'),
        'contact_name'  => env('BITESHIP_ORIGIN_CONTACT_NAME'),
        'contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE'),
        'contact_email' => env('BITESHIP_ORIGIN_CONTACT_EMAIL'),
        'address'       => env('BITESHIP_ORIGIN_ADDRESS'),
        'note'          => env('BITESHIP_ORIGIN_NOTE'),
    ],

    /*
     * 4. CACHE RATES
     * =========================
     * Hindari rate limit dengan cache. TTL default 15 menit.
     */
    'cache' => [
        'enabled' => env('BITESHIP_CACHE_ENABLED', true),
        'ttl'     => env('BITESHIP_CACHE_TTL', 900),
        'store'   => env('BITESHIP_CACHE_STORE', null),
        'prefix'  => env('BITESHIP_CACHE_PREFIX', 'biteship'),
    ],

    /*
     * 5. MOCK MODE
     * =========================
     * Development & testing tanpa hit API asli.
     */
    'mock_mode' => [
        'enabled'    => env('BITESHIP_MOCK_MODE', false),
        'validation' => env('BITESHIP_MOCK_VALIDATION', true),
        'delay'      => env('BITESHIP_MOCK_DELAY', 0),
        'errors'     => [
            'authentication' => env('BITESHIP_MOCK_ERROR_AUTH', false),
            'rate_limit'     => env('BITESHIP_MOCK_ERROR_RATE_LIMIT', false),
            'validation'     => env('BITESHIP_MOCK_ERROR_VALIDATION', false),
            'server'         => env('BITESHIP_MOCK_ERROR_SERVER', false),
        ],
    ],

    /*
     * 6. LABEL
     * =========================
     * View untuk generate label cetak.
     */
    'label' => [
        'view' => env('BITESHIP_LABEL_VIEW', 'biteship::label'),
    ],

    /*
     * 7. SHIPPER DEFAULT
     * =========================
     * Branding pengirim di label — nama toko, kontak, dll.
     * Optional, terpisah dari origin.
     */
    'default_shipper' => [
        'contact_name'  => env('BITESHIP_SHIPPER_CONTACT_NAME'),
        'contact_phone' => env('BITESHIP_SHIPPER_CONTACT_PHONE'),
        'contact_email' => env('BITESHIP_SHIPPER_CONTACT_EMAIL'),
        'organization'  => env('BITESHIP_SHIPPER_ORGANIZATION'),
    ],

];
```

## Mock Mode (Development & Testing)

Package mendukung Mock Mode untuk development dan testing tanpa perlu hit API Biteship asli. Mock Mode hanya tersedia untuk **Rates API** dan **Orders API**.

### Aktifkan Mock Mode

```env
BITESHIP_MOCK_MODE=true
```

### Konfigurasi Mock Mode

```env
# Aktifkan mock mode
BITESHIP_MOCK_MODE=true

# Validasi input (seperti real API)
BITESHIP_MOCK_VALIDATION=true

# Artificial delay (dalam ms)
BITESHIP_MOCK_DELAY=500

# Error simulation (untuk testing error handling)
BITESHIP_MOCK_ERROR_AUTH=false      # Simulasi 401
BITESHIP_MOCK_ERROR_RATE_LIMIT=false # Simulasi 429
BITESHIP_MOCK_ERROR_VALIDATION=false # Simulasi 422
BITESHIP_MOCK_ERROR_SERVER=false     # Simulasi 500
```

### Contoh Penggunaan Mock Mode

```php
use Aliziodev\Biteship\Facades\Biteship;
use Aliziodev\Biteship\DTOs\Rate\RateRequest;

// Mock mode diaktifkan - tidak perlu API key!
$request = (new RateRequest)
    ->originAreaId('IDNP10001')
    ->originContact('Budi', '08123456789')
    ->originAddress('Jl. Sudirman No.1')
    ->destinationAreaId('IDNP20001')
    ->destinationContact('Ani', '08987654321')
    ->destinationAddress('Jl. Merdeka No.10')
    ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

$response = Biteship::rates()->check($request);

// Response dynamic dengan random ID dan harga
// Courier: JNE, SiCepat, J&T (selalu return 3 kurir)
// Price: calculated based on weight
```

### Fitur Mock Mode

| Fitur | Deskripsi |
|-------|-----------|
| Dynamic Response | ID random, harga calculated berdasarkan berat |
| Data Persistence | Order tersimpan di cache untuk retrieve/cancel |
| Validasi Input | Same validation rules seperti real API |
| Error Simulation | Test error handling dengan toggle env |
| COD Support | Mock mode mendukung cash on delivery |

### Switch ke Production

```env
# Production
BITESHIP_MOCK_MODE=false
BITESHIP_API_KEY=biteship_live.your_api_key_here
```

**Tidak perlu mengubah controller atau model** - hanya toggle env variable!

## Default Origin & Shipper

Jika toko Anda selalu mengirim dari lokasi yang sama, Anda bisa mengatur **default origin** dan **default shipper** di `.env` untuk menghindari pengulangan kode.

### Environment Variables

| Variable | Keterangan | Contoh |
|----------|------------|--------|
| `BITESHIP_ORIGIN_AREA_ID` | Area ID lokasi pickup (prioritas) | `IDNP6IDNC148...` |
| `BITESHIP_ORIGIN_POSTAL_CODE` | Kode pos lokasi pickup (fallback) | `12440` |
| `BITESHIP_ORIGIN_CONTACT_NAME` | Nama kontak di lokasi pickup | `Budi` |
| `BITESHIP_ORIGIN_CONTACT_PHONE` | Telepon kontak pickup | `08123456789` |
| `BITESHIP_ORIGIN_CONTACT_EMAIL` | Email kontak pickup (opsional) | `warehouse@toko.com` |
| `BITESHIP_ORIGIN_ADDRESS` | Alamat lengkap pickup | `Jl. Sudirman No.1, Jakarta` |
| `BITESHIP_ORIGIN_NOTE` | Catatan untuk kurir (opsional) | `Depan gerbang hitam` |
| `BITESHIP_SHIPPER_CONTACT_NAME` | Nama brand/toko di label | `Toko Elektronik` |
| `BITESHIP_SHIPPER_CONTACT_PHONE` | Telepon brand di label | `021-12345678` |
| `BITESHIP_SHIPPER_CONTACT_EMAIL` | Email brand di label (opsional) | `support@toko.com` |
| `BITESHIP_SHIPPER_ORGANIZATION` | Nama organisasi di label (opsional) | `PT Toko Elektronik` |

### Penggunaan di Controller

```php
use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\DTOs\Rate\RateRequest;

// Order dengan default origin & shipper
$orderRequest = (new OrderRequest)
    ->defaultOrigin()      // Auto-fill dari BITESHIP_ORIGIN_*
    ->defaultShipper()     // Auto-fill dari BITESHIP_SHIPPER_*
    ->destinationAreaId('IDNP20001')
    ->destinationContact('Ani', '08987654321')
    ->destinationAddress('Jl. Merdeka No.10, Bandung')
    ->courier('jne', 'REG')
    ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

// Cek ongkir dengan default origin
$rateRequest = (new RateRequest)
    ->defaultOrigin()      // Auto-fill dari BITESHIP_ORIGIN_*
    ->destinationAreaId('IDNP20001')
    ->destinationContact('Ani', '08987654321')
    ->destinationAddress('Jl. Merdeka No.10')
    ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

// Override jika perlu origin berbeda
$orderRequest = (new OrderRequest)
    ->defaultOrigin()
    ->originAreaId('IDNP30001')  // Override area_id untuk order ini saja
    ->destinationAreaId('IDNP20001')...
```

## Penggunaan

### Cek Ongkir

```php
use Aliziodev\Biteship\Facades\Biteship;
use Aliziodev\Biteship\DTOs\Rate\RateRequest;

$request = (new RateRequest)
    ->originAreaId('IDNP10001')
    ->originContact('Budi', '08123456789')
    ->originAddress('Jl. Sudirman No.1')
    ->destinationAreaId('IDNP20001')
    ->destinationContact('Ani', '08987654321')
    ->destinationAddress('Jl. Merdeka No.10')
    ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

$response = Biteship::rates()->check($request);

// Ambil rate termurah
$cheapest = $response->cheapest();
echo $cheapest->courierCode; // misal: 'sicepat'
echo $cheapest->price; // misal: 12000

// Filter berdasarkan kurir
$jneRates = $response->byCourier('jne');

// Ambil hanya kurir yang support COD
$codRates = $response->codAvailable();
```

#### Lewati Cache

Untuk operasi kritis yang membutuhkan data terbaru:

```php
$response = Biteship::rates()->fresh()->check($request);
```

#### Hapus Cache

```php
Biteship::rates()->forget($request);
```

### Membuat Order

```php
use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\Enums\OrderStatus;

$request = (new OrderRequest)
    ->originAreaId('IDNP10001')
    ->originContact('Ali Sender', '08123456789')
    ->originAddress('Jl. Sudirman No.1')
    ->destinationAreaId('IDNP20001')
    ->destinationContact('Ani Recipient', '08987654321')
    ->destinationAddress('Jl. Merdeka No.10')
    ->courier('jne', 'REG')
    ->addItem(['name' => 'Sepatu Olahraga', 'value' => 250000, 'weight' => 800, 'quantity' => 1]);

$order = Biteship::orders()->create($request);

echo $order->id; // ID order Biteship
echo $order->status; // enum OrderStatus
echo $order->price; // Harga pengiriman
```

#### Shipper Fields (Branding/Labeling)

Biteship API mendukung `shipper_*` fields untuk branding pengirim pada shipping label. Shipper terpisah dari `origin` (lokasi pickup).

**Via Environment Variables:**

```env
BITESHIP_SHIPPER_CONTACT_NAME="Toko Elektronik"
BITESHIP_SHIPPER_CONTACT_PHONE="021-12345678"
BITESHIP_SHIPPER_CONTACT_EMAIL="support@toko.com"
BITESHIP_SHIPPER_ORGANIZATION="PT Toko Elektronik Indonesia"
```

**Penggunaan:**

```php
// Auto-fill dari config
$request = (new OrderRequest)
    ->defaultShipper() // Load dari .env
    ->originAreaId('IDNP10001')
    ->originContact('Budi', '08123456789')
    ->originAddress('Jl. Sudirman No.1')
    ->destinationAreaId('IDNP20001')
    ->destinationContact('Ani', '08987654321')
    ->destinationAddress('Jl. Merdeka No.10')
    ->courier('jne', 'REG')
    ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);
```

**Manual Set:**

```php
$request = (new OrderRequest)
    ->shipper('Toko Elektronik', '021-12345678', 'support@toko.com', 'PT Toko Elektronik')
    // atau set individual:
    ->shipperName('Toko Elektronik')
    ->shipperPhone('021-12345678')
    ->shipperEmail('support@toko.com')
    ->shipperOrganization('PT Toko Elektronik')
    ->originAreaId('IDNP10001')...
```

**Generated Payload:**

```json
{
  "shipper_contact_name": "Toko Elektronik",
  "shipper_contact_phone": "021-12345678",
  "shipper_contact_email": "support@toko.com",
  "shipper_organization": "PT Toko Elektronik",
  "origin_contact_name": "Budi",
  "origin_contact_phone": "08123456789",
  ...
}
```

### Mengambil Order

```php
$order = Biteship::orders()->find('ORD-123456');
```

### Membatalkan Order

```php
$order = Biteship::orders()->cancel('ORD-123456', 'Pembeli membatalkan');
```

### Melacak Pengiriman

```php
// Lacak berdasarkan ID order Biteship
$tracking = Biteship::tracking()->byOrderId('ORD-123456');

// Lacak berdasarkan resi (public tracking - works untuk kurir apapun)
$tracking = Biteship::tracking()->byWaybill('JNE00123456789', 'jne');
```

### Daftar Kurir

```php
$couriers = Biteship::couriers()->all();
```

### Search Area (Maps API)

**Skenario:** Form input alamat dengan autocomplete untuk memudahkan user memilih area pengiriman yang valid.

```php
use Aliziodev\Biteship\Facades\Biteship;

class AddressController extends Controller
{
    public function searchArea(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        // Search area berdasarkan input user
        $areas = Biteship::locations()->search(
            $request->query,
            $request->type ?? 'single' // 'single' atau 'all'
        );

        return response()->json([
            'areas' => $areas->map(function ($area) {
                return [
                    'id' => $area['id'], // Area ID untuk digunakan di RateRequest
                    'name' => $area['name'],
                    'description' => $area['description'],
                    'country' => $area['country'],
                    'province' => $area['province'],
                    'city' => $area['city'],
                    'type' => $area['type'],
                ];
            }),
        ]);
    }
}
```

**Contoh response:**
```json
{
  "areas": [
    {
      "id": "IDNP6IDNC148...",
      "name": "Jakarta Selatan",
      "description": "Jakarta Selatan, DKI Jakarta",
      "country": "ID",
      "province": "DKI Jakarta",
      "city": "Jakarta Selatan",
      "type": "single"
    }
  ]
}
```

### Mengelola Lokasi

```php
use Aliziodev\Biteship\DTOs\Location\LocationRequest;

// Buat lokasi
$location = Biteship::locations()->create(
    (new LocationRequest)
        ->name('Apotik Gambir')
        ->contactName('Ahmad')
        ->contactPhone('08123456789')
        ->address('Jl. Gambir Selatan')
        ->postalCode('10110')
        ->coordinates(-6.232, 102.221)
        ->type('origin')
);

// Cari lokasi
$location = Biteship::locations()->find($id);

// Update lokasi
$location = Biteship::locations()->update($id, ['name' => 'Apotek Monas']);

// Hapus lokasi
Biteship::locations()->delete($id);
```

### Generate Label Pengiriman

```php
use Aliziodev\Biteship\Facades\Biteship;

// Generate data label
$label = Biteship::label()->generate($orderResponse);

// Render sebagai HTML
$html = Biteship::label()->render($orderResponse);

// Return sebagai HTTP response
return Biteship::label()->response($orderResponse);
```

Anda dapat publish dan mengkustomisasi view label:

```bash
php artisan vendor:publish --tag=biteship-views
```

## Real Case Implementasi

### Case 1: E-commerce Checkout dengan Cek Ongkir Real-time

**Skenario:** Toko online ingin menampilkan ongkir di halaman checkout saat user memilih alamat dan kurir.

```php
use Aliziodev\Biteship\Facades\Biteship;
use Aliziodev\Biteship\DTOs\Rate\RateRequest;

class CheckoutController extends Controller
{
    public function calculateShipping(Request $request)
    {
        $cart = Cart::where('user_id', auth()->id())->with('product')->get();
        
        // Build request dari cart
        $rateRequest = (new RateRequest)
            ->originAreaId(config('store.origin_area_id'))
            ->originContact(config('store.contact_name'), config('store.contact_phone'))
            ->originAddress(config('store.address'))
            ->destinationPostalCode($request->postal_code)
            ->destinationContact($request->name, $request->phone)
            ->destinationAddress($request->address);

        // Tambahkan semua item dari cart
        foreach ($cart as $item) {
            $rateRequest->addItem([
                'name' => $item->product->name,
                'value' => $item->product->price,
                'weight' => $item->product->weight,
                'quantity' => $item->quantity,
            ]);
        }

        // Cek ongkir (otomatis di-cache 15 menit)
        $response = Biteship::rates()->check($rateRequest);

        // Filter hanya kurir yang support COD jika user bayar COD
        if ($request->payment_method === 'cod') {
            $rates = $response->codAvailable();
        } else {
            $rates = $response->pricing;
        }

        return response()->json([
            'rates' => $rates,
            'cheapest' => $response->cheapest(),
        ]);
    }
}
```

### Case 2: Order dengan COD dan Asuransi

**Skenario:** Toko elektronik ingin mengirim barang dengan COD dan asuransi untuk barang bernilai tinggi.

```php
use Aliziodev\Biteship\Facades\Biteship;
use Aliziodev\Biteship\DTOs\Order\OrderRequest;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $order = Order::create($request->validated());
        
        // Buat order ke Biteship
        $biteshipRequest = (new OrderRequest)
            ->originAreaId(config('store.origin_area_id'))
            ->originContact('Toko Elektronik', '021-12345678')
            ->originAddress('Jl. Teknologi No. 10, Jakarta')
            ->destinationPostalCode($order->shipping_postal_code)
            ->destinationContact($order->recipient_name, $order->recipient_phone)
            ->destinationAddress($order->shipping_address)
            ->courier($request->courier_code, $request->courier_type);

        // Tambahkan item
        foreach ($order->items as $item) {
            $biteshipRequest->addItem([
                'name' => $item->product_name,
                'value' => $item->price,
                'weight' => $item->weight,
                'quantity' => $item->quantity,
            ]);
        }

        // Tambahkan asuransi jika barang bernilai > 1 juta
        if ($order->total_value > 1000000) {
            $biteshipRequest->withInsurance($order->total_value);
        }

        // Tambahkan COD jika pembayaran COD
        if ($order->payment_method === 'cod') {
            $biteshipRequest->withCOD($order->total_amount, '7_days');
        }

        try {
            $biteshipOrder = Biteship::orders()->create($biteshipRequest);
            
            // Simpan ID order Biteship
            $order->update([
                'biteship_order_id' => $biteshipOrder->id,
                'shipping_cost' => $biteshipOrder->price,
                'status' => 'processing',
            ]);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order berhasil dibuat');
                
        } catch (RateLimitException $e) {
            // Retry dengan queue job
            dispatch(new CreateBiteshipOrderJob($order, $biteshipRequest))
                ->delay(now()->addSeconds($e->retryAfter() ?? 5));
            
            return back()->with('warning', 'Order sedang diproses');
        }
    }
}
```

### Case 3: Auto-update Status Order via Webhook

**Skenario:** Sistem ingin otomatis update status order dan kirim notifikasi ke customer saat status pengiriman berubah.

```php
// app/Listeners/UpdateOrderStatusFromWebhook.php
namespace App\Listeners;

use Aliziodev\Biteship\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Notifications\ShippingStatusNotification;

class UpdateOrderStatusFromWebhook
{
    public function handle(OrderStatusUpdated $event)
    {
        // Cari order berdasarkan Biteship order ID
        $order = Order::where('biteship_order_id', $event->payload->orderId)->first();
        
        if (! $order) {
            return;
        }

        // Update status order
        $oldStatus = $order->shipping_status;
        $order->update(['shipping_status' => $event->payload->status]);

        // Kirim notifikasi jika status berubah
        if ($oldStatus !== $event->payload->status) {
            $order->user->notify(new ShippingStatusNotification($order));
            
            // Jika status delivered, auto-complete order
            if ($event->payload->status === 'delivered') {
                $order->update(['status' => 'completed']);
            }
            
            // Jika status returned, buat refund request
            if ($event->payload->status === 'returned') {
                dispatch(new CreateRefundRequestJob($order));
            }
        }
    }
}
```

### Case 4: Tracking Page untuk Customer

**Skenario:** Halaman tracking untuk customer melihat status pengiriman secara real-time.

```php
use Aliziodev\Biteship\Facades\Biteship;

class TrackingController extends Controller
{
    public function show($orderId)
    {
        $order = Order::with('items')->findOrFail($orderId);
        
        // Ambil data tracking terbaru dari Biteship
        $tracking = Biteship::tracking()->byOrderId($order->biteship_order_id);
        
        return view('tracking.show', [
            'order' => $order,
            'tracking' => $tracking,
            'history' => $tracking->history, // Array history status
            'currentStatus' => $tracking->status,
        ]);
    }
}
```

### Case 5: Public Tracking untuk Resi Apapun

**Skenario:** Customer ingin melacak paket dengan nomor resi dari kurir manapun (tidak harus order dari toko kita).

```php
class PublicTrackingController extends Controller
{
    public function track(Request $request)
    {
        $request->validate([
            'waybill' => 'required|string',
            'courier' => 'required|string|in:jne,sicepat,jnt,anteraja',
        ]);

        try {
            $tracking = Biteship::tracking()->byWaybill(
                $request->waybill,
                $request->courier
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'waybill' => $tracking->waybillId,
                    'status' => $tracking->status,
                    'history' => $tracking->history,
                ],
            ]);
        } catch (ApiException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resi tidak ditemukan atau kurir salah',
            ], 404);
        }
    }
}
```

### Case 6: Autocomplete Alamat di Form Checkout

**Skenario:** Form checkout dengan autocomplete alamat untuk memudahkan user memilih area yang valid dan mendapatkan area_id yang akurat.

```php
use Aliziodev\Biteship\Facades\Biteship;

class CheckoutController extends Controller
{
    public function searchAddress(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 3) {
            return response()->json(['areas' => []]);
        }

        // Search area dengan Maps API
        $areas = Biteship::locations()->search($query, 'single');

        return response()->json([
            'areas' => $areas->take(10)->map(function ($area) {
                return [
                    'id' => $area['id'],
                    'text' => $area['description'],
                    'province' => $area['province'],
                    'city' => $area['city'],
                ];
            }),
        ]);
    }

    public function calculateShipping(Request $request)
    {
        // Gunakan area_id dari hasil autocomplete
        $rateRequest = (new RateRequest)
            ->originAreaId(config('store.origin_area_id'))
            ->originContact(config('store.contact_name'), config('store.contact_phone'))
            ->originAddress(config('store.address'))
            ->destinationAreaId($request->destination_area_id) // Area ID dari autocomplete
            ->destinationContact($request->name, $request->phone)
            ->destinationAddress($request->address);

        // Tambahkan item dari cart
        foreach (Cart::items() as $item) {
            $rateRequest->addItem([
                'name' => $item->name,
                'value' => $item->price,
                'weight' => $item->weight,
                'quantity' => $item->quantity,
            ]);
        }

        $response = Biteship::rates()->check($rateRequest);

        return response()->json([
            'rates' => $response->pricing,
            'cheapest' => $response->cheapest(),
        ]);
    }
}
```

**Frontend (JavaScript/Vue/React):**
```javascript
// Contoh implementasi dengan debounce
async function searchAddress(query) {
    if (query.length < 3) return [];
    
    const response = await fetch(`/checkout/search-address?q=${query}`);
    const data = await response.json();
    
    return data.areas;
}

// Gunakan area_id yang dipilih untuk cek ongkir
async function calculateShipping(areaId) {
    const response = await fetch('/checkout/shipping', {
        method: 'POST',
        body: JSON.stringify({ destination_area_id: areaId }),
    });
    
    return await response.json();
}
```

### Case 7: Display Daftar Kurir di Frontend

**Skenario:** Halaman checkout ingin menampilkan daftar kurir yang tersedia dengan logo dan informasi layanan.

```php
use Aliziodev\Biteship\Facades\Biteship;

class CourierController extends Controller
{
    public function index()
    {
        // Ambil semua kurir yang tersedia
        $couriers = Biteship::couriers()->all();
        
        // Group berdasarkan kode kurir untuk kemudahan display
        $grouped = $couriers->groupBy('code');
        
        return response()->json([
            'couriers' => $grouped->map(function ($items, $code) {
                return [
                    'code' => $code,
                    'name' => $items->first()->name,
                    'logo' => $items->first()->logo_url,
                    'services' => $items->map(function ($item) {
                        return [
                            'type' => $item->type,
                            'name' => $item->service_name,
                            'description' => $item->description,
                        ];
                    })->values(),
                ];
            })->values(),
        ]);
    }
}
```

### Case 8: Manajemen Lokasi untuk Multi-warehouse

**Skenario:** Toko dengan beberapa gudang ingin menyimpan lokasi gudang sebagai origin yang bisa digunakan berulang.

```php
class WarehouseController extends Controller
{
    public function store(Request $request)
    {
        // Simpan lokasi gudang ke Biteship
        $location = Biteship::locations()->create(
            (new LocationRequest)
                ->name($request->name)
                ->contactName($request->contact_name)
                ->contactPhone($request->contact_phone)
                ->address($request->address)
                ->postalCode($request->postal_code)
                ->coordinates($request->latitude, $request->longitude)
                ->type('origin')
        );

        // Simpan ID lokasi Biteship ke database lokal
        Warehouse::create([
            'name' => $request->name,
            'biteship_location_id' => $location->id,
            'address' => $request->address,
            // ...
        ]);

        return back()->with('success', 'Gudang berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        
        // Update lokasi di Biteship
        Biteship::locations()->update(
            $warehouse->biteship_location_id,
            [
                'name' => $request->name,
                'contact_name' => $request->contact_name,
                'contact_phone' => $request->contact_phone,
                'address' => $request->address,
            ]
        );

        // Update lokal
        $warehouse->update($request->only(['name', 'address']));

        return back()->with('success', 'Gudang berhasil diupdate');
    }
}
```

### Case 9: Generate dan Print Label Pengiriman

**Skenario:** Admin ingin generate label PDF untuk dicetak dan ditempel di paket.

```php
class ShippingLabelController extends Controller
{
    public function generate($orderId)
    {
        $order = Order::with('biteshipOrder')->findOrFail($orderId);
        
        // Ambil data order terbaru dari Biteship
        $biteshipOrder = Biteship::orders()->find($order->biteship_order_id);
        
        // Generate label HTML
        $html = Biteship::label()->render($biteshipOrder);
        
        // Konversi ke PDF (gunakan dompdf/snappy)
        $pdf = app('pdf')->loadHTML($html);
        
        return $pdf->download("label-{$order->id}.pdf");
    }

    public function print($orderId)
    {
        $order = Order::with('biteshipOrder')->findOrFail($orderId);
        $biteshipOrder = Biteship::orders()->find($order->biteship_order_id);
        
        // Return HTML response untuk print langsung
        return Biteship::label()->response($biteshipOrder);
    }
}
```

### Case 10: Cancellation Order dengan Validasi

**Skenario:** Customer ingin batalkan order, tapi hanya bisa dibatalkan jika status masih `confirmed`, `scheduled`, atau `allocated`.

```php
class OrderCancellationController extends Controller
{
    public function cancel(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Validasi: hanya bisa batalkan jika belum dipickup
        if (! in_array($order->shipping_status, ['confirmed', 'scheduled', 'allocated'])) {
            return back()->with('error', 'Order tidak dapat dibatalkan karena sudah diproses kurir');
        }

        try {
            // Batalkan di Biteship
            $cancelledOrder = Biteship::orders()->cancel(
                $order->biteship_order_id,
                $request->reason ?? 'Dibatalkan oleh customer'
            );

            // Update status lokal
            $order->update([
                'status' => 'cancelled',
                'shipping_status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->reason,
            ]);

            // Proses refund jika perlu
            if ($order->payment_method === 'midtrans') {
                dispatch(new ProcessRefundJob($order));
            }

            return back()->with('success', 'Order berhasil dibatalkan');

        } catch (ApiException $e) {
            return back()->with('error', 'Gagal membatalkan order: ' . $e->getMessage());
        }
    }
}
```

## Webhooks

### Setup Webhook

1. Konfigurasi URL webhook di dashboard Biteship:
   ```
   https://your-app.com/biteship/webhook
   ```

2. Opsional konfigurasi verifikasi signature di dashboard Biteship:
   - **Headers Signature Key**: `X-Biteship-Signature` (atau nama custom)
   - **Headers Signature Secret**: `your-secret-value`

3. Tambahkan environment variable yang sesuai:
   ```env
   BITESHIP_WEBHOOK_SIGNATURE_KEY=X-Biteship-Signature
   BITESHIP_WEBHOOK_SIGNATURE_SECRET=your-secret-value
   ```

### Menangani Event Webhook

Package mendispatch event Laravel untuk setiap tipe webhook. Dengarkan di `EventServiceProvider` Anda:

```php
use Aliziodev\Biteship\Events\OrderStatusUpdated;
use Aliziodev\Biteship\Events\OrderPriceUpdated;
use Aliziodev\Biteship\Events\OrderWaybillUpdated;

protected $listen = [
    OrderStatusUpdated::class => [
        SendShippingNotification::class,
        UpdateOrderStatus::class,
    ],
    OrderPriceUpdated::class => [
        HandlePriceChange::class,
    ],
    OrderWaybillUpdated::class => [
        UpdateWaybillNumber::class,
    ],
];
```

### Payload Event

Setiap event berisi payload DTO dengan data yang relevan:

```php
Event::listen(OrderStatusUpdated::class, function ($event) {
    $orderId = $event->payload->orderId;
    $status = $event->payload->status;
    // Handle update status
});
```

## Exception Handling

Package menyediakan exception terstruktur untuk skenario error berbeda:

```php
use Aliziodev\Biteship\Exceptions\AuthenticationException;
use Aliziodev\Biteship\Exceptions\RateLimitException;
use Aliziodev\Biteship\Exceptions\ValidationException;
use Aliziodev\Biteship\Exceptions\ApiException;

try {
    $rates = Biteship::rates()->check($request);
} catch (AuthenticationException $e) {
    // API key tidak valid
} catch (RateLimitException $e) {
    // Rate limit terlampaui - gunakan retryAfter() untuk backoff
    $retryAfter = $e->retryAfter(); // detik untuk menunggu
} catch (ValidationException $e) {
    // Data request tidak valid
} catch (ApiException $e) {
    // Error API lainnya
}
```

### Penanganan Rate Limit

```php
try {
    $rates = Biteship::rates()->check($request);
} catch (RateLimitException $e) {
    // Retry setelah waktu yang ditentukan
    dispatch(new CheckRatesJob($request))
        ->delay(now()->addSeconds($e->retryAfter() ?? 5));
}
```

## Testing

Jalankan test suite:

```bash
composer test
```

Atau menggunakan Pest langsung:

```bash
./vendor/bin/pest
```

## Layer Database (Opsional)

Jika Anda memilih menggunakan layer database opsional saat instalasi:

1. Jalankan migrasi:
   ```bash
   php artisan migrate
   ```

2. Tambahkan trait `HasBiteship` ke model Order Anda:
   ```php
   use Aliziodev\Biteship\Concerns\HasBiteship;

   class Order extends Model
   {
       use HasBiteship;
   }
   ```

3. Gunakan helper methods:
   ```php
   $order->biteshipOrder; // Relasi ke biteship_orders
   $order->biteship_status; // Shortcut attribute
   $order->biteship_waybill_id; // Shortcut attribute
   ```

## Batas Rate API

Biteship memiliki batas rate berikut (berdasarkan dokumentasi):

| API | Production | Sandbox |
|-----|-----------|---------|
| Maps | 50 req/s | 5 req/s |
| Rates | 20 req/s | 5 req/s |
| Location | 10 req/s | 5 req/s |
| Order | 20 req/s | 5 req/s |
| Tracking | 50 req/s | 5 req/s |

Rate caching bawaan membantu mengurangi risiko mencapai batas ini.

## Keamanan

- Verifikasi signature webhook menggunakan `hash_equals()` untuk perbandingan yang aman dari timing attack
- API key disimpan di environment variables
- Semua request HTTP menggunakan konfigurasi timeout yang tepat
- Exception terstruktur mencegah kebocoran data sensitif

## Changelog

Silakan lihat [CHANGELOG.md](CHANGELOG.md) untuk perubahan terbaru.

## Kontribusi

Kontribusi sangat diterima! Silakan submit Pull Request.

## Lisensi

The MIT License (MIT). Silakan lihat [LICENSE](LICENSE) untuk informasi lebih lanjut.


## Dukungan

Jika Anda mengalami masalah atau memiliki pertanyaan, silakan buka issue di [GitHub](https://github.com/aliziodev/laravel-biteship/issues).

## Tautan

- [Dokumentasi Biteship](https://biteship.com/docs)
- [Dokumentasi Laravel](https://laravel.com/docs)
