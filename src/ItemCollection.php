<?php

namespace Homc\ShopifyOrderParser;

use Homc\ShopifyOrderParser\Collection;

class ItemCollection extends Collection
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
