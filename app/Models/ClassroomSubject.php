<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomSubject extends Model
{
    use HasFactory, HasUlids;

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
}
