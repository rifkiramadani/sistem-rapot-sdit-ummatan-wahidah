<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassroomSubject extends Model
{
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'classroom_subjects';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'classroom_id',
        'subject_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function scopeQ(Builder $query, string $search): Builder
    {
        return $query->whereHas('subject', function (Builder $subQuery) use ($search) {
            $searchLower = strtolower($search);
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
        });
    }

    /**
     * Scope untuk menangani sorting berdasarkan nama mata pelajaran.
     */
    public function scopeSort(Builder $query, string $sortBy, string $sortDirection): Builder
    {
        return $query->select('classroom_subjects.*')
            ->join('subjects', 'classroom_subjects.subject_id', '=', 'subjects.id')
            ->orderBy('subjects.' . $sortBy, $sortDirection);
    }

    /**
     * Mendapatkan data kelas yang terkait.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Mendapatkan data mata pelajaran yang terkait.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function summatives(): HasMany
    {
        return $this->hasMany(Summative::class);
    }

    /**
     * Check if the given user has access to this classroom subject record
     */
    public function canBeManagedBy($user): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        // Management roles can manage all classroom subjects
        if (in_array($user->role->name, [\App\Enums\RoleEnum::SUPERADMIN->value, \App\Enums\RoleEnum::ADMIN->value, \App\Enums\RoleEnum::PRINCIPAL->value])) {
            return true;
        }

        // Teachers can only manage classroom subjects in classrooms they homeroom
        if ($user->role->name === \App\Enums\RoleEnum::TEACHER->value) {
            $teacherRecord = $user->teacher()
                                 ->where('school_academic_year_id', $this->classroom->school_academic_year_id)
                                 ->first();

            if (!$teacherRecord) {
                return false;
            }

            return $teacherRecord->id === $this->classroom->teacher_id;
        }

        return false;
    }

    /**
     * Static method to check if user can create classroom subjects in a specific classroom
     */
    public static function canBeCreatedBy($user, Classroom $classroom): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        // Management roles can create classroom subjects in any classroom
        if (in_array($user->role->name, [\App\Enums\RoleEnum::SUPERADMIN->value, \App\Enums\RoleEnum::ADMIN->value, \App\Enums\RoleEnum::PRINCIPAL->value])) {
            return true;
        }

        // Teachers can only create classroom subjects in classrooms they homeroom
        if ($user->role->name === \App\Enums\RoleEnum::TEACHER->value) {
            $teacherRecord = $user->teacher()
                                 ->where('school_academic_year_id', $classroom->school_academic_year_id)
                                 ->first();

            if (!$teacherRecord) {
                return false;
            }

            return $teacherRecord->id === $classroom->teacher_id;
        }

        return false;
    }
}
