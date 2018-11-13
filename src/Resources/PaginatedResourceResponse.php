<?php

namespace Eddic\Support\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse as ResourceResponse;

/**
 * Extend PaginatedResourceResponse
 */
class PaginatedResourceResponse extends ResourceResponse
{
    /**
     * Add the pagination information to the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function paginationInformation($request)
    {
        $paginator = $this->resource->resource;

        return [
            'data' => $this->meta($paginator),
        ];
    }

    /**
     * Gather the meta data for the response.
     *
     * @param  \Illuminate\Pagination\AbstractPaginator  $paginator
     * @return array
     */
    protected function meta($paginator)
    {
        $pagination = [
            'page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'count' => $paginator->count(),
            'has_more' => $paginator->hasMorePages(),
        ];

        if ($this->isLengthAware()) {
            $pagination += [
                'total' => $paginator->total(),
                'total_page' => $paginator->lastPage(),
            ];
        }

        return ['pagination' => $pagination];
    }

    /**
     * Is it a LengthAwarePaginator.
     *
     * @return bool
     */
    protected function isLengthAware()
    {
        return $this->resource->resource instanceof LengthAwarePaginator;
    }
}
