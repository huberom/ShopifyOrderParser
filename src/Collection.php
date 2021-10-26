<?php

namespace Homc\ShopifyOrderParser;

use Homc\ShopifyOrderParser\AbstractCollection;

class Collection extends AbstractCollection
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }
}
