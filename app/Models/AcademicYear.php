<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class AcademicYear extends Model
{
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start',
        'end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start' => 'date',
        'end' => 'date',
    ];

    public function scopeQ(Builder $query, string $search): Builder
    {
        $searchLower = strtolower($search);

        return $query->where(function (Builder $subQuery) use ($searchLower) {
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                // [UBAH] Tambahkan tanda kutip ganda pada nama kolom
                ->orWhereRaw('CAST("start" AS CHAR) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('CAST("end" AS CHAR) LIKE ?', ["%{$searchLower}%"]);
        });
    }

    public function schools(): HasMany
    {
        return $this->hasMany(School::class, 'current_academic_year_id');
    }

    /**
     * Get the pivot records linking this academic year to various schools.
     */
    public function schoolAcademicYears(): HasMany
    {
        return $this->hasMany(SchoolAcademicYear::class);
    }
}
