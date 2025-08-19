<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;

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
}