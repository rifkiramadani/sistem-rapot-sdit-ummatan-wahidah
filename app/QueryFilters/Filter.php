<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

class Filter
{
    public function handle(Builder|Relation $query, Closure $next)
    {
        $filters = request('filter');

        if (! $filters || ! is_array($filters)) {
            return $next($query);
        }

        foreach ($filters as $name => $value) {
            $methodName = Str::camel($name);

            // [UBAH] Cek apakah ada named scope yang cocok di model
            if ($query->hasNamedScope($methodName)) {
                if (! is_null($value)) {
                    // [UBAH] Panggil scope-nya pada query
                    // misal: $query->status('active')
                    $query->{$methodName}($value);
                }
            }
        }

        return $next($query);
    }
}
