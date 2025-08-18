<?php

namespace App\Support;

use App\Enums\PerPageEnum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation; // <-- Tambahkan import ini
use Illuminate\Pipeline\Pipeline;

class QueryBuilder
{
    // [UBAH] Gunakan Builder|Relation untuk fleksibilitas
    protected Builder|Relation $query;

    // [UBAH] Perbarui type hint di constructor
    public function __construct(Builder|Relation $query)
    {
        $this->query = $query;
    }

    /**
     * Metode static untuk memulai chain.
     * [UBAH] Perbarui type hint di sini juga
     */
    public static function for(Builder|Relation $query): self
    {
        return new static($query);
    }

    /**
     * Menjalankan query melalui serangkaian pipe.
     */
    public function through(array $pipes): self
    {
        $this->query = app(Pipeline::class)
            ->send($this->query)
            ->through($pipes)
            ->thenReturn();

        return $this;
    }

    /**
     * Menerapkan paginasi pada query yang sudah dibangun.
     */
    public function paginate(): LengthAwarePaginator
    {
        $perPage = request()->input('per_page', PerPageEnum::DEFAULT->value);

        if ($perPage === PerPageEnum::Pall->value) {
            $total = $this->query->count();

            return $this->query->paginate($total > 0 ? $total : PerPageEnum::DEFAULT->value)->withQueryString();
        }

        return $this->query->paginate($perPage)->withQueryString();
    }

    /**
     * Mengembalikan instance query builder yang sudah dimodifikasi.
     */
    public function getQuery(): Builder|Relation
    {
        return $this->query;
    }
}
