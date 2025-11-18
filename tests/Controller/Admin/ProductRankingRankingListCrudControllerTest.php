<?php

declare(strict_types=1);

namespace ProductRankingBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Controller\Admin\ProductRankingRankingListCrudController;
use ProductRankingBundle\Entity\RankingList;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ProductRankingRankingListCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ProductRankingRankingListCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AbstractCrudController<RankingList>
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(ProductRankingRankingListCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'id' => ['ID'];
        yield 'valid' => ['是否有效'];
        yield 'title' => ['标题'];
        yield 'subtitle' => ['副标题'];
        yield 'color' => ['颜色'];
        yield 'count' => ['数量'];
        yield 'position' => ['展示位置'];
        yield 'createTime' => ['创建时间'];
        yield 'updateTime' => ['更新时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'valid' => ['valid'];
        yield 'title' => ['title'];
        yield 'subtitle' => ['subtitle'];
        yield 'color' => ['color'];
        yield 'count' => ['count'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        return self::provideNewPageFields();
    }
}
