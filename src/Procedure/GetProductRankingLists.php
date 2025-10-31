<?php

namespace ProductRankingBundle\Procedure;

use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Repository\RankingListRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;

#[MethodTag(name: '产品模块')]
#[MethodDoc(summary: '获取所有排行榜列表')]
#[MethodExpose(method: 'GetProductRankingLists')]
class GetProductRankingLists extends CacheableProcedure
{
    #[MethodParam(description: '推荐位置')]
    public ?string $position = null;

    public function __construct(
        private readonly RankingListRepository $listRepository,
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function execute(): array
    {
        $qb = $this->listRepository
            ->createQueryBuilder('a')
            ->where('a.valid = true')
        ;
        if (null !== $this->position) {
            $qb->leftJoin('a.positions', 'p');
            $qb->andWhere('p.title = :title');
            $qb->setParameter('title', $this->position);
        }

        $result = [
            'list' => [],
        ];
        $lists = $qb->getQuery()->getResult();
        foreach ($lists as $item) {
            /* @var RankingList $item */
            $result['list'][] = $this->normalizer->normalize($item, 'array', ['groups' => 'restful_read']);
        }

        return $result;
    }

    public function getCacheKey(JsonRpcRequest $request): string
    {
        $params = $request->getParams();
        $key = static::buildParamCacheKey($params ?? new JsonRpcParams([]));
        if (null !== $this->security->getUser()) {
            $key .= '-' . $this->security->getUser()->getUserIdentifier();
        }

        return $key;
    }

    public function getCacheDuration(JsonRpcRequest $request): int
    {
        return 60 * 10;
    }

    /**
     * @return iterable<string>
     */
    public function getCacheTags(JsonRpcRequest $request): iterable
    {
        yield CacheHelper::getClassTags(RankingList::class);
        yield CacheHelper::getClassTags(RankingItem::class);
    }
}
