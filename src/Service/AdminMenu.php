<?php

namespace ProductRankingBundle\Service;

use Knp\Menu\ItemInterface;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Entity\RankingPosition;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Attribute\MenuProvider;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[MenuProvider]
#[Autoconfigure(public: true)]
class AdminMenu implements MenuProviderInterface
{
    public function __construct(private readonly ?LinkGeneratorInterface $linkGenerator = null)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $this->linkGenerator) {
            return;
        }

        if (null === $item->getChild('电商中心')) {
            $item->addChild('电商中心');
        }

        $ecommerceItem = $item->getChild('电商中心');
        if (null !== $ecommerceItem) {
            // 排行榜管理
            $ecommerceItem->addChild('排行榜列表')
                ->setUri($this->linkGenerator->getCurdListPage(RankingList::class))
                ->setAttribute('icon', 'fas fa-list-ol')
            ;

            $ecommerceItem->addChild('排行榜商品')
                ->setUri($this->linkGenerator->getCurdListPage(RankingItem::class))
                ->setAttribute('icon', 'fas fa-trophy')
            ;

            $ecommerceItem->addChild('排行榜位置')
                ->setUri($this->linkGenerator->getCurdListPage(RankingPosition::class))
                ->setAttribute('icon', 'fas fa-map-marker-alt')
            ;
        }
    }
}
