<?php

declare(strict_types=1);

namespace ProductRankingBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Controller\Admin\ProductRankingRankingItemCrudController;
use ProductRankingBundle\Entity\RankingItem;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ProductRankingRankingItemCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ProductRankingRankingItemCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<RankingItem>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(ProductRankingRankingItemCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'valid' => ['是否有效'];
        yield 'list' => ['所属排行榜'];
        yield 'number' => ['排名'];
        yield 'spuId' => ['SPU ID'];
        yield 'score' => ['分数'];
        yield 'fixed' => ['固定排名'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'number' => ['number'];
        yield 'spuId' => ['spuId'];
        yield 'score' => ['score'];
        yield 'fixed' => ['fixed'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(RankingItem::class, ProductRankingRankingItemCrudController::getEntityFqcn());
    }
}
