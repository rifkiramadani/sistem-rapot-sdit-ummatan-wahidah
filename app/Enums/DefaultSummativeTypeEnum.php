<?php

namespace App\Enums;

enum DefaultSummativeTypeEnum: string
{
    case MATERI = 'Sumatif Materi';
    case TENGAH_SEMESTER = 'Sumatif Tengah Semester';
    case AKHIR_SEMESTER = 'Sumatif Akhir Semester';
}
