<?php

namespace ProductRankingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class RankingItemFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const RANKING_ITEM_1 = 'ranking-item-1';
    public const RANKING_ITEM_2 = 'ranking-item-2';
    public const RANKING_ITEM_3 = 'ranking-item-3';

    public static function getGroups(): array
    {
        return ['product-ranking', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        $rankingList = $this->getReference(RankingListFixtures::RANKING_LIST_HOT, RankingList::class);

        $item1 = new RankingItem();
        $item1->setList($rankingList);
        $item1->setNumber(1);
        $item1->setSpuId('1001');
        $item1->setValid(true);
        $item1->setFixed(false);
        $item1->setScore(100);
        $item1->setTextReason('热销第一');
        $item1->setRecommendReason('品质优秀，用户好评如潮');

        $manager->persist($item1);

        $item2 = new RankingItem();
        $item2->setList($rankingList);
        $item2->setNumber(2);
        $item2->setSpuId('1002');
        $item2->setValid(true);
        $item2->setFixed(true);
        $item2->setScore(95);
        $item2->setTextReason('性价比之选');
        $item2->setRecommendReason('价格实惠，功能齐全');

        $manager->persist($item2);

        $item3 = new RankingItem();
        $item3->setList($rankingList);
        $item3->setNumber(3);
        $item3->setSpuId('1003');
        $item3->setValid(true);
        $item3->setFixed(false);
        $item3->setScore(90);
        $item3->setTextReason('新品推荐');
        $item3->setRecommendReason('最新款式，引领潮流');

        $manager->persist($item3);

        $manager->flush();

        $this->addReference(self::RANKING_ITEM_1, $item1);
        $this->addReference(self::RANKING_ITEM_2, $item2);
        $this->addReference(self::RANKING_ITEM_3, $item3);
    }

    public function getDependencies(): array
    {
        return [
            RankingListFixtures::class,
        ];
    }
}
