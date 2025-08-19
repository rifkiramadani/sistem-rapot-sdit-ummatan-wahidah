<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity; // <-- Tambahkan import ini
use Spatie\Activitylog\LogOptions; // <-- Tambahkan import ini
use Illuminate\Database\Eloquent\Builder;

class Classroom extends Model
{
    use HasFactory, HasUlids, LogsActivity;

    protected $fillable = [
        'name',
        'teacher_id',
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

        return $query->where(function (Builder $subQuery) use ($searchLower) {
            // Cari berdasarkan nama kelas
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                // Cari juga berdasarkan nama wali kelas di relasi teacher
                ->orWhereHas('teacher', function ($teacherQuery) use ($searchLower) {
                    $teacherQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                });
        });
    }


    /**
     * Get the teacher for this classroom.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the school academic year this classroom belongs to.
     */
    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class);
    }

    public function classroomStudents(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class);
    }

    public function classroomSubjects(): HasMany
    {
        return $this->hasMany(ClassroomSubject::class);
    }
}
