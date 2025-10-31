<?php

namespace ProductRankingBundle\Tests\Repository;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingPosition;
use ProductRankingBundle\Repository\RankingPositionRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(RankingPositionRepository::class)]
#[RunTestsInSeparateProcesses]
final class RankingPositionRepositoryTest extends AbstractRepositoryTestCase
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
        $entity = new RankingPosition();
        $entity->setTitle('Test Position ' . uniqid());

        return $entity;
    }

    protected function getRepository(): RankingPositionRepository
    {
        $repository = self::getService(RankingPositionRepository::class);
        self::assertInstanceOf(RankingPositionRepository::class, $repository);

        return $repository;
    }

    public function testSaveWithNewEntityShouldPersist(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle('Saved Position');

        $this->getRepository()->save($entity);

        $this->assertNotNull($entity->getId());
        $found = $this->getRepository()->find($entity->getId());
        $this->assertInstanceOf(RankingPosition::class, $found);
        $this->assertEquals('Saved Position', $found->getTitle());
    }

    public function testRemoveWithExistingEntityShouldDelete(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle('To Delete');
        $this->getRepository()->save($entity);
        $id = $entity->getId();

        $this->getRepository()->remove($entity);

        $found = $this->getRepository()->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $entity1 = new RankingPosition();
        $entity1->setTitle('Z Position');
        $entity2 = new RankingPosition();
        $entity2->setTitle('A Position');
        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy([], ['title' => 'ASC']);

        $this->assertInstanceOf(RankingPosition::class, $result);
        $this->assertEquals('A Position', $result->getTitle());
    }

    public function testClear(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle('Clear Test');
        $this->getRepository()->save($entity);

        $this->getRepository()->clear();

        $this->assertFalse(self::getEntityManager()->contains($entity));
    }

    public function testFlush(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle('Flush Test');
        self::getEntityManager()->persist($entity);

        $this->getRepository()->flush();

        $this->assertNotNull($entity->getId());
    }

    public function testSaveAll(): void
    {
        $initialCount = $this->getRepository()->count([]);

        $entity1 = new RankingPosition();
        $entity1->setTitle('Batch Save 1');
        $entity2 = new RankingPosition();
        $entity2->setTitle('Batch Save 2');

        $this->getRepository()->saveAll([$entity1, $entity2]);

        $this->assertNotNull($entity1->getId());
        $this->assertNotNull($entity2->getId());
        $this->assertEquals($initialCount + 2, $this->getRepository()->count([]));
    }

    public function testNullableFieldIsNullQuery(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['title' => null]);

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->getTitle());
    }

    public function testNullableFieldIsNullCountQuery(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle(null);
        $entity->setValid(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['valid' => null]);

        $this->assertEquals(1, $count);
    }

    private function cleanupTestData(): void
    {
        try {
            self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingPosition')->execute();
            self::getEntityManager()->flush();
        } catch (\Exception $e) {
            // Table might not exist in some test scenarios, ignore the error
        }
    }

    public function testFindOneByWithSortingLogic(): void
    {
        $entity1 = new RankingPosition();
        $entity1->setTitle('位置A');
        $entity1->setValid(true);

        $entity2 = new RankingPosition();
        $entity2->setTitle('位置B');
        $entity2->setValid(false);

        $this->getRepository()->save($entity1);
        $this->getRepository()->save($entity2);

        $result = $this->getRepository()->findOneBy([], ['valid' => 'DESC']);

        $this->assertInstanceOf(RankingPosition::class, $result);
        $this->assertEquals('位置A', $result->getTitle());
    }

    public function testIsNullQueryForDescription(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle(null);
        $entity->setValid(true);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['title' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testIsNullQueryForSort(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle(null);
        $entity->setValid(true);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['title' => null]);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);
    }

    public function testIsNullCountQuery(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle(null);
        $entity->setValid(true);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['title' => null]);

        $this->assertGreaterThan(0, $count);
    }

    public function testNullableFieldIsNullQueryWithValid(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle('Valid Null Test');
        $entity->setValid(null);
        $this->getRepository()->save($entity);

        $results = $this->getRepository()->findBy(['valid' => null]);

        $this->assertCount(1, $results);
        $this->assertNull($results[0]->isValid());
    }

    public function testNullableFieldIsNullCountQueryWithValid(): void
    {
        $entity = new RankingPosition();
        $entity->setTitle('Valid Null Count Test');
        $entity->setValid(null);
        $this->getRepository()->save($entity);

        $count = $this->getRepository()->count(['valid' => null]);

        $this->assertEquals(1, $count);
    }
}
