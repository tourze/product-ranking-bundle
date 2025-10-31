<?php

namespace ProductRankingBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use ProductRankingBundle\Entity\RankingList;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(RankingList::class)]
final class RankingListTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new RankingList();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'valid' => ['valid', true];
        yield 'title' => ['title', 'Test Title'];
        yield 'subtitle' => ['subtitle', 'Test Subtitle'];
        yield 'color' => ['color', 'red'];
        yield 'logoUrl' => ['logoUrl', 'https://example.com/logo.png'];
        yield 'scoreSql' => ['scoreSql', 'SELECT * FROM products'];
        yield 'count' => ['count', 100];
    }
}
