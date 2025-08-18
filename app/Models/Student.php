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
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Student extends Model
{
    use HasFactory, HasUlids, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable);
    }

    public function scopeQ(Builder $query, string $search): Builder
    {
        $searchLower = strtolower($search);

        return $query->where(function (Builder $subQuery) use ($searchLower) {
            // Cari di tabel students
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                ->orWhere('nisn', 'like', "%{$searchLower}%")
                // Cari juga di tabel relasi student_guardian
                ->orWhereHas('guardian', function ($guardianQuery) use ($searchLower) {
                    $guardianQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"]);
                });
        });
    }

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

    public function guardian(): HasOne
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