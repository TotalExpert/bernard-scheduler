<?php
namespace TotalExpert\BernardScheduler\Tests;

use Bernard\Message\PlainMessage;
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

        $this->scheduler = new Scheduler($this->eventDispatcher, $this->schedule);
    }

    public function testItDispatchesEvent()
    {
        $this
            ->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(BernardSchedulerEvents::SCHEDULE),
                $this->callback(function($event){
                    return $event instanceof JobEvent;
                })
            );

        $this->scheduler->schedule(new PlainMessage('SendEmail', []), new \DateTime());
    }

    public function testItSchedules()
    {
        $this
            ->schedule
            ->expects($this->once())
            ->method('enqueue')
            ->with(
                $this->callback(function($job) {
                    return $job instanceof Job;
                })
            );

        $this->scheduler->schedule(new PlainMessage('SendEmail', []), new \DateTime());
    }
}