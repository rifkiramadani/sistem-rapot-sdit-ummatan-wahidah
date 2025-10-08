<?php

namespace App\Enums;

enum DefaultSummativeTypeEnum: string
{
    case MATERI = 'Sumatif Materi';
    case TENGAH_SEMESTER = 'Sumatif Tengah Semester';
    case AKHIR_SEMESTER = 'Sumatif Akhir Semester';

    /**
     * Mengembalikan semua nilai dari enum sebagai array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}