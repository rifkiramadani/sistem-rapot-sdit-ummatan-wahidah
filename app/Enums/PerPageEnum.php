<?php

namespace App\Enums;

enum PerPageEnum: int
{
    case P10 = 10;
    case P20 = 20;
    case P30 = 30;
    case P40 = 40;
    case P50 = 50;
    case P100 = 100;

    /**
     * Nilai default untuk paginasi.
     */
    public const DEFAULT = self::P10;

    /**
     * Mengembalikan semua nilai integer dari case Enum.
     * Berguna untuk aturan validasi 'in'.
     *
     * @return array<int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
