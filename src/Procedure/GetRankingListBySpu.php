<?php

namespace ProductRankingBundle\Procedure;

use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Repository\RankingItemRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tourze\DoctrineHelper\CacheHelper;
use Tourze\JsonRPC\Core\Attribute\MethodDoc;
use Tourze\JsonRPC\Core\Attribute\MethodExpose;
use Tourze\JsonRPC\Core\Attribute\MethodParam;
use Tourze\JsonRPC\Core\Attribute\MethodTag;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\JsonRPC\Core\Model\JsonRpcParams;
use Tourze\JsonRPC\Core\Model\JsonRpcRequest;
use Tourze\JsonRPCCacheBundle\Procedure\CacheableProcedure;

#[MethodTag(name: '产品模块')]
#[MethodDoc(summary: '根据商品获取排行榜')]
#[MethodExpose(method: 'GetRankingListBySpu')]
class GetRankingListBySpu extends CacheableProcedure
{
    #[MethodParam(description: 'SPU ID')]
    public string $spuId;

    public function __construct(
        private readonly RankingItemRepository $itemRepository,
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    public function execute(): array
    {
        $items = $this->itemRepository->findBy([
            'spuId' => $this->spuId,
            'valid' => true,
        ]);
        if ([] === $items) {
            throw new ApiException('该商品不在任何榜单上');
        }

        $list = [];
        foreach ($items as $item) {
            if (null === $item->getList()) {
                continue;
            }
            if (false === $item->getList()->isValid()) {
                continue;
            }

            $tmp = $this->normalizer->normalize($item, 'array', ['groups' => 'restful_read']);
            $list[] = $tmp;
        }

        if ([] === $list) {
            throw new ApiException('该商品不在任何榜单上');
        }

        return [
            'list' => $list,
        ];
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
        yield CacheHelper::getClassTags(RankingItem::class);
    }
}
