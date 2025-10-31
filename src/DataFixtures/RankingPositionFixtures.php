<?php

namespace ProductRankingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use ProductRankingBundle\Entity\RankingPosition;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class RankingPositionFixtures extends Fixture implements FixtureGroupInterface
{
    public const POSITION_HOME = 'position-home';
    public const POSITION_CATEGORY = 'position-category';
    public const POSITION_SEARCH = 'position-search';

    public static function getGroups(): array
    {
        return ['product-ranking', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        $homePosition = new RankingPosition();
        $homePosition->setTitle('首页');
        $homePosition->setValid(true);

        $manager->persist($homePosition);

        $categoryPosition = new RankingPosition();
        $categoryPosition->setTitle('分类页');
        $categoryPosition->setValid(true);

        $manager->persist($categoryPosition);

        $searchPosition = new RankingPosition();
        $searchPosition->setTitle('搜索页');
        $searchPosition->setValid(true);

        $manager->persist($searchPosition);

        $manager->flush();

        $this->addReference(self::POSITION_HOME, $homePosition);
        $this->addReference(self::POSITION_CATEGORY, $categoryPosition);
        $this->addReference(self::POSITION_SEARCH, $searchPosition);
    }
}
