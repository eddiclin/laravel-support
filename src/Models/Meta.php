<?php

namespace Eddic\Support\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    /**
     * No timestamps for meta data
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * Defining guarded attributes on the model
     *
     * @var array
     */
    protected $guarded = [];
    
    /**
     * Maybe decode a meta value.
     * 
     * @param $value
     * @return mixed
     */
    public function getValueAttribute($value)
    {
        return maybe_json_decode($value, true);
    }
    
    /**
     * Maybe encode a value for saving.
     * 
     * @param $value
     * @return null
     */
    public function setValueAttribute($value)
    {
        if (is_null($value)) {
            throw new \InvalidArgumentException('the meta value cannot be null');
        }

        $this->attributes['value'] = maybe_json_encode($value);
    }
}
