<?php

namespace Eddic\Support\Traits;

trait DisableUpdatedAt
{
    public function setUpdatedAt($value)
    {
        // Do nothing.
    }

    public function getUpdatedAtColumn()
    {
        return null;
    }
}
