<?php

namespace ProductRankingBundle\Tests\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ProductRankingBundle\Command\RankingListCalcCommand;
use ProductRankingBundle\Repository\RankingListRepository;
use ProductRankingBundle\Service\RankingService;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(RankingListCalcCommand::class)]
#[RunTestsInSeparateProcesses]
final class RankingListCalcCommandTest extends AbstractCommandTestCase
{
    private RankingListCalcCommand $command;

    private CommandTester $commandTester;

    public function testCommandName(): void
    {
        $this->assertEquals('product:ranking-list:calc', RankingListCalcCommand::NAME);
    }

    public function testCommandCanBeExecuted(): void
    {
        $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testCommandServiceRegistration(): void
    {
        $this->assertInstanceOf(RankingListCalcCommand::class, $this->command);
        $this->assertEquals('product:ranking-list:calc', $this->command->getName());
    }

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $mockRankingService = $this->createMock(RankingService::class);
        $mockRankingService->expects($this->any())
            ->method('updateRankingItems')
        ;

        $mockListRepository = $this->createMock(RankingListRepository::class);
        $mockListRepository->expects($this->any())
            ->method('findBy')
            ->with(['valid' => true])
            ->willReturn([])
        ;

        self::getContainer()->set(RankingService::class, $mockRankingService);
        self::getContainer()->set(RankingListRepository::class, $mockListRepository);

        $command = self::getContainer()->get(RankingListCalcCommand::class);
        self::assertInstanceOf(RankingListCalcCommand::class, $command);
        $this->command = $command;

        $application = new Application();
        $application->add($this->command);
        $this->commandTester = new CommandTester($this->command);
    }
}
