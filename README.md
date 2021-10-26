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
use Homc\ShopifyOrderParser\BatchOrderParser;

$data = Shopify::get('/admin/orders.json');
$parser = new BatchOrderParser($data);

// Get orders containing a specific item
$parser->getOrdersContainingItem(123456789);

// Return a specific order by id
$parser->getOrderById(987654321);
```

More documentation comming soon
