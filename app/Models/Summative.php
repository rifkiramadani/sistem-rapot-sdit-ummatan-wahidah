<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Summative extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'identifier',
        'classroom_subject_id',
        'summative_type_id',
        'prominent',
        'improvement',
    ];

    public function scopeQ(Builder $query, string $search): Builder
    {
        $searchLower = strtolower($search);

        return $query->where(function (Builder $subQuery) use ($searchLower) {
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(identifier) LIKE ?', ["%{$searchLower}%"])
                ->orWhereHas('summativeType', function ($typeQuery) use ($searchLower) {
                    $typeQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                });
        });
    }

    /**
     * Mendapatkan mata pelajaran (subject) dari sumatif ini.
     */
    public function classroomSubject(): BelongsTo // <-- Diubah
    {
        return $this->belongsTo(ClassroomSubject::class);
    }

    /**
     * Mendapatkan jenis sumatif (summative type) dari sumatif ini.
     */
    public function summativeType(): BelongsTo
    {
        return $this->belongsTo(SummativeType::class);
    }

    public function studentSummatives(): HasMany
    {
        return $this->hasMany(StudentSummative::class);
    }
}