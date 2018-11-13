<?php

namespace Eddic\Support\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection as IlluminateResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

class ResourceCollection extends IlluminateResourceCollection
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request)
    {
        return $this->resource instanceof AbstractPaginator
            ? (new PaginatedResourceResponse($this))->toResponse($request)
            : parent::toResponse($request);
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return ['list' => parent::toArray($request)];
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return ['code' => 0, 'message' => 'OK'];
    }
}
