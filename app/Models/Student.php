<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\ReligionEnum;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'nisn',
        'name',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'last_education',
        'address',
        'school_academic_year_id',
    ];

    protected $casts = [
        'gender' => GenderEnum::class,
        'religion' => ReligionEnum::class,
        'birth_date' => 'date',
    ];

    /**
     * Get the school academic year this student is enrolled in.
     */
    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class);
    }

    public function parent(): HasOne
    {
        return $this->hasOne(StudentParent::class);
    }

    public function guardians(): HasOne
    {
        return $this->hasOne(StudentGuardian::class);
    }

    public function studentClassrooms(): HasMany
    {
        return $this->hasMany(StudentClassroom::class);
    }

    public function studentSummatives(): HasMany
    {
        return $this->hasMany(StudentSummative::class);
    }
}
