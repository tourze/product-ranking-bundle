# Product Ranking Bundle

[![PHP Version Require](https://poser.pugx.org/tourze/product-ranking-bundle/require/php)]
(https://packagist.org/packages/tourze/product-ranking-bundle)  
[![License](https://poser.pugx.org/tourze/product-ranking-bundle/license)]
(https://packagist.org/packages/tourze/product-ranking-bundle)  
[![Build Status](https://github.com/tourze/php-monorepo/workflows/CI/badge.svg)]
(https://github.com/tourze/php-monorepo/actions)  
[![Coverage Status](https://coveralls.io/repos/github/tourze/php-monorepo/badge.svg?branch=master)]
(https://coveralls.io/github/tourze/php-monorepo?branch=master)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony bundle for managing product rankings with automatic calculation and flexible positioning.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Advanced Usage](#advanced-usage)
- [Entities](#entities)
- [Commands](#commands)
- [API Procedures](#api-procedures)
- [Admin Interface](#admin-interface)
- [Dependencies](#dependencies)
- [License](#license)

## Features

- **Dynamic Rankings**: Create multiple ranking lists with custom calculation SQL
- **Position Management**: Assign rankings to different display positions
- **Fixed Rankings**: Support for manually fixed product positions
- **Automatic Updates**: Scheduled command to refresh rankings
- **Admin Interface**: EasyAdmin CRUD controllers for management
- **JSON-RPC APIs**: Access ranking data via API procedures

## Installation

```bash
composer require tourze/product-ranking-bundle
```

## Quick Start

1. Install the bundle:
   ```bash
   composer require tourze/product-ranking-bundle
   ```

2. Create a ranking list:
   ```php
   $rankingList = new RankingList();
   $rankingList->setTitle('Top Products');
   $rankingList->setValid(true);
   $entityManager->persist($rankingList);
   ```

3. Run ranking calculation:
   ```bash
   php bin/console product:ranking-list:calc
   ```

4. Access via API:
   ```php
   $lists = $jsonRpc->call('GetProductRankingLists');
   ```

## Configuration

Register the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ...
    ProductRankingBundle\ProductRankingBundle::class => ['all' => true],
];
```

## Basic Usage

### Creating a Ranking List

```php
use ProductRankingBundle\Entity\RankingList;

$rankingList = new RankingList();
$rankingList->setTitle('Top Selling Products');
$rankingList->setSubtitle('Best sellers this month');
$rankingList->setValid(true);
$rankingList->setCount(10); // Top 10 products
$rankingList->setScoreSql('
    SELECT 
        p.id,
        COUNT(o.id) as score,
        "High sales volume" as reason
    FROM product_spu p
    JOIN order_item oi ON oi.spu_id = p.id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY score DESC
');
```

### Accessing Rankings via API

```php
// Get all ranking lists
$lists = $jsonRpc->call('GetProductRankingLists');

// Get rankings for homepage position
$homepageLists = $jsonRpc->call('GetProductRankingLists', [
    'position' => 'homepage'
]);

// Check if a product is ranked
$rankings = $jsonRpc->call('GetRankingListBySpu', [
    'spuId' => '123456'
]);
```

## Advanced Usage

### Custom SQL Ranking Algorithms

You can create sophisticated ranking algorithms using SQL:

```sql
-- Sales performance ranking
SELECT 
    p.id,
    (COUNT(o.id) * 0.7 + AVG(r.rating) * 0.3) as score,
    CONCAT('Sales: ', COUNT(o.id), ', Avg Rating: ', ROUND(AVG(r.rating), 2)) as reason
FROM product_spu p
LEFT JOIN order_item oi ON oi.spu_id = p.id
LEFT JOIN orders o ON o.id = oi.order_id AND o.status = 'completed'
LEFT JOIN product_review r ON r.spu_id = p.id
WHERE p.valid = 1
GROUP BY p.id
HAVING COUNT(o.id) >= 5
ORDER BY score DESC
```

### Managing Fixed Positions

Manually set fixed positions for specific products:

```php
use ProductRankingBundle\Entity\RankingItem;

$item = new RankingItem();
$item->setList($rankingList);
$item->setSpuId('special-product-id');
$item->setNumber(1); // Fixed at position 1
$item->setFixed(true);
$item->setScore(999);
$item->setTextReason('Featured product');
```

### Scheduling Updates

The ranking calculation is automatically scheduled but can also be triggered manually:

```bash
# Manual update
php bin/console product:ranking-list:calc

# Add to crontab for custom scheduling
*/15 * * * * php /path/to/project/bin/console product:ranking-list:calc
```

## Performance Optimization

For large datasets, consider:

1. **Index optimization**: Ensure proper database indexes on fields used in your SQL
2. **Batch processing**: Use query builders for large result sets
3. **Caching**: Implement caching for frequently accessed rankings

```php
// Example: Optimized query with pagination
$items = $this->itemRepository->createQueryBuilder('i')
    ->where('i.list = :list')
    ->setParameter('list', $list)
    ->setMaxResults(100)
    ->getQuery()
    ->toIterable();
```

## Entities

### RankingList
Represents a product ranking list with title, subtitle, logo, and calculation SQL.

### RankingItem
Individual items within a ranking list, including product SPU, score, and position.

### RankingPosition
Display positions where ranking lists can be shown (e.g., homepage, category page).

## Commands

### product:ranking-list:calc

Calculates and updates all active ranking lists based on their SQL formulas.

```bash
php bin/console product:ranking-list:calc
```

This command:
- Runs every 30 minutes via cron (configured with `@AsCronTask`)
- Processes all active ranking lists
- Executes custom SQL to calculate product scores
- Updates ranking positions while preserving fixed items
- Removes items exceeding the list count limit

## API Procedures

### GetProductRankingLists
Retrieve all active ranking lists, optionally filtered by position.

### GetRankingListBySpu
Get ranking information for a specific product SPU.

## Admin Interface

The bundle provides EasyAdmin CRUD controllers for:
- Managing ranking lists
- Viewing and editing ranking items
- Configuring display positions

## Dependencies

- Symfony 7.3+
- Doctrine ORM 3.0+
- Doctrine DBAL 4.0+
- tourze/doctrine-snowflake-bundle
- tourze/doctrine-timestamp-bundle
- tourze/doctrine-user-bundle
- tourze/product-core-bundle
- tourze/json-rpc-core
- tourze/json-rpc-cache-bundle
- tourze/symfony-cron-job-bundle
- tourze/easy-admin-extra-bundle

## License

This bundle is licensed under the MIT License.
