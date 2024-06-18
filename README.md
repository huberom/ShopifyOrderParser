# Shopify Order Parser

Tool to parse shopify orders in json format, it is not a production ready tool.

## Installation

Add following to your composer.json file

```
"require": {
    "huberom/shopifyorderparser": "dev-master"
}

"repositories": [
    {
        "type": "vcs",
        "url": "git@github.com:huberom/ShopifyOrderParser.git"
    }
]
```

Next update composer:

```
composer update
```

## How to Use

Basic Example

```php
use Homc\ShopifyOrderParser\ShopifyOrderParser;

$data = Shopify::get('/admin/orders.json');
$parser = new ShopifyOrderParser($data);

// Get orders containing a specific item
$parser->getOrdersContainingItem(123456789);

// Return a specific order by id
$parser->getOrderById(987654321);

// Get the total price of all loaed orders
$parser->getTotalPrice();
});
```

Advanced Usage

```php
/**
 * getOrdersContainingItem moethod returns
 * a new instance of BatchOrderParser. That allows you
 * to keeps using class methods.
 *
 * For instance, if you want to get all
 * orders with a specific item and get the total
 * dollar amount of those orders.
 */
$orders = $parser->getOrdersContainingItem(123456789);
$orders->getTotalPrice();

// Or get the total number of items.
$orders->getNumberOfOrders();

/**
 * Since we're working with collections, we can leverage
 * methods like map to simplify our tasks. For instance,
 * let's create an array that extracts the id from each order
 */
$ids = $orders->map(function($order) {
    return $order['id'];
});
```
