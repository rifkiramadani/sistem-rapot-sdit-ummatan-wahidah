<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Teacher extends Model
{
    use HasFactory, HasUlids, LogsActivity;

    protected $fillable = [
        'name',
        'niy',
        'user_id',
        'school_academic_year_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function scopeQ(Builder $query, string $search): Builder
    {
        $searchLower = strtolower($search);

        return $query->where(function ($subQuery) use ($searchLower) {
            // Cari pada kolom 'name' dan 'niy' di tabel teachers
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(niy) LIKE ?', ["%{$searchLower}%"])
                // Gunakan 'orWhereHas' untuk mencari pada relasi 'user'
                ->orWhereHas('user', function ($userQuery) use ($searchLower) {
                    $userQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"]);
                });
        });
    }

    /**
     * Get the user account associated with the teacher.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
