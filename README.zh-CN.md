# product-ranking-bundle

[![PHP Version Require](https://poser.pugx.org/tourze/product-ranking-bundle/require/php)]
(https://packagist.org/packages/tourze/product-ranking-bundle)  
[![License](https://poser.pugx.org/tourze/product-ranking-bundle/license)]
(https://packagist.org/packages/tourze/product-ranking-bundle)  
[![Build Status](https://github.com/tourze/php-monorepo/workflows/CI/badge.svg)]
(https://github.com/tourze/php-monorepo/actions)  
[![Coverage Status](https://coveralls.io/repos/github/tourze/php-monorepo/badge.svg?branch=master)]
(https://coveralls.io/github/tourze/php-monorepo?branch=master)

[English](README.md) | [中文](README.zh-CN.md)

一个用于管理产品排行榜的 Symfony Bundle，支持自动计算和灵活定位。

## 目录

- [功能特性](#功能特性)
- [安装](#安装)
- [快速开始](#快速开始)
- [配置](#配置)
- [基本用法](#基本用法)
- [高级用法](#高级用法)
- [实体](#实体)
- [命令](#命令)
- [API 过程](#api-过程)
- [管理界面](#管理界面)
- [依赖](#依赖)
- [许可证](#许可证)

## 功能特性

- **动态排行榜**：创建多个带有自定义计算 SQL 的排行榜
- **位置管理**：将排行榜分配到不同的显示位置
- **固定排名**：支持手动固定产品位置
- **自动更新**：定时命令刷新排行榜
- **管理界面**：EasyAdmin CRUD 控制器用于管理
- **JSON-RPC API**：通过 API 访问排行榜数据

## 安装

```bash
composer require tourze/product-ranking-bundle
```

## 快速开始

1. 安装 Bundle：
   ```bash
   composer require tourze/product-ranking-bundle
   ```

2. 创建排行榜：
   ```php
   $rankingList = new RankingList();
   $rankingList->setTitle('热门产品');
   $rankingList->setValid(true);
   $entityManager->persist($rankingList);
   ```

3. 运行排行计算：
   ```bash
   php bin/console product:ranking-list:calc
   ```

4. 通过 API 访问：
   ```php
   $lists = $jsonRpc->call('GetProductRankingLists');
   ```

## 配置

在 Symfony 应用中注册 Bundle：

```php
// config/bundles.php
return [
    // ...
    ProductRankingBundle\ProductRankingBundle::class => ['all' => true],
];
```

## 基本用法

### 创建排行榜

```php
use ProductRankingBundle\Entity\RankingList;

$rankingList = new RankingList();
$rankingList->setTitle('热销产品');
$rankingList->setSubtitle('本月最畅销');
$rankingList->setValid(true);
$rankingList->setCount(10); // 前10名产品
$rankingList->setScoreSql('
    SELECT 
        p.id,
        COUNT(o.id) as score,
        "销量高" as reason
    FROM product_spu p
    JOIN order_item oi ON oi.spu_id = p.id
    JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY p.id
    ORDER BY score DESC
');
```

### 通过 API 访问排行榜

```php
// 获取所有排行榜
$lists = $jsonRpc->call('GetProductRankingLists');

// 获取首页位置的排行榜
$homepageLists = $jsonRpc->call('GetProductRankingLists', [
    'position' => 'homepage'
]);

// 检查产品是否在排行榜中
$rankings = $jsonRpc->call('GetRankingListBySpu', [
    'spuId' => '123456'
]);
```

## 高级用法

### 自定义 SQL 排名算法

您可以使用 SQL 创建复杂的排名算法：

```sql
-- 销售表现排名
SELECT 
    p.id,
    (COUNT(o.id) * 0.7 + AVG(r.rating) * 0.3) as score,
    CONCAT('销量: ', COUNT(o.id), ', 平均评分: ', ROUND(AVG(r.rating), 2)) as reason
FROM product_spu p
LEFT JOIN order_item oi ON oi.spu_id = p.id
LEFT JOIN orders o ON o.id = oi.order_id AND o.status = 'completed'
LEFT JOIN product_review r ON r.spu_id = p.id
WHERE p.valid = 1
GROUP BY p.id
HAVING COUNT(o.id) >= 5
ORDER BY score DESC
```

### 管理固定位置

手动设置特定产品的固定位置：

```php
use ProductRankingBundle\Entity\RankingItem;

$item = new RankingItem();
$item->setList($rankingList);
$item->setSpuId('special-product-id');
$item->setNumber(1); // 固定在第1位
$item->setFixed(true);
$item->setScore(999);
$item->setTextReason('特色产品');
```

### 调度更新

排名计算会自动调度，但也可以手动触发：

```bash
# 手动更新
php bin/console product:ranking-list:calc

# 添加到 crontab 进行自定义调度
*/15 * * * * php /path/to/project/bin/console product:ranking-list:calc
```

## 性能优化

对于大数据集，请考虑：

1. **索引优化**：确保在 SQL 中使用的字段上有适当的数据库索引
2. **批处理**：对大结果集使用查询构建器
3. **缓存**：为频繁访问的排行榜实现缓存

```php
// 示例：带分页的优化查询
$items = $this->itemRepository->createQueryBuilder('i')
    ->where('i.list = :list')
    ->setParameter('list', $list)
    ->setMaxResults(100)
    ->getQuery()
    ->toIterable();
```

## 实体

### RankingList（排行榜）
表示一个产品排行榜，包含标题、副标题、logo 和计算 SQL。

### RankingItem（排行榜项）
排行榜中的单个项目，包括产品 SPU、分数和位置。

### RankingPosition（排行榜位置）
可以显示排行榜的位置（如首页、分类页）。

## 命令

### product:ranking-list:calc

根据 SQL 公式计算并更新所有活跃的排行榜。

```bash
php bin/console product:ranking-list:calc
```

此命令：
- 通过 cron 每 30 分钟运行一次（使用 `@AsCronTask` 配置）
- 处理所有活跃的排行榜
- 执行自定义 SQL 计算产品分数
- 更新排名位置同时保留固定项
- 删除超出列表数量限制的项目

## API 过程

### GetProductRankingLists
获取所有活跃的排行榜，可按位置过滤。

### GetRankingListBySpu
获取特定产品 SPU 的排行信息。

## 管理界面

该 Bundle 提供了 EasyAdmin CRUD 控制器用于：
- 管理排行榜
- 查看和编辑排行榜项
- 配置显示位置

## 依赖

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

## 许可证

本 Bundle 基于 MIT 许可证发布。
