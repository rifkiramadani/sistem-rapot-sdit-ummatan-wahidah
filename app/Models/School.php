<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'current_academic_year_id',
        'place_date_raport',
        'place_date_sts',
    ];

    public function principal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_principal_id');
    }

    public function currentAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'current_academic_year_id');
    }

    public function schoolAcademicYears(): HasMany
    {
        return $this->hasMany(SchoolAcademicYear::class);
    }
}
