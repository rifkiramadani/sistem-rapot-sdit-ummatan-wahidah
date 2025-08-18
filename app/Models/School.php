<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;

class School extends Model
{
    use HasFactory, HasUlids, LogsActivity;

    protected $fillable = [
        'name',
        'npsn',
        'address',
        'postal_code',
        'website',
        'email',
        'school_principal_id',
        'current_academic_year_id',
        'place_date_raport',
        'place_date_sts',
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
            $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                ->orWhere('npsn', 'like', "%{$searchLower}%");
        });
    }

    public function principal(): BelongsTo
    {
        return $this->belongsTo(User::class, 'school_principal_id');
    }

    public function currentAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'current_academic_year_id');
    }

    public function schoolAcademicYears(): HasMany
    {
        return $this->hasMany(SchoolAcademicYear::class);
    }

    // protected $appends = ['issue_key'];

    // protected function issueKey(): Attribute
    // {
    //     return Attribute::make(
    //         get: function ($value, array $attributes) {
    //             $words = explode(' ', trim($attributes['name']));

    //             // Jika hanya satu kata, ambil 3 huruf pertama
    //             if (count($words) === 1) {
    //                 return Str::upper(substr($attributes['name'], 0, 3));
    //             }

    //             // Jika lebih dari satu kata, ambil huruf pertama dari setiap kata
    //             $schoolNameInitial =  collect($words)->map(fn($word) => Str::upper(substr($word, 0, 1)))->implode('');

    //             return $schoolNameInitial;
    //         },
    //     );
    // }
}