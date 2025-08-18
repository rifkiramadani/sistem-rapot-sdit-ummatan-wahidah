<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class Sort
{
    public function handle(Builder|Relation $query, Closure $next)
    {
        if (request()->has('sort_by')) {
            // Cek apakah model memiliki scope bernama 'sort'
            if ($query->hasNamedScope('sort')) {
                // Jika ada, serahkan semua logika sorting ke scope tersebut.
                // Ini memungkinkan penanganan kasus kompleks seperti JOIN.
                $query->sort(request('sort_by'), request('sort_direction', 'asc'));
            } else {
                // Jika tidak ada scope 'sort', gunakan orderBy sederhana seperti biasa.
                $query->orderBy(request('sort_by'), request('sort_direction', 'asc'));
            }
        }

        return $next($query);
    }
}