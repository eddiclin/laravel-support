<?php

namespace Eddic\Support\Models;

/**
 * Inspired by postmeta of WordPress.
 * Reference to phoenix/eloquent-meta.
 * @link https://github.com/chrismichaels84/eloquent-meta
 * 
 * It is little different with WordPress. It assumes that the specified
 * meta key of a particular model is unique.
 */
trait MetaTrait
{
    /**
     * Attache meta data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function meta()
    {
        $metaModel = isset($this->metaModel) ? $this->metaModel : Meta::class;
        return $this->morphMany($metaModel, 'metable');
    }
    
    /**
     * Get meta data.
     * 
     * @param string $key
     * @param mixed $default
     * @param bool $getObj
     * @return mixed
     */
    public function getMeta($key, $default = null, $getObj = false)
    {
        $meta = $this->meta()->where('key', $key)->first();
        if (is_null($meta)) {
            return $default;
        }
        
        return $getObj ? $meta : $meta->value;
    }
    
    /**
     * Add meta data.
     * 
     * @param string $key
     * @param mixed $value
     * @return bool|int
     */
    public function addMeta($key, $value)
    {
        if (! is_null($this->getMeta($key))) {
            return false;
        }
        
        $meta = $this->meta()->create([
            'key'   => $key,
            'value' => $value,
        ]);

        // return primary key
        return $meta->getKey();
    }
    
    /**
     * Update meta data.
     * If the meta field does not exist, it will be added.
     * 
     * @param string $key
     * @param mixed $newValue
     * @param bool $merge
     * @return bool|int
     */
    public function updateMeta($key, $newValue, $merge = false)
    {
        $meta = $this->getMeta($key, null, true);
        
        if (is_null($meta)) {
            return $this->addMeta($key, $newValue);
        }

        if ($merge && is_array($newValue) && is_array($meta->value)) {
            $newValue = array_merge($meta->value, $newValue);
        }

        return $meta->update(['value' => $newValue]);
    }
    
    /**
     * Delete meta data.
     * 
     * @param string $key
     * @return bool
     */
    public function deleteMeta($key)
    {
        return $this->meta()->where('key', $key)->delete();
    }
    
    /**
     * Update multiple metas.
     * 
     * @param array $metas
     * @param bool $merge
     * @return null
     */
    public function updateMetas($metas, $merge = false)
    {
        foreach ($metas as $key => $value) {
            $this->updateMeta($key, $value, $merge);
        }
    }
    
    /**
     * Get all meta data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllMeta()
    {
        return $this->meta->pluck('value', 'key');
    }
    
    /**
     * Deletes all meta data
     * 
     * @return mixed
     */
    public function deleteAllMeta()
    {
        return $this->meta()->delete();
    }
}
