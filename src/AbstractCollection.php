<?php

namespace Homc\ShopifyOrderParser;

use Homc\ShopifyOrderParser\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Homc\ShopifyOrderParser\Support\ArrayHelper as Arr;

abstract class AbstractCollection extends ArrayCollection
{
    /**
     * Get Resource By Key Value
     *
     * @param  array  $data
     * @param  string $key
     * @param  mixed  $value
     * @return array|null
     */
    public function getResourceByKeyValue(array $data, string $key, $value)
    {
        foreach ($data as $item) {
            if ($item[$key] === $value) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Get Resources By Key Value
     *
     * @param  array  $data
     * @param  string $key
     * @param  mixed  $value
     * @return array
     */
    public function getResourcesByKeyValue(array $data, string $key, $value)
    {
        $result = [];

        foreach ($data as $item) {
            if ($item[$key] === $value) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * __get
     *
     * This is a magic method used to return collection properties.
     *
     * Instead of using:
     *
     * $this->get('id');
     *
     * You can use:
     *
     * $this->id
     *
     * This also tries to return the currect type:
     *
     * "1234" => 1234
     *
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            $value = $this->get($name);

            if (is_numeric($value)) {
                $value = (int) $value;
            }

            if (is_array($value)) {
                $value = new Collection(Arr::flatten($value));
            }

            return $value;
        }
    }
}
