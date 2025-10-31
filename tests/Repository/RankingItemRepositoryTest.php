<?php

namespace ProductRankingBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Repository\RankingItemRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(RankingItemRepository::class)]
#[RunTestsInSeparateProcesses]
final class RankingItemRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
    }

    protected function createNewEntity(): object
    {
        $list = new RankingList();
        $list->setTitle('Test List ' . uniqid());
        $list->setColor('#FF0000');
        self::getEntityManager()->persist($list);

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('test-spu-' . uniqid());
        $entity->setList($list);

        return $entity;
    }

    protected function getRepository(): RankingItemRepository
    {
        $repository = self::getService(RankingItemRepository::class);
        self::assertInstanceOf(RankingItemRepository::class, $repository);

        return $repository;
    }

    private function cleanupTestData(): void
    {
        try {
            self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingItem')->execute();
            self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingList')->execute();
            self::getEntityManager()->flush();
        } catch (\Exception $e) {
            // Table might not exist in some test scenarios, ignore the error
        }
    }

    public function testSaveWithNewEntityShouldPersist(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(15);
        $entity->setSpuId('789');
        $entity->setList($list);

        $this->getRepository()->save($entity);

        $this->assertNotNull($entity->getId());
        $found = $this->getRepository()->find($entity->getId());
        $this->assertInstanceOf(RankingItem::class, $found);
        $this->assertEquals(15, $found->getNumber());
    }

    public function testRemoveWithExistingEntityShouldDelete(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $this->getRepository()->save($entity);
        $id = $entity->getId();

        $this->getRepository()->remove($entity);

        $found = $this->getRepository()->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity1 = new RankingItem();
        $entity1->setNumber(2);
        $entity1->setSpuId('100');
        $entity1->setList($list);
        $entity2 = new RankingItem();
        $entity2->setNumber(1);
        $entity2->setSpuId('101');
        $entity2->setList($list);
        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy([], ['number' => 'ASC']);

        $this->assertInstanceOf(RankingItem::class, $result);
        $this->assertEquals(1, $result->getNumber());
    }

    public function testClear(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $this->getRepository()->save($entity);

        $this->getRepository()->clear();

        $this->assertFalse(self::getEntityManager()->contains($entity));
    }

    public function testFlush(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        self::getEntityManager()->persist($entity);

        $this->getRepository()->flush();

        $this->assertNotNull($entity->getId());
    }

    public function testSaveAll(): void
    {
        // Clean up any existing data
        $this->cleanupTestData();

        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity1 = new RankingItem();
        $entity1->setNumber(1);
        $entity1->setSpuId('100');
        $entity1->setList($list);
        $entity2 = new RankingItem();
        $entity2->setNumber(2);
        $entity2->setSpuId('101');
        $entity2->setList($list);

        $this->getRepository()->saveAll([$entity1, $entity2]);

        $this->assertNotNull($entity1->getId());
        $this->assertNotNull($entity2->getId());
        $this->assertEquals(2, $this->getRepository()->count([]));
    }

    public function testFindOneByWithSortingLogic(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity1 = new RankingItem();
        $entity1->setNumber(1);
        $entity1->setSpuId('100');
        $entity1->setList($list);
        $entity1->setScore(95);

        $entity2 = new RankingItem();
        $entity2->setNumber(2);
        $entity2->setSpuId('101');
        $entity2->setList($list);
        $entity2->setScore(100);

        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy(['list' => $list], ['score' => 'DESC']);

        $this->assertInstanceOf(RankingItem::class, $result);
        $this->assertEquals('101', $result->getSpuId());
    }

    public function testAssociationQuery(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['list' => $list]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $resultList = $result->getList();
            $this->assertNotNull($resultList);
            $this->assertEquals($list->getId(), $resultList->getId());
        }
    }

    public function testAssociationCountQuery(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['list' => $list]);

        $this->assertGreaterThan(0, $count);
    }

    public function testIsNullQuery(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(99);
        $entity->setSpuId('999');
        $entity->setList($list);
        $entity->setTextReason(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['textReason' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testIsNullCountQuery(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(98);
        $entity->setSpuId('998');
        $entity->setList($list);
        $entity->setRecommendReason(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['recommendReason' => null]);

        $this->assertGreaterThan(0, $count);
    }

    public function testAssociationQueryWithList(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(99);
        $entity->setSpuId('999');
        $entity->setList($list);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['list' => $list]);

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $resultList = $results[0]->getList();
        $this->assertNotNull($resultList);
        $this->assertEquals($list->getId(), $resultList->getId());
    }

    public function testIsNullQueryWithScore(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(101);
        $entity->setSpuId('1001');
        $entity->setList($list);
        // score is nullable by default, no need to set it to null
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['score' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertNull($results[0]->getScore());
    }

    public function testIsNullQueryWithTextReason(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(102);
        $entity->setSpuId('1002');
        $entity->setList($list);
        $entity->setTextReason(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['textReason' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        $this->assertNull($results[0]->getTextReason());
    }

    public function testIsNullCountQueryWithRecommendThumb(): void
    {
        // Clean up any existing data
        $this->cleanupTestData();

        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(103);
        $entity->setSpuId('1003');
        $entity->setList($list);
        $entity->setRecommendThumb(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['recommendThumb' => null]);

        $this->assertEquals(1, $count);
    }

    public function testFindOneBySortingWithMultipleCriteria(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity1 = new RankingItem();
        $entity1->setNumber(1);
        $entity1->setSpuId('100');
        $entity1->setList($list);
        $entity1->setScore(90);

        $entity2 = new RankingItem();
        $entity2->setNumber(2);
        $entity2->setSpuId('101');
        $entity2->setList($list);
        $entity2->setScore(90);

        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy(['list' => $list, 'score' => 90], ['number' => 'ASC']);

        $this->assertInstanceOf(RankingItem::class, $result);
        $this->assertEquals(1, $result->getNumber());
    }

    public function testAssociationQueryWithValidField(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $entity->setValid(true);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['list' => $list, 'valid' => true]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertTrue($result->isValid());
        }
    }

    public function testAssociationQueryWithFixedField(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $entity->setFixed(true);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['list' => $list, 'fixed' => true]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertTrue($result->isFixed());
        }
    }

    public function testAssociationCountWithMultipleFields(): void
    {
        $list = new RankingList();
        $list->setTitle('Test List');
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $entity = new RankingItem();
        $entity->setNumber(1);
        $entity->setSpuId('100');
        $entity->setList($list);
        $entity->setValid(true);
        $entity->setFixed(true);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['list' => $list, 'valid' => true, 'fixed' => true]);

        $this->assertGreaterThan(0, $count);
    }
}
