<?php

declare(strict_types=1);

namespace ProductRankingBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\ProductRankingBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(ProductRankingBundle::class)]
#[RunTestsInSeparateProcesses]
final class ProductRankingBundleTest extends AbstractBundleTestCase
{
}
