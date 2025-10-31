<?php

namespace ProductRankingBundle\Command;

use ProductRankingBundle\Repository\RankingListRepository;
use ProductRankingBundle\Service\RankingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '*/30 * * * *')]
#[AsCommand(name: self::NAME, description: '计算排行榜商品')]
class RankingListCalcCommand extends Command
{
    public const NAME = 'product:ranking-list:calc';

    public function __construct(
        private readonly RankingListRepository $listRepository,
        private readonly RankingService $rankingService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lists = $this->listRepository->findBy([
            'valid' => true,
        ]);
        foreach ($lists as $list) {
            $this->rankingService->updateRankingItems($list);
        }

        return Command::SUCCESS;
    }
}
