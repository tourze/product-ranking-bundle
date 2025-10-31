<?php

namespace ProductRankingBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Procedure\GetProductRankingLists;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetProductRankingLists::class)]
#[RunTestsInSeparateProcesses]
final class GetProductRankingListsTest extends AbstractProcedureTestCase
{
    public function testClassExists(): void
    {
        $procedure = self::getService(GetProductRankingLists::class);
        $this->assertInstanceOf(GetProductRankingLists::class, $procedure);
    }

    public function testExecuteMethodReturnsArray(): void
    {
        $reflection = new \ReflectionMethod(GetProductRankingLists::class, 'execute');
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }

    protected function onSetUp(): void
    {
        // 在这里可以添加自定义的初始化逻辑
    }

    public function testExecuteWithoutPosition(): void
    {
        // 清理现有数据
        self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingList')->execute();

        // 创建测试数据
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setColor('#FF0000');
        $list->setValid(true);
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $procedure = self::getService(GetProductRankingLists::class);
        $result = $procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);
        $this->assertNotEmpty($result['list']);

        // 验证返回的数据结构
        $firstItem = $result['list'][0];
        $this->assertIsArray($firstItem);

        // 验证返回的数据结构
        $this->assertArrayHasKey('title', $firstItem);
        $this->assertEquals('Test List', $firstItem['title']);
    }

    public function testExecuteWithPosition(): void
    {
        // 清理现有数据
        self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingList')->execute();

        // 创建测试数据
        $list = new RankingList();
        $list->setTitle('Hot List');
        $list->setColor('#FF0000');
        $list->setValid(true);
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $procedure = self::getService(GetProductRankingLists::class);
        $procedure->position = 'hot';
        $result = $procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);
        // 由于没有设置 position 关联，应该返回空数组
        $this->assertEmpty($result['list']);
    }

    public function testExecuteWithInvalidList(): void
    {
        // 清理现有数据
        self::getEntityManager()->createQuery('DELETE FROM ProductRankingBundle\Entity\RankingList')->execute();

        // 创建无效的测试数据
        $list = new RankingList();
        $list->setTitle('Invalid List');
        $list->setColor('#FF0000');
        $list->setValid(false);
        self::getEntityManager()->persist($list);
        self::getEntityManager()->flush();

        $procedure = self::getService(GetProductRankingLists::class);
        $result = $procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);
        // 无效的列表不应该返回
        $this->assertEmpty($result['list']);
    }
}
