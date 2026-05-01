<?php

return [

    /*
     * =========================================================================
     * 1. KONEKSI & AUTENTIKASI
     * =========================================================================
     *
     * Tiga nilai ini adalah fondasi dari semua request ke Biteship.
     * Pastikan api_key sudah diisi sebelum melakukan apapun.
     *
     * Prefix key menentukan mode:
     *   - biteship_test.*  → sandbox (aman untuk eksperimen)
     *   - biteship_live.*  → production (hati-hati!)
     */
    'api_key' => env('BITESHIP_API_KEY'),
    'base_url' => env('BITESHIP_BASE_URL', 'https://api.biteship.com'),
    'timeout' => env('BITESHIP_TIMEOUT', 30),  // detik

    /*
     * =========================================================================
     * 2. WEBHOOK
     * =========================================================================
     *
     * Konfigurasi ini menentukan bagaimana aplikasi kamu menerima notifikasi
     * dari Biteship — misalnya saat status order berubah dari "picked up"
     * ke "in transit", atau saat pengiriman gagal.
     *
     * Signature verification (opsional tapi sangat disarankan):
     * Di dashboard Biteship → Buat Webhook → Headers (Optional), isi:
     *   - "Headers Signature Key"    → nama header yang Biteship kirim
     *   - "Headers Signature Secret" → nilai header tersebut
     *
     * Package akan verifikasi setiap request masuk menggunakan hash_equals().
     * Kalau kamu skip ini, semua request ke endpoint webhook akan diterima
     * tanpa verifikasi — tidak aman untuk production.
     */
    'webhook' => [
        'path' => env('BITESHIP_WEBHOOK_PATH', 'biteship/webhook'),
        'middleware' => ['api'],
        'signature_key' => env('BITESHIP_WEBHOOK_SIGNATURE_KEY', null),
        'signature_secret' => env('BITESHIP_WEBHOOK_SIGNATURE_SECRET', null),
    ],

    /*
     * =========================================================================
     * 3. ORIGIN PENGIRIMAN (DEFAULT)
     * =========================================================================
     *
     * Ini adalah lokasi dari mana paket akan dijemput — bisa berupa gudang,
     * toko, atau titik pickup manapun. Dipakai sebagai default saat kamu
     * membuat order tanpa menyertakan origin secara eksplisit.
     *
     * Wajib isi salah satu: area_id ATAU postal_code.
     * Kalau keduanya diisi, area_id yang dipakai.
     */
    'default_origin' => [
        'area_id' => env('BITESHIP_ORIGIN_AREA_ID'),      // lebih presisi, diutamakan
        'postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE'),  // fallback jika area_id tidak ada
        'contact_name' => env('BITESHIP_ORIGIN_CONTACT_NAME'),
        'contact_phone' => env('BITESHIP_ORIGIN_CONTACT_PHONE'),
        'contact_email' => env('BITESHIP_ORIGIN_CONTACT_EMAIL'),
        'address' => env('BITESHIP_ORIGIN_ADDRESS'),
        'note' => env('BITESHIP_ORIGIN_NOTE'),          // petunjuk untuk kurir, opsional
    ],

    /*
     * =========================================================================
     * 4. CACHE RATES
     * =========================================================================
     *
     * Rates API Biteship punya rate limit. Kalau aplikasi kamu sering
     * menghitung ongkir dengan payload yang sama (misalnya dari halaman
     * checkout yang di-refresh berkali-kali), cache ini yang akan menyelamatkan.
     *
     * Default TTL 15 menit — cukup untuk sesi checkout normal.
     * Naikkan kalau tarif pengiriman di tokomu jarang berubah,
     * turunkan kalau kamu butuh data yang lebih real-time.
     *
     * 'store' → null berarti pakai cache driver default dari config/cache.php.
     * Bisa diisi 'redis', 'memcached', dll. sesuai kebutuhan.
     */
    'cache' => [
        'enabled' => env('BITESHIP_CACHE_ENABLED', true),
        'ttl' => env('BITESHIP_CACHE_TTL', 900),       // detik, default 15 menit
        'store' => env('BITESHIP_CACHE_STORE', null),
        'prefix' => env('BITESHIP_CACHE_PREFIX', 'biteship'),
    ],

    /*
     * =========================================================================
     * 5. MOCK MODE
     * =========================================================================
     *
     * Aktifkan ini saat development atau testing — semua request ke Rates
     * dan Orders API akan dijawab dengan data palsu tanpa menyentuh
     * API Biteship yang asli. Kuota aman, dompet aman.
     *
     * 'validation' → kalau true, mock tetap menolak input yang tidak valid,
     *                jadi behavior-nya mendekati API asli.
     * 'delay'      → artificial delay dalam milidetik, berguna untuk simulasi
     *                kondisi jaringan lambat saat testing loading state.
     *
     * Simulasi error — set ke true untuk menguji error handling di aplikasimu:
     *   authentication → 401, seolah-olah api_key salah
     *   rate_limit     → 429, seolah-olah kamu kena throttle
     *   validation     → 422, seolah-olah payload tidak valid
     *   server         → 500, seolah-olah server Biteship bermasalah
     */
    'mock_mode' => [
        'enabled' => env('BITESHIP_MOCK_MODE', false),
        'validation' => env('BITESHIP_MOCK_VALIDATION', true),
        'delay' => env('BITESHIP_MOCK_DELAY', 0),

        'errors' => [
            'authentication' => env('BITESHIP_MOCK_ERROR_AUTH', false),
            'rate_limit' => env('BITESHIP_MOCK_ERROR_RATE_LIMIT', false),
            'validation' => env('BITESHIP_MOCK_ERROR_VALIDATION', false),
            'server' => env('BITESHIP_MOCK_ERROR_SERVER', false),
        ],
    ],

    /*
     * =========================================================================
     * 6. LABEL
     * =========================================================================
     *
     * Menentukan tampilan label cetak yang digenerate oleh package.
     * Default-nya pakai Blade view bawaan — sudah cukup untuk kebanyakan kasus.
     *
     * Kalau kamu butuh format PDF, layout custom, atau branding khusus,
     * buat class yang implement LabelGeneratorInterface lalu daftarkan di sini.
     */
    'label' => [
        'view' => env('BITESHIP_LABEL_VIEW', 'biteship::label'),
    ],

    /*
     * =========================================================================
     * 7. SHIPPER DEFAULT
     * =========================================================================
     *
     * Informasi pengirim yang muncul di label cetak — nama toko, nomor telepon,
     * dan sebagainya. Ini murni untuk keperluan branding/tampilan saja,
     * tidak berpengaruh ke logika pengiriman.
     *
     * Kosongkan semua field ini kalau kamu tidak perlu branding khusus
     * pada label, atau kalau informasi pengirim selalu berbeda per order.
     */
    'default_shipper' => [
        'contact_name' => env('BITESHIP_SHIPPER_CONTACT_NAME'),
        'contact_phone' => env('BITESHIP_SHIPPER_CONTACT_PHONE'),
        'contact_email' => env('BITESHIP_SHIPPER_CONTACT_EMAIL'),
        'organization' => env('BITESHIP_SHIPPER_ORGANIZATION'),
    ],

];
