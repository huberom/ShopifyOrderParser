<?php

/**
 * Shopify Batch Order Parser
 *
 * A tool to parse, query, and manipulate Shopify orders.
 */
namespace Homc\ShopifyOrderParser;

use Homc\ShopifyOrderParser\Collection;
use Homc\ShopifyOrderParser\ItemCollection;
use Homc\ShopifyOrderParser\OrderCollection;
use Homc\ShopifyOrderParser\Helpers\JsonStreamer;
use Homc\ShopifyOrderParser\Exceptions\InvalidJsonException;

class ShopifyOrderParser extends Collection
{
    /**
     * Constructor
     *
     * @param string $json
     */
    public function __construct(string $json = '{}')
    {
        /**
         * For extremely large JSON data, use a
         * JSON stream to create an array.
         */
        if ($this->jsonExhaustsMemory($json)) {
            $data = JsonStreamer::create($json);
        } else {
            if (!$this->isJson($json)) {
                throw new InvalidJsonException();
            }

            $data = json_decode($json, true);
        }

        parent::__construct($data);
    }

    /**
     * JSON Exhausts Memory
     *
     * @param  string $json
     * @return bool
     */
    public function jsonExhaustsMemory(string $json) : bool
    {
        $memoryLimit = (intval(ini_get('memory_limit')) * 1024 * 1024);

        return strlen($json) * 10 > $memoryLimit;
    }

    /**
     * Is Json
     *
     * @param  string  $data
     * @return boolean
     */
    public function isJson(string $data = '{}') : bool
    {
        json_decode($data);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Get Total Number of Orders
     *
     * @return int
     */
    public function getNumberOfOrders()
    {
        return count($this->orders);
    }

    /**
     * Get Orders As Collection
     *
     * @return array[OrderCollection]
     */
    public function getOrdersAsCollection()
    {
        return array_map(function ($order) {
            return new OrderCollection($order);
        }, $this->orders->toArray());
    }

    /**
     * Get a Single Order Using Key/Value
     *
     * @param  string $key
     * @param  mixed $value
     * @return OrderCollection|null
     */
    public function getOrderByKeyValue(string $key, $value)
    {
        $order = $this->getResourceByKeyValue($this->orders->toArray(), $key, $value);

        return $order ? new OrderCollection($order) : null;
    }

    /**
     * Get Multiple Orders Using Key/Value
     *
     * @param  string $key
     * @param  mixed $value
     * @return ShopifyOrderParser
     */
    public function getOrdersByKeyValue(string $key, $value)
    {
        $result['orders'] = $this->getResourcesByKeyValue($this->orders->toArray(), $key, $value);

        return new ShopifyOrderParser(json_encode($result));
    }

    /**
     * Get Order By Id
     *
     * @param  int    $id
     * @return OrderCollection
     */
    public function getOrderById(int $id)
    {
        return $this->getOrderByKeyValue('id', $id);
    }

    /**
     * Get Orders By Email
     *
     * @param  string $email
     * @return ShopifyOrderParser
     */
    public function getOrdersByEmail(string $email)
    {
        return $this->getOrdersByKeyValue('email', $email);
    }

    /**
     * Get Orders Containing a Single Item
     *
     * @param  int $id
     * @param  array $exclude
     * @return ShopifyOrderParser
     */
    public function getOrdersContainingItem(int $id, array $exclude = [])
    {
        $add = true;
        $result['orders'] = [];

        foreach ($this->orders as $order) {
            foreach ($order['line_items'] as $item) {
                if (in_array($item['product_id'], $exclude)) {
                    $add = false;
                }

                if ($item['product_id'] === $id && $add) {
                    $result['orders'][] = $order;
                }
            }
        }

        return new ShopifyOrderParser(json_encode($result));
    }

    /**
     * Get Orders Containing a Multiple Items
     *
     * @param  array[int] $itemsIds
     * @return ShopifyOrderParser
     */
    public function getOrdersContainingItems(array $itemIds)
    {
        $result['orders'] = [];
        $numberOfItems = count($itemIds);

        /**
         * Loop through each order and their items. If
         * a order contains an id we're searcing for
         * we increase $found. After looping each item
         * we determine if the number found in that order
         * matches the number we're looking for and, if so,
         * we add it the the returned result.
         */
        foreach ($this->orders as $order) {
            /**
             * Keep track of ids because the same id can be
             * present with different variations.
             */
            $foundItemIds = [];
            // Set to zero for each iteration.
            $found = 0;

            foreach ($order['line_items'] as $item) {
                if (in_array($item['product_id'], $itemIds)) {
                    if (!in_array($item['product_id'], $foundItemIds)) {
                        $foundItemIds[] = $item['product_id'];
                        $found++;
                    }
                }
            }

            if ($found === $numberOfItems) {
                $result['orders'][] = $order;
            }
        }

        return new ShopifyOrderParser(json_encode($result));
    }

    /**
     * Get Total Price of All Orders
     *
     * @return int $total
     */
    public function getTotalPrice()
    {
        $total = 0;

        foreach ($this->orders as $order) {
            $total += $order['total_price'];
        }

        return $total;
    }

    /**
     * Get Items With Totals
     *
     * [itemId => total]
     *
     * @return array[int]
     */
    public function getItemsWithTotals() : array
    {
        $itemAndTotal = [];

        foreach ($this->getOrdersAsCollection() as $order) {
            foreach ($order->getItems() as $item) {
                $id = $item->product_id;
                $price = $item->price;

                if (array_key_exists($id, $itemAndTotal)) {
                    $itemAndTotal[$id] += $price;
                } else {
                    $itemAndTotal[$id] = $price;
                }
            }
        }

        return $itemAndTotal;
    }

    /**
     * Get Best Selling Items
     *
     * @return array[int]
     */
    public function getBestSellingItems(int $limit = null) : array
    {
        $sorted = $this->getItemsWithTotals();

        // Sort by total, highest to lowest.
        uasort($sorted, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }

            return ($a < $b) ? 1 : -1;
        });

        return $limit ? array_slice($sorted, 0, $limit, true) : $sorted;
    }

    /**
     * Get Item By Id
     *
     * @param  int            $id
     * @return ItemCollection|null
     */
    public function getItemById(int $id)
    {
        $order = $this->getOrdersContainingItem($id)->first();

        if ($order) {
            foreach ($order[0]['line_items'] as $product) {
                if ($product['product_id'] === $id) {
                    return new ItemCollection($product);
                }
            }
        }

        return null;
    }

    /**
     * Get Total Units Sold
     *
     * @param  int $itemId
     * @return int
     */
    public function getTotalUnitsSold(int $itemId) : int
    {
        $sold = 0;

        foreach ($this->getOrdersAsCollection() as $order) {
            foreach ($order->getItems() as $item) {
                if ($item->product_id === $itemId) {
                    $sold += $item->quantity;
                }
            }
        }

        return $sold;
    }
}
