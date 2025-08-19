<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Summative extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'description',
        'identifier',
        'subject_id',
        'summative_type_id',
    ];

    /**
     * Mendapatkan mata pelajaran (subject) dari sumatif ini.
     */
    public function subject(): BelongsTo // TODO: change to classroomSubject
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Mendapatkan jenis sumatif (summative type) dari sumatif ini.
     */
    public function summativeType(): BelongsTo
    {
        return $this->belongsTo(SummativeType::class);
    }

    public function studentSummatives(): HasMany
    {
        return $this->hasMany(StudentSummative::class);
    }
}
