<?php

namespace App\Enums;

enum PerPageEnum: string
{
    case P10 = '10';
    case P20 = '20';
    case P30 = '30';
    case P40 = '40';
    case P50 = '50';
    case P100 = '100';
    case Pall = 'all'; // <-- Tambahkan case baru di sini

    /**
     * Nilai default untuk paginasi.
     */
    public const DEFAULT = self::P10;

    /**
     * Mengembalikan semua nilai dari case Enum.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        // Logikanya menjadi lebih sederhana
        return array_column(self::cases(), 'value');
    }
}
