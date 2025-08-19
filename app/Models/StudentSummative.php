<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSummative extends Model // TODO: SummativeStudent
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'student_id',
        'summative_id',
        'value',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function summative(): BelongsTo
    {
        return $this->belongsTo(Summative::class);
    }
}