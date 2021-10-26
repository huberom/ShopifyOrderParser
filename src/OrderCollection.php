<?php

namespace Homc\ShopifyOrderParser;

use Homc\ShopifyOrderParser\Collection;
use Homc\ShopifyOrderParser\ItemCollection;

class OrderCollection extends Collection
{
    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Get Item By Id
     *
     * @param  int    $id
     * @return ItemCollection
     */
    public function getItemById(int $id)
    {
        $item = $this->getResourceByKeyValue($this->get('line_items'), 'product_id', $id);

        return $item ? new ItemCollection($item) : null;
    }

    /**
     * Get Item Ids
     *
     * @return array
     */
    public function getItemIds()
    {
        $result = [];

        foreach ($this->getItems() as $item) {
            $result[] = $item->get('product_id');
        }

        return $result;
    }

    /**
     * Get Items
     *
     * @return array[ItemCollection]
     */
    public function getItems()
    {
        /**
         * Keep track of ids because the same id can be
         * present with different variations.
         */
        $foundItemIds = [];

        return array_map(function ($item) use ($foundItemIds) {
            if (!in_array($item['product_id'], $foundItemIds)) {
                $foundItemIds[] = $item['product_id'];
                return new ItemCollection($item);
            }
        }, $this->get('line_items'));
    }

    /**
     * Get Number of Items
     *
     * @return int
     */
    public function getNumberOfItems()
    {
        return count($this->get('line_items'));
    }
}
