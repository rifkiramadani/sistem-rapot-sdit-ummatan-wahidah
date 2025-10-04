<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SummativeType extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'school_academic_year_id',
    ];

    /**
     * Get the school academic year this summative type belongs to.
     */
    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class);
    }

    public function summatives(): HasOne
    {
        return $this->hasOne(Summative::class);
    }
}