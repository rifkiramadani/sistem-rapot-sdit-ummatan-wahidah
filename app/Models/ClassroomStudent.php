<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomStudent extends Model
{
    use HasFactory, HasUlids;

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
