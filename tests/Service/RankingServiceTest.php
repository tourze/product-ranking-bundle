<?php

namespace ProductRankingBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Repository\RankingItemRepository;
use ProductRankingBundle\Service\RankingService;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(RankingService::class)]
#[RunTestsInSeparateProcesses]
final class RankingServiceTest extends AbstractIntegrationTestCase
{
    private RankingService $rankingService;

    private RankingItemRepository $itemRepository;

    protected function onSetUp(): void
    {
        $this->rankingService = self::getService(RankingService::class);
        $this->itemRepository = self::getService(RankingItemRepository::class);
    }

    public function testRankingServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(RankingService::class, $this->rankingService);
    }

    public function testUpdateRankingItemsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(RankingService::class, 'updateRankingItems');
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame('void', $returnType->getName());
        $this->assertCount(1, $reflection->getParameters());
    }

    public function testUpdateRankingItemsWithEmptyScoreSql(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setValid(true);
        $list->setScoreSql('');

        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $initialItemCount = $this->itemRepository->count(['list' => $list]);

        $this->rankingService->updateRankingItems($list);

        $finalItemCount = $this->itemRepository->count(['list' => $list]);
        $this->assertSame($initialItemCount, $finalItemCount);
    }

    public function testUpdateRankingItemsWithNullScoreSql(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setValid(true);
        $list->setScoreSql(null);

        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $initialItemCount = $this->itemRepository->count(['list' => $list]);

        $this->rankingService->updateRankingItems($list);

        $finalItemCount = $this->itemRepository->count(['list' => $list]);
        $this->assertSame($initialItemCount, $finalItemCount);
    }

    public function testUpdateRankingItemsRemovesNonFixedItems(): void
    {
        // Create list with simple SQL that returns one record
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setValid(true);
        $list->setScoreSql('SELECT "test-spu-new" as id, 100 as score, "test reason" as text_reason');
        $list->setCount(10);

        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        // Add items directly to the list's items collection and persist them
        $nonFixedItem = new RankingItem();
        $nonFixedItem->setList($list);
        $nonFixedItem->setSpuId('test-spu-old');
        $nonFixedItem->setNumber(1);
        $nonFixedItem->setFixed(false);
        $nonFixedItem->setValid(true);
        self::getEntityManager()->persist($nonFixedItem);

        $fixedItem = new RankingItem();
        $fixedItem->setList($list);
        $fixedItem->setSpuId('test-spu-fixed');
        $fixedItem->setNumber(2);
        $fixedItem->setFixed(true);
        $fixedItem->setValid(true);
        self::getEntityManager()->persist($fixedItem);

        self::getEntityManager()->flush();

        // Refresh the list to ensure the items collection is loaded
        self::getEntityManager()->refresh($list);

        // Verify items exist before calling the service
        $this->assertCount(2, $this->itemRepository->findBy(['list' => $list]));

        $this->rankingService->updateRankingItems($list);

        // Verify the behavior: non-fixed items removed, fixed items preserved
        $allItems = $this->itemRepository->findBy(['list' => $list]);

        // Should have the fixed item plus potentially new items from SQL
        $fixedItems = array_filter($allItems, fn (RankingItem $item): bool => (bool) $item->isFixed());
        $this->assertCount(1, $fixedItems);

        // The fixed item should still exist
        $remainingFixedItem = $this->itemRepository->findOneBy(['spuId' => 'test-spu-fixed', 'fixed' => true]);
        $this->assertNotNull($remainingFixedItem);

        // The non-fixed item should be gone
        $removedItem = $this->itemRepository->findOneBy(['spuId' => 'test-spu-old']);
        $this->assertNull($removedItem);
    }

    public function testUpdateRankingItemsWithLimitedCount(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setValid(true);
        $list->setScoreSql('SELECT "test-spu-1" as id, 100 as score, "reason1" as text_reason UNION SELECT "test-spu-2" as id, 90 as score, "reason2" as text_reason UNION SELECT "test-spu-3" as id, 80 as score, "reason3" as text_reason');
        $list->setCount(2); // Limit to 2 items

        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $this->rankingService->updateRankingItems($list);

        $items = $this->itemRepository->findBy(['list' => $list], ['number' => 'ASC']);
        $this->assertCount(2, $items);

        $this->assertSame(1, $items[0]->getNumber());
        $this->assertSame(2, $items[1]->getNumber());
    }

    public function testUpdateRankingItemsCreatesNewItems(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setValid(true);
        $list->setScoreSql('SELECT "test-spu-new" as id, 95 as score, "new reason" as text_reason');
        $list->setCount(5);

        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $initialCount = $this->itemRepository->count(['list' => $list]);

        $this->rankingService->updateRankingItems($list);

        $finalCount = $this->itemRepository->count(['list' => $list]);
        $this->assertGreaterThan($initialCount, $finalCount);

        $newItem = $this->itemRepository->findOneBy(['spuId' => 'test-spu-new']);
        $this->assertNotNull($newItem);
        $this->assertSame(95, $newItem->getScore());
        $this->assertSame('new reason', $newItem->getTextReason());
        $this->assertFalse($newItem->isFixed());
        $this->assertTrue($newItem->isValid());
    }
}
