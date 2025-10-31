<?php

namespace ProductRankingBundle\Tests\Procedure;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Procedure\GetRankingListBySpu;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Tests\AbstractProcedureTestCase;

/**
 * @internal
 */
#[CoversClass(GetRankingListBySpu::class)]
#[RunTestsInSeparateProcesses]
final class GetRankingListBySpuTest extends AbstractProcedureTestCase
{
    public function testClassExists(): void
    {
        $procedure = self::getService(GetRankingListBySpu::class);
        $this->assertInstanceOf(GetRankingListBySpu::class, $procedure);
    }

    public function testExecuteMethodReturnsArray(): void
    {
        $reflection = new \ReflectionMethod(GetRankingListBySpu::class, 'execute');
        $returnType = $reflection->getReturnType();
        $this->assertInstanceOf(\ReflectionNamedType::class, $returnType);
        $this->assertSame('array', $returnType->getName());
    }

    protected function onSetUp(): void
    {
        // 在这里可以添加自定义的初始化逻辑
    }

    public function testExecuteWithNonExistentSpu(): void
    {
        $procedure = self::getService(GetRankingListBySpu::class);
        $procedure->spuId = 'non-existent';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('该商品不在任何榜单上');

        $procedure->execute();
    }

    public function testExecuteWithValidSpu(): void
    {
        // 创建测试数据
        $list = new RankingList();
        $list->setTitle('Test List');
        $list->setColor('#FF0000');
        $list->setValid(true);
        self::getEntityManager()->persist($list);

        $item = new RankingItem();
        $item->setNumber(1);
        $item->setSpuId('test-spu-123');
        $item->setList($list);
        $item->setValid(true);
        self::getEntityManager()->persist($item);

        self::getEntityManager()->flush();

        $procedure = self::getService(GetRankingListBySpu::class);
        $procedure->spuId = 'test-spu-123';
        $result = $procedure->execute();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('list', $result);
        $this->assertIsArray($result['list']);
        $this->assertNotEmpty($result['list']);

        // 验证返回的数据结构
        $firstItem = $result['list'][0];
        $this->assertIsArray($firstItem);
        $this->assertArrayHasKey('spuId', $firstItem);
        $this->assertEquals('test-spu-123', $firstItem['spuId']);
    }

    public function testExecuteWithInvalidList(): void
    {
        // 创建测试数据 - 无效的列表
        $list = new RankingList();
        $list->setTitle('Invalid List');
        $list->setColor('#FF0000');
        $list->setValid(false);
        self::getEntityManager()->persist($list);

        $item = new RankingItem();
        $item->setNumber(1);
        $item->setSpuId('test-spu-invalid');
        $item->setList($list);
        $item->setValid(true);
        self::getEntityManager()->persist($item);

        self::getEntityManager()->flush();

        $procedure = self::getService(GetRankingListBySpu::class);
        $procedure->spuId = 'test-spu-invalid';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('该商品不在任何榜单上');

        $procedure->execute();
    }

    public function testExecuteWithInvalidItem(): void
    {
        // 创建测试数据 - 无效的项目
        $list = new RankingList();
        $list->setTitle('Valid List');
        $list->setColor('#FF0000');
        $list->setValid(true);
        self::getEntityManager()->persist($list);

        $item = new RankingItem();
        $item->setNumber(1);
        $item->setSpuId('test-spu-invalid-item');
        $item->setList($list);
        $item->setValid(false);
        self::getEntityManager()->persist($item);

        self::getEntityManager()->flush();

        $procedure = self::getService(GetRankingListBySpu::class);
        $procedure->spuId = 'test-spu-invalid-item';

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('该商品不在任何榜单上');

        $procedure->execute();
    }
}
