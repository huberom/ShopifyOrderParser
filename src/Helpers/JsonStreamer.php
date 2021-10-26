<?php

namespace Homc\ShopifyOrderParser\Helpers;

use JsonMachine\JsonMachine;

class JsonStreamer
{
    /**
     * Create
     *
     * This uses a JSON stream to iterate over the data
     * and create a PHP array.
     *
     * @param  string $json
     * @return array
     */
    public static function create(string $json) : array
    {
        $result = [];
        $stream = JsonMachine::fromString($json);

        foreach ($stream as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }
}
