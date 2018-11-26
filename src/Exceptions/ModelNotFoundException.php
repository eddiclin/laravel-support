<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException as NotFoundException;
use Illuminate\Support\Arr;

class ModelNotFoundException extends NotFoundException
{
    /**
     * 设置 model 名称和相关的 ids
     *
     * @param string $model
     * @param array $ids 扩展 ids 为组合键
     * @return $this
     */
    public function setModel($model, $ids = [])
    {
        $this->model = $model;
        $this->ids = Arr::wrap($ids);

        $this->message = "No query results for model [{$model}]";

        $idsString = $this->getIdsString();

        if (strlen($idsString) > 0) {
            $this->message .= ' - ' . $idsString;
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    protected function getIdsString()
    {
        if (! Arr::isAssoc($this->ids)) {
            return implode(', ', $this->ids);
        }

        $values = [];
        foreach ($this->ids as $key => $value) {
            $values[] = "{$key}: {$value}";
        }

        return implode(', ', $values);
    }
}
