<?php

namespace ProductRankingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use ProductRankingBundle\Entity\RankingPosition;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(RankingPosition::class)]
final class RankingPositionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new RankingPosition();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'valid' => ['valid', true];
        yield 'title' => ['title', 'Test Position'];
    }
}
