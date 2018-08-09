<?php
namespace TotalExpert\BernardScheduler\Tests\Unit;

use Bernard\Message\PlainMessage;
use Bernard\Producer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TotalExpert\BernardScheduler\Event\BernardSchedulerEvents;
use TotalExpert\BernardScheduler\Event\JobEvent;
use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;
use TotalExpert\BernardScheduler\Scheduler;

class SchedulerTest extends TestCase
{
    /**
     * @var EventDispatcher|MockObject $eventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var ScheduleInterface|MockObject
     */
    protected $schedule;

    /**
     * @var Producer|MockObject
     */
    protected $producer;

    /**
     * @var Scheduler
     */
    protected $scheduler;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->eventDispatcher = $this
            ->getMockBuilder(EventDispatcher::class)
            ->setMethods(['dispatch'])
            ->getMock();

        $this->schedule = $this
            ->getMockBuilder(ScheduleInterface::class)
            ->setMethods(['schedule'])
            ->getMockForAbstractClass();

        $this->producer = $this
            ->getMockBuilder(Producer::class)
            ->disableOriginalConstructor()
            ->setMethods(['produce'])
            ->getMock();

        $this->scheduler = new Scheduler(
            $this->eventDispatcher,
            $this->schedule,
            $this->producer
        );
    }

    public function testItSchedulesJobWhenScheduleTimeIsInFuture()
    {
        $this
            ->schedule
            ->expects($this->once())
            ->method('enqueue')
            ->with(
                $this->callback(function ($job) {
                    return $job instanceof Job;
                })
            );

        $this
            ->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(BernardSchedulerEvents::SCHEDULE),
                $this->callback(function ($event) {
                    return $event instanceof JobEvent;
                })
            );

        $this->producer->expects($this->never())->method('produce');

        $this->scheduler->schedule($this->createMessage(), new \DateTime('+1 minute'));
    }

    public function testItProducesJobWhenScheduleTimeIsInPast()
    {
        $queueName = 'email';
        $message = $this->createMessage();

        $this->schedule->expects($this->never())->method('enqueue');

        $this->eventDispatcher->expects($this->never())->method('dispatch');

        $this
            ->producer
            ->expects($this->once())
            ->method('produce')
            ->with(
                $this->identicalTo($message),
                $this->identicalTo($queueName)
            );

        $this->scheduler->schedule($message, new \DateTime('-1 minute'), $queueName);
    }

    private function createMessage()
    {
        return new PlainMessage('SendEmail', []);
    }
}
