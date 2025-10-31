<?php

namespace ProductRankingBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Repository\RankingListRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(RankingListRepository::class)]
#[RunTestsInSeparateProcesses]
final class RankingListRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 不清理数据，让 DataFixtures 的数据保留，以便 testCountWithDataFixtureShouldReturnGreaterThanZero 测试通过
    }

    protected function onTearDown(): void
    {
        $this->cleanupTestData();
    }

    protected function createNewEntity(): object
    {
        $entity = new RankingList();
        $entity->setTitle('Test List ' . uniqid());
        $entity->setColor('#FF0000');

        return $entity;
    }

    protected function getRepository(): RankingListRepository
    {
        $repository = self::getService(RankingListRepository::class);
        self::assertInstanceOf(RankingListRepository::class, $repository);

        return $repository;
    }

    public function testSaveWithNewEntityShouldPersist(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Saved List');
        $entity->setColor('#00FFFF');

        $this->getRepository()->save($entity);

        $this->assertNotNull($entity->getId());
        $found = $this->getRepository()->find($entity->getId());
        $this->assertInstanceOf(RankingList::class, $found);
        $this->assertEquals('Saved List', $found->getTitle());
    }

    public function testRemoveWithExistingEntityShouldDelete(): void
    {
        $entity = new RankingList();
        $entity->setTitle('To Delete');
        $entity->setColor('#FF00FF');
        $this->getRepository()->save($entity);
        $id = $entity->getId();

        $this->getRepository()->remove($entity);

        $found = $this->getRepository()->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $entity1 = new RankingList();
        $entity1->setTitle('Z List');
        $entity1->setColor('#000000');
        $entity2 = new RankingList();
        $entity2->setTitle('A List');
        $entity2->setColor('#000000');
        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy([], ['title' => 'ASC']);

        $this->assertInstanceOf(RankingList::class, $result);
        $this->assertEquals('A List', $result->getTitle());
    }

    public function testClear(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Clear Test');
        $entity->setColor('#FF0000');
        $this->getRepository()->save($entity);

        $this->getRepository()->clear();

        $this->assertFalse(self::getEntityManager()->contains($entity));
    }

    public function testFlush(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Flush Test');
        $entity->setColor('#FF0000');
        self::getEntityManager()->persist($entity);

        $this->getRepository()->flush();

        $this->assertNotNull($entity->getId());
    }

    public function testSaveAll(): void
    {
        $initialCount = $this->getRepository()->count([]);

        $entity1 = new RankingList();
        $entity1->setTitle('Batch Save 1');
        $entity1->setColor('#FF0000');
        $entity2 = new RankingList();
        $entity2->setTitle('Batch Save 2');
        $entity2->setColor('#00FF00');

        $this->getRepository()->saveAll([$entity1, $entity2]);

        $this->assertNotNull($entity1->getId());
        $this->assertNotNull($entity2->getId());
        $this->assertEquals($initialCount + 2, $this->getRepository()->count([]));
    }

    public function testNullableFieldIsNullQuery(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Null Test');
        $entity->setColor('#FF0000');
        $entity->setSubtitle(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['subtitle' => null]);

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->getSubtitle());
    }

    public function testNullableFieldIsNullCountQuery(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Null Count Test');
        $entity->setColor('#FF0000');
        $entity->setLogoUrl(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['logoUrl' => null]);

        $this->assertEquals(1, $count);
    }

    private function cleanupTestData(): void
    {
        try {
            self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingList')->execute();
            self::getEntityManager()->flush();
        } catch (\Exception $e) {
            // Table might not exist in some test scenarios, ignore the error
        }
    }

    public function testFindOneByWithSortingLogic(): void
    {
        $entity1 = new RankingList();
        $entity1->setTitle('排行榜A');
        $entity1->setCount(1);

        $entity2 = new RankingList();
        $entity2->setTitle('排行榜B');
        $entity2->setCount(2);

        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy([], ['count' => 'ASC']);

        $this->assertInstanceOf(RankingList::class, $result);
        $this->assertEquals('排行榜A', $result->getTitle());
    }

    public function testIsNullQueryForDescription(): void
    {
        $entity = new RankingList();
        $entity->setTitle('测试排行榜');
        $entity->setSubtitle(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['subtitle' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testIsNullQueryForSort(): void
    {
        $entity = new RankingList();
        $entity->setTitle('测试排行榜2');
        $entity->setCount(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['count' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testIsNullCountQuery(): void
    {
        $entity = new RankingList();
        $entity->setTitle('测试排行榜3');
        $entity->setSubtitle(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['subtitle' => null]);

        $this->assertGreaterThan(0, $count);
    }

    public function testNullableFieldIsNullQueryWithLogoUrl(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Logo Url Null Test');
        $entity->setColor('#FF0000');
        $entity->setLogoUrl(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['logoUrl' => null]);

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->getLogoUrl());
    }

    public function testNullableFieldIsNullCountQueryWithCount(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Count Null Test');
        $entity->setColor('#FF0000');
        $entity->setCount(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['count' => null]);

        $this->assertEquals(1, $count);
    }

    public function testNullableFieldIsNullCountQueryWithScoreSql(): void
    {
        $entity = new RankingList();
        $entity->setTitle('Score Sql Null Test');
        $entity->setColor('#FF0000');
        $entity->setScoreSql(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['scoreSql' => null]);

        $this->assertEquals(1, $count);
    }
}
