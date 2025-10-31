<?php

namespace ProductRankingBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use ProductRankingBundle\Entity\RankingItem;
use ProductRankingBundle\Entity\RankingList;
use ProductRankingBundle\Repository\RankingItemRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\ProductCoreBundle\Service\SpuService;

#[Autoconfigure(public: true)]
class RankingService
{
    public function __construct(
        private readonly RankingItemRepository $itemRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly SpuService $spuService,
    ) {
    }

    public function updateRankingItems(RankingList $list): void
    {
        if (null === $list->getScoreSql() || '' === $list->getScoreSql()) {
            return;
        }

        $this->entityManager->wrapInTransaction(function () use ($list): void {
            $this->removeNonFixedItems($list);
            $this->generateNewRankingItems($list);
            $this->removeExcessItems($list);
        });
    }

    private function removeNonFixedItems(RankingList $list): void
    {
        foreach ($list->getItems() as $item) {
            if (false === $item->isFixed()) {
                $this->entityManager->remove($item);
            }
        }
        $this->entityManager->flush();
    }

    private function generateNewRankingItems(RankingList $list): void
    {
        $scoreSql = $list->getScoreSql();
        if (null === $scoreSql) {
            return;
        }
        $rows = $this->entityManager->getConnection()->executeQuery($scoreSql);

        // 预加载已占用的位置，避免循环中查询数据库
        $occupiedPositions = $this->getOccupiedPositions($list);
        $number = 1;
        $newItems = [];

        foreach ($rows->iterateAssociative() as $row) {
            if ($this->shouldStopProcessing($list, $number)) {
                break;
            }

            $spuData = $this->extractSpuData($row);
            $spuId = $this->resolveSpuId($spuData['id']);

            if ($this->isSpuAlreadyRanked($list, $spuId)) {
                ++$number;
                continue;
            }

            $number = $this->findNextAvailablePositionFromSet($occupiedPositions, $number);
            $newItems[] = $this->createRankingItemEntity($list, $spuId, $number, $spuData);
            $occupiedPositions[$number] = true; // 标记这个位置已被占用

            ++$number;
        }

        // 批量持久化，避免频繁flush
        $this->persistRankingItems($newItems);
    }

    private function removeExcessItems(RankingList $list): void
    {
        if ($list->getCount() <= 0) {
            return;
        }

        $items = $this->itemRepository->createQueryBuilder('a')
            ->where('a.list = :list')
            ->orderBy('a.number', 'DESC')
            ->setParameter('list', $list)
            ->getQuery()
            ->toIterable()
        ;

        foreach ($items as $item) {
            /** @var RankingItem $item */
            if ($item->getNumber() > $list->getCount()) {
                $this->entityManager->remove($item);
            }
        }
        $this->entityManager->flush();
    }

    private function shouldStopProcessing(RankingList $list, int $number): bool
    {
        return $list->getCount() > 0 && $list->getCount() < $number;
    }

    /**
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    private function extractSpuData(array $row): array
    {
        return [
            'id' => array_shift($row),
            'score' => intval(array_shift($row)),
            'textReason' => array_shift($row),
        ];
    }

    private function resolveSpuId(mixed $id): string
    {
        $spuId = is_string($id) ? $id : (string) $id;

        // 通过 SpuService 访问，避免跨模块Repository调用
        $spu = $this->spuService->findSpuById($spuId);

        return null !== $spu ? (string) $spu->getId() : $spuId;
    }

    private function isSpuAlreadyRanked(RankingList $list, string $spuId): bool
    {
        $item = $this->itemRepository->findOneBy([
            'list' => $list,
            'spuId' => $spuId,
        ]);

        return null !== $item;
    }

    /**
     * @return array<int, true>
     */
    private function getOccupiedPositions(RankingList $list): array
    {
        $positions = [];
        $items = $this->itemRepository->findBy(['list' => $list]);

        foreach ($items as $item) {
            $number = $item->getNumber();
            if (null !== $number) {
                $positions[$number] = true;
            }
        }

        return $positions;
    }

    /**
     * @param array<int, true> $occupiedPositions
     */
    private function findNextAvailablePositionFromSet(array $occupiedPositions, int $startNumber): int
    {
        $number = $startNumber;

        while (isset($occupiedPositions[$number])) {
            ++$number;
        }

        return $number;
    }

    /**
     * @param array<string, mixed> $spuData
     */
    private function createRankingItemEntity(RankingList $list, string $spuId, int $number, array $spuData): RankingItem
    {
        $item = new RankingItem();
        $item->setList($list);
        $item->setSpuId($spuId);
        $item->setNumber($number);
        $item->setScore($spuData['score']);
        $item->setFixed(false);
        $item->setTextReason($spuData['textReason']);
        $item->setValid(true);

        return $item;
    }

    /**
     * @param RankingItem[] $items
     */
    private function persistRankingItems(array $items): void
    {
        foreach ($items as $item) {
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}
