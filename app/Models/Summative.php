<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SummativeType; // Diperlukan untuk relasi summativeType()

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

    /**
     * LOGIKA CASCADE DELETE: Hapus semua StudentSummative terkait.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Summative $summative) {
            // Jika StudentSummative menggunakan Soft Deletes, gunakan forceDelete()
            $summative->studentSummatives()->delete();
        });
    }

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

    public function classroomSubject(): BelongsTo
    {
        return $this->belongsTo(ClassroomSubject::class);
    }

    /**
     * FIX: Relasi summativeType.
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
