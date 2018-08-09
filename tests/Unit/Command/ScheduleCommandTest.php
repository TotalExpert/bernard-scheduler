<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Command;

use Bernard\Message\PlainMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use TotalExpert\BernardScheduler\Command\ScheduleCommand;
use TotalExpert\BernardScheduler\Scheduler;

class ScheduleCommandTest extends TestCase
{
    /**
     * @var Scheduler|MockObject
     */
    protected $scheduler;

    /**
     * @var CommandTester
     */
    protected $commandTester;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->scheduler = $this->createMock(Scheduler::class);
        $command = new ScheduleCommand($this->scheduler);
        $this->commandTester = new CommandTester($command);
    }

    public function testItSchedulesWithValidInput()
    {
        $name = 'SendEmail';
        $datetime = new \DateTime(date('Y-m-d H:i:s'));
        $messageArgs = [
            'subject' => 'email subject',
            'body' => 'email body',
            'email_id' => 10
        ];
        $queue = 'emails';

        $this
            ->scheduler
            ->expects($this->once())
            ->method('schedule')
            ->with(
                $this->equalTo(new PlainMessage($name, $messageArgs)),
                $this->equalTo($datetime),
                $this->equalTo($queue)
            );

        $this->commandTester->execute([
            'name' => $name,
            'message' => json_encode($messageArgs),
            '--datetime' => $datetime->format('Y-m-d H:i:s'),
            '--queue' => $queue
        ]);
    }

    public function testItSchedulesWithNoMessageOrOptions()
    {
        $name = 'SendEmail';

        $this
            ->scheduler
            ->expects($this->once())
            ->method('schedule')
            ->with(
                $this->equalTo(new PlainMessage($name, [])),
                $this->callback(function($datetime){
                    $nowDt = new \DateTime();
                    return $nowDt->getTimestamp() === $datetime->getTimestamp();
                }),
                $this->equalTo(null)
            );

        $this->commandTester->execute(['name' => $name]);
    }

    public function testItThrowsExceptionWithInvalidJson()
    {
        $this->expectException(\RuntimeException::class);
        $this->commandTester->execute([
            'name' => 'SendEmail',
            'message' => '&!:[}]}'
        ]);
    }
}