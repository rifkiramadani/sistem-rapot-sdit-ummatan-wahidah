<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentParent extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'student_id',
        'father_name',
        'mother_name',
        'father_job',
        'mother_job',
        'address',
    ];

    /**
     * Get the student that this parent information belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
