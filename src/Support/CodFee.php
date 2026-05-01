<?php

namespace Aliziodev\Biteship\Support;

class CodFee
{
    /**
     * Data biaya COD per kurir.
     * Sumber: tabel resmi Biteship (diverifikasi Mei 2026).
     *
     * Struktur per kurir:
     *   7_days    — % fee untuk settlement 7 hari
     *   5_days    — % fee untuk settlement 5 hari
     *   3_days    — % fee untuk settlement 3 hari
     *   min_fee   — minimum fee dalam rupiah (null = tidak ada minimum)
     *   min_value — minimum nilai COD dalam rupiah (null = tidak ada minimum)
     *   max_value — maksimum nilai COD dalam rupiah (null = tidak ada maksimum)
     */
    private static array $rates = [
        'sicepat' => [
            '7_days' => 4,
            '5_days' => 5,
            '3_days' => 6,
            'min_fee' => 2000,
            'min_value' => 1000,
            'max_value' => 2_500_000,
        ],
        'jne' => [
            '7_days' => 4,
            '5_days' => 5,
            '3_days' => 6,
            'min_fee' => 3500,
            'min_value' => 25000,
            'max_value' => 5_000_000,
        ],
        'sap' => [
            '7_days' => 3,
            '5_days' => 4,
            '3_days' => 5,
            'min_fee' => null,
            'min_value' => null,
            'max_value' => 10_000_000,
        ],
        'anteraja' => [
            '7_days' => 3,
            '5_days' => 4,
            '3_days' => 5,
            'min_fee' => 5000,
            'min_value' => 5000,
            'max_value' => 2_500_000,
        ],
        'tiki' => [
            '7_days' => 3,
            '5_days' => 4,
            '3_days' => 5,
            'min_fee' => null,
            'min_value' => null,
            'max_value' => null,
        ],
        'jnt' => [
            '7_days' => 3,
            '5_days' => 4,
            '3_days' => 5,
            'min_fee' => null,
            'min_value' => null,
            'max_value' => null,
        ],
        'id_express' => [
            '7_days' => 3,
            '5_days' => 4,
            '3_days' => 5,
            'min_fee' => null,
            'min_value' => null,
            'max_value' => null,
        ],
    ];

    /**
     * Hitung biaya COD dalam rupiah.
     *
     * @param  string  $courier  Kode kurir, misal 'jne', 'sicepat'
     * @param  int  $codAmount  Nilai COD dalam rupiah
     * @param  string  $period  '7_days' | '5_days' | '3_days'
     */
    public static function calculate(string $courier, int $codAmount, string $period = '7_days'): int
    {
        $data = self::$rates[strtolower($courier)] ?? null;

        if ($data === null) {
            throw new \InvalidArgumentException("COD fee data not available for courier: {$courier}");
        }

        if (! isset($data[$period])) {
            throw new \InvalidArgumentException("Invalid period: {$period}. Use 7_days, 5_days, or 3_days.");
        }

        $fee = (int) ceil($codAmount * $data[$period] / 100);

        // Apply minimum fee jika ada
        if ($data['min_fee'] !== null && $fee < $data['min_fee']) {
            $fee = $data['min_fee'];
        }

        return $fee;
    }

    /**
     * Cek apakah kurir mendukung COD.
     */
    public static function supports(string $courier): bool
    {
        return isset(self::$rates[strtolower($courier)]);
    }

    /**
     * Maksimum nilai COD yang didukung kurir (null = tidak ada batas).
     */
    public static function maxValue(string $courier): ?int
    {
        return self::$rates[strtolower($courier)]['max_value'] ?? null;
    }

    /**
     * Minimum nilai COD yang didukung kurir (null = tidak ada batas).
     */
    public static function minValue(string $courier): ?int
    {
        return self::$rates[strtolower($courier)]['min_value'] ?? null;
    }

    /**
     * Semua kurir yang mendukung COD.
     *
     * @return array<string>
     */
    public static function supportedCouriers(): array
    {
        return array_keys(self::$rates);
    }
}
