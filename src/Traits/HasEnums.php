<?php

namespace Eddic\Support\Traits;

use ReflectionClass;

trait HasEnums
{
    /**
     * The array of enumerators of a given group.
     * 
     * @param null|String $group
     * @return array
     */
    public static function enums($group = null)
    {
        $constants = (new ReflectionClass(get_called_class()))->getConstants();
        
        if ($group) {
            return array_filter($constants, function ($key) use ($group) {
                return 0 === stripos($key, "{$group}_");
            }, ARRAY_FILTER_USE_KEY);
        }
        
        return $constants;
    }
    
    /**
     * Check if the given value is valid within the given group.
     * 
     * @param mixed $value
     * @param null|String $group
     * @return bool
     */
    public static function isValidEnumValue($value, $group = null)
    {
        return in_array($value, static::enums($group));
    }
    
    /**
     * Check if the given key exists.
     *
     * @param mixed $key
     * @return bool
     */
    public static function isValidEnumKey($key)
    {
        return array_key_exists($key, static::enums());
    }
}
