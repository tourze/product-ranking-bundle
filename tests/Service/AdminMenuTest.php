<?php

namespace ProductRankingBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Entity\RankingPosition;
use ProductRankingBundle\Service\AdminMenu;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 不需要调用 parent::onSetUp()，因为 AbstractEasyAdminMenuTestCase 继承自 AbstractIntegrationTestCase
    }

    public function testAdminMenuCanBeInstantiatedWithoutLinkGenerator(): void
    {
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testAdminMenuCanBeInstantiatedWithLinkGenerator(): void
    {
        $linkGenerator = self::getService(LinkGeneratorInterface::class);
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testInvokeWithLinkGeneratorAddsMenuItems(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);

        // 设置getCurdListPage的期望调用，使用callBacks验证参数
        $linkGenerator
            ->expects($this->exactly(3))
            ->method('getCurdListPage')
            ->willReturnCallback(function (string $entityClass) {
                return match ($entityClass) {
                    RankingList::class => '/admin/ranking-list',
                    RankingItem::class => '/admin/ranking-item',
                    RankingPosition::class => '/admin/ranking-position',
                    default => self::fail("Unexpected entity class: {$entityClass}"),
                };
            })
        ;

        $parentMenuItem = $this->createMock(ItemInterface::class);
        $childMenuItem1 = $this->createMock(ItemInterface::class);
        $childMenuItem2 = $this->createMock(ItemInterface::class);
        $childMenuItem3 = $this->createMock(ItemInterface::class);
        $rootItem = $this->createMock(ItemInterface::class);

        $rootItem
            ->expects($this->exactly(2))
            ->method('getChild')
            ->with('电商中心')
            ->willReturnOnConsecutiveCalls(null, $parentMenuItem)
        ;

        $rootItem
            ->expects($this->once())
            ->method('addChild')
            ->with('电商中心')
            ->willReturn($parentMenuItem)
        ;

        // 创建返回的菜单项mock，支持链式调用
        $parentMenuItem
            ->expects($this->exactly(3))
            ->method('addChild')
            ->willReturnCallback(function (string $name) use ($childMenuItem1, $childMenuItem2, $childMenuItem3) {
                return match ($name) {
                    '排行榜列表' => $childMenuItem1,
                    '排行榜商品' => $childMenuItem2,
                    '排行榜位置' => $childMenuItem3,
                    default => self::fail("Unexpected name: {$name}"),
                };
            })
        ;

        // 为每个返回的菜单项设置期望，支持链式调用
        $childMenuItem1
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin/ranking-list')
            ->willReturnSelf()
        ;

        $childMenuItem1
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-list-ol')
        ;

        $childMenuItem2
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin/ranking-item')
            ->willReturnSelf()
        ;

        $childMenuItem2
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-trophy')
        ;

        $childMenuItem3
            ->expects($this->once())
            ->method('setUri')
            ->with('/admin/ranking-position')
            ->willReturnSelf()
        ;

        $childMenuItem3
            ->expects($this->once())
            ->method('setAttribute')
            ->with('icon', 'fas fa-map-marker-alt')
        ;

        // 使用反射来创建带有依赖的对象
        $reflection = new \ReflectionClass(AdminMenu::class);
        $adminMenu = $reflection->newInstance($linkGenerator);
        $adminMenu($rootItem);
    }
}
