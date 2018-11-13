<?php

namespace Eddic\Support\Resources;

use Illuminate\Http\Resources\Json\Resource as IlluminateResource;

class Resource extends IlluminateResource
{
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
