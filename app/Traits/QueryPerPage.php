<?php

namespace App\Traits;

/**
 * Trait PerPageQuery
 * Designed for Model classes. Works with per_page query parameter.
 */
trait QueryPerPage
{
    protected $perPageMax = 2000;

    /**
     * @see \Illuminate\Database\Eloquent\Model::getPerPage
     * @return int
     */
    public function getPerPage(): int
    {
        $perPage = (int)request('per_page', $this->perPage);

        return max(1, min($this->perPageMax, $perPage));
    }
}
