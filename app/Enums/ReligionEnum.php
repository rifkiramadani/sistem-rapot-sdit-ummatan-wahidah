<?php

namespace App\Enums;

enum ReligionEnum: string
{
    case MUSLIM = 'muslim';
    case CHRISTIAN = 'christian';
    case CATHOLIC = 'catholic';
    case HINDU = 'hindu';
    case BUDDHIST = 'buddhist';
    case OTHER = 'other';
}
