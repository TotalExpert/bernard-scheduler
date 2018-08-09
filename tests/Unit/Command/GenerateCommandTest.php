<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Command;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TotalExpert\BernardScheduler\Command\GenerateCommand;
use TotalExpert\BernardScheduler\Generator;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;

class GenerateCommandTest extends TestCase
{
    /**
     * @var Generator|MockObject
     */
    protected $generator;

    /**
     * @var ScheduleInterface|MockObject
     */
    protected $schedule;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->generator = $this->createMock(Generator::class);
        $this->schedule = $this->createMock(ScheduleInterface::class);

        $command = new GenerateCommand($this->schedule, $this->generator);
        $this->commandTester = new CommandTester($command);
    }

    public function testItGenerates()
    {
        $this
            ->generator
            ->expects($this->once())
            ->method('run')
            ->with(
                $this->identicalTo($this->schedule),
                [
                    'max-runtime' => 10,
                    'max-messages' => 2,
                    'stop-when-empty' => true,
                    'stop-on-error' => false
                ]
            );

        $this->commandTester->execute([
            '--max-runtime' => '10',
            '--max-messages' => 2,
            '--stop-when-empty' => true,
            '--stop-on-error' => false
        ]);
    }
}