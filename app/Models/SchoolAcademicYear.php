<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;

class SchoolAcademicYear extends Model
{
    use HasFactory, HasUlids, LogsActivity;

    protected $fillable = [
        'school_id',
        'academic_year_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function scopeQ(Builder $query, string $search): Builder
    {
        $searchLower = strtolower($search);

        // Gunakan whereHas untuk filter pada relasi 'academicYear'
        return $query->whereHas('academicYear', function ($subQuery) use ($searchLower) {
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
        });
    }

    public function scopeSort(Builder $query, string $sortBy, string $sortDirection): Builder
    {
        return $query->join('academic_years', 'school_academic_years.academic_year_id', '=', 'academic_years.id')
            ->orderBy('academic_years.' . $sortBy, $sortDirection)
            ->select('school_academic_years.*');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function summativeTypes(): HasMany
    {
        return $this->hasMany(SummativeType::class);
    }
}
