<?php

namespace App\Enums;

// This is a "string-backed" Enum.
// The string value is what gets stored in the database.
enum RoleEnum: string
{
    case SUPERADMIN = 'superadmin';
    case ADMIN = 'admin';
    case TEACHER = 'teacher';

    case PRINCIPAL = 'principal';
}
