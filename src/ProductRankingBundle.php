<?php

namespace ProductRankingBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\ProductCoreBundle\ProductCoreBundle;
use Tourze\Symfony\CronJob\CronJobBundle;

class ProductRankingBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            CronJobBundle::class => ['all' => true],
            // \AntdCpBundle\AntdCpBundle::class => ['all' => true], // TODO: AntdCpBundle 不存在于 monorepo 中
            ProductCoreBundle::class => ['all' => true],
            SecurityBundle::class => ['all' => true],
        ];
    }
}
