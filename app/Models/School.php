<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class School extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'npsn',
        'address',
        'postal_code',
        'website',
        'email',
        'school_principal_id',
        'current_academic_year',
        'place_date_raport',
        'place_date_sts',
    ];

    public function principal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_principal_id');
    }
}
