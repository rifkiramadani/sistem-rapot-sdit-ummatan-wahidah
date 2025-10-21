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

class ClassroomStudent extends Model
{
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'classroom_students';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'classroom_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    /**
     * Scope untuk pencarian berdasarkan data siswa terkait.
     */
    public function scopeQ(Builder $query, string $search): Builder
    {
        return $query->whereHas('student', function (Builder $subQuery) use ($search) {
            $searchLower = strtolower($search);
            $subQuery->where(function (Builder $q) use ($searchLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhere('nisn', 'like', "%{$searchLower}%");
            });
        });
    }

    /**
     * Scope untuk menangani sorting yang kompleks, termasuk JOIN.
     * Akan dipanggil secara otomatis oleh pipe Sort.
     */
    public function scopeSort(Builder $query, string $sortBy, string $sortDirection): Builder
    {
        return $query->select('classroom_students.*')
            ->join('students', 'classroom_students.student_id', '=', 'students.id')
            ->orderBy('students.' . $sortBy, $sortDirection);
    }

    /**
     * Mendapatkan data siswa yang terkait.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Mendapatkan data kelas yang terkait.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Check if the given user has access to this classroom student record
     */
    public function canBeManagedBy($user): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        // Management roles can manage all classroom students
        if (in_array($user->role->name, [\App\Enums\RoleEnum::SUPERADMIN->value, \App\Enums\RoleEnum::ADMIN->value, \App\Enums\RoleEnum::PRINCIPAL->value])) {
            return true;
        }

        // Teachers can only manage classroom students in classrooms they homeroom
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
     * Static method to check if user can create classroom students in a specific classroom
     */
    public static function canBeCreatedBy($user, Classroom $classroom): bool
    {
        if (!$user || !$user->role) {
            return false;
        }

        // Management roles can create classroom students in any classroom
        if (in_array($user->role->name, [\App\Enums\RoleEnum::SUPERADMIN->value, \App\Enums\RoleEnum::ADMIN->value, \App\Enums\RoleEnum::PRINCIPAL->value])) {
            return true;
        }

        // Teachers can only create classroom students in classrooms they homeroom
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