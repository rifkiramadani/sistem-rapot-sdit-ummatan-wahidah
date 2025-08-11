<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'school_academic_year_id',
    ];

    /**
     * Get the school academic year this subject belongs to.
     */
    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class);
    }

    public function summatives(): HasMany
    {
        return $this->hasMany(Summative::class);
    }
}