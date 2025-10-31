<?php

declare(strict_types=1);

namespace ProductRankingBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Controller\Admin\ProductRankingRankingPositionCrudController;
use ProductRankingBundle\Entity\RankingPosition;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ProductRankingRankingPositionCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ProductRankingRankingPositionCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<RankingPosition>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(ProductRankingRankingPositionCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'valid' => ['是否有效'];
        yield 'title' => ['位置名称'];
        yield 'list' => ['关联排行榜'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'title' => ['title'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(RankingPosition::class, ProductRankingRankingPositionCrudController::getEntityFqcn());
    }
}
