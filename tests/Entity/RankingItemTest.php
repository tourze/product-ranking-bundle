<?php

namespace ProductRankingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use ProductRankingBundle\Entity\RankingItem;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(RankingItem::class)]
final class RankingItemTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new RankingItem();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'valid' => ['valid', true];
        yield 'number' => ['number', 1];
        yield 'spuId' => ['spuId', '123'];
        yield 'textReason' => ['textReason', 'Test reason'];
        yield 'score' => ['score', 100];
        yield 'fixed' => ['fixed', true];
        yield 'recommendThumb' => ['recommendThumb', 'https://example.com/thumb.jpg'];
        yield 'recommendReason' => ['recommendReason', 'Test recommend reason'];
    }
}
