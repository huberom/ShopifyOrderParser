<?php

namespace Homc\ShopifyOrderParser\Helpers;

class ArrayHelper
{
    /**
     * Flatten
     *
     * @param  arry $array
     * @return array
     */
    public static function flatten(array $array) : array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $result[] = self::flatten($value);
                } else {
                    $result[$key] = self::flatten($value);
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
