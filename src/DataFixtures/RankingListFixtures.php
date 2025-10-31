<?php

namespace ProductRankingBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Entity\RankingPosition;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[When(env: 'dev')]
class RankingListFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public const RANKING_LIST_HOT = 'ranking-list-hot';
    public const RANKING_LIST_NEW = 'ranking-list-new';
    public const RANKING_LIST_SALE = 'ranking-list-sale';

    public static function getGroups(): array
    {
        return ['product-ranking', 'test'];
    }

    public function load(ObjectManager $manager): void
    {
        $homePosition = $this->getReference(RankingPositionFixtures::POSITION_HOME, RankingPosition::class);
        $categoryPosition = $this->getReference(RankingPositionFixtures::POSITION_CATEGORY, RankingPosition::class);

        $hotList = new RankingList();
        $hotList->setTitle('热销排行榜');
        $hotList->setSubtitle('最受欢迎商品');
        $hotList->setValid(true);
        $hotList->setColor('#ff6b6b');
        $hotList->setLogoUrl('https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=200');
        $hotList->setCount(10);
        $hotList->setScoreSql('SELECT COUNT(*) as score FROM orders WHERE spu_id = ?');
        $hotList->addPosition($homePosition);
        $hotList->addPosition($categoryPosition);

        $manager->persist($hotList);

        $newList = new RankingList();
        $newList->setTitle('新品排行榜');
        $newList->setSubtitle('最新上架商品');
        $newList->setValid(true);
        $newList->setColor('#4ecdc4');
        $newList->setLogoUrl('https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200');
        $newList->setCount(5);
        $newList->setScoreSql('SELECT DATEDIFF(NOW(), created_at) as score FROM products WHERE spu_id = ?');
        $newList->addPosition($homePosition);

        $manager->persist($newList);

        $saleList = new RankingList();
        $saleList->setTitle('促销排行榜');
        $saleList->setSubtitle('超值优惠商品');
        $saleList->setValid(true);
        $saleList->setColor('#45b7d1');
        $saleList->setLogoUrl('https://images.unsplash.com/photo-1607083206869-4c7672e72a8a?w=200');
        $saleList->setCount(8);
        $saleList->setScoreSql('SELECT discount_percent as score FROM promotions WHERE spu_id = ?');
        $saleList->addPosition($categoryPosition);

        $manager->persist($saleList);

        $manager->flush();

        $this->addReference(self::RANKING_LIST_HOT, $hotList);
        $this->addReference(self::RANKING_LIST_NEW, $newList);
        $this->addReference(self::RANKING_LIST_SALE, $saleList);
    }

    public function getDependencies(): array
    {
        return [
            RankingPositionFixtures::class,
        ];
    }
}
