<?php

namespace Homc\ShopifyOrderParser\Exceptions;

use InvalidArgumentException;

class InvalidJsonException extends InvalidArgumentException
{
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return sprintf('Provide json is no valid in %s on line %s', $this->file, $this->line);
    }
}
