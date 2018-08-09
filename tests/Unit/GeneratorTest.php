<?php
namespace TotalExpert\BernardScheduler\Tests\Unit;

use Bernard\Producer;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TotalExpert\BernardScheduler\Event\BernardSchedulerEvents;
use TotalExpert\BernardScheduler\Event\ErrorEvent;
use TotalExpert\BernardScheduler\Event\JobEvent;
use TotalExpert\BernardScheduler\Event\PingEvent;
use TotalExpert\BernardScheduler\Generator;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;
use TotalExpert\BernardScheduler\Tests\Base\AbstractSchedulerTestCase;

class GeneratorTest extends AbstractSchedulerTestCase
{
    /**
     * @var Producer|MockObject
     */
    protected $producer;

    /**
     * @var EventDispatcher|MockObject
     */
    protected $eventDispatcher;

    /**
     * @var ScheduleInterface|MockObject
     */
    protected $schedule;

    /**
     * @var Generator
     */
    protected $generator;

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
            ->setMethods(['dequeue', 'cleanup'])
            ->getMockForAbstractClass();

        $this->producer = $this
            ->getMockBuilder(Producer::class)
            ->disableOriginalConstructor()
            ->setMethods(['produce'])
            ->getMock();

        $this->generator = new Generator($this->producer, $this->eventDispatcher);
    }

    public function testItShutsDown()
    {
        $this->generator->shutdown();

        $this->assertFalse($this->generator->tick($this->schedule, []));
    }

    public function testItEmitsScheduleEvents()
    {
        $job = $this->createJob();

        $this
            ->schedule
            ->expects($this->once())
            ->method('dequeue')
            ->willReturn($job);

        $this
            ->schedule
            ->expects($this->once())
            ->method('cleanup');

        $this
            ->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(BernardSchedulerEvents::PING, new PingEvent());

        $this
            ->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(BernardSchedulerEvents::GENERATE, new JobEvent($job));

        $this
            ->eventDispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(BernardSchedulerEvents::CLEANUP, new JobEvent($job));

        $this->assertTrue($this->generator->tick($this->schedule, []));
    }

    public function testItEmitsErrorEventOnException()
    {
        $this->assertErrorEventOn(new \Exception());

        $this->assertTrue($this->generator->tick($this->schedule, []));
    }

    public function testItEmitsErrorEventOnThrowable()
    {
        $this->assertErrorEventOn(new \TypeError());

        $this->assertTrue($this->generator->tick($this->schedule, []));
    }

    public function testItThrowsExceptionWithStopOnError()
    {
        $this->assertErrorEventOn(new \Exception());

        $this->expectException(\Exception::class);

        $this->assertTrue($this->generator->tick($this->schedule, ['stop-on-error' => true]));
    }

    protected function assertErrorEventOn($error)
    {
        $job = $this->createJob();

        $this
            ->schedule
            ->expects($this->once())
            ->method('dequeue')
            ->willReturn($job);

        $this
            ->producer
            ->expects($this->once())
            ->method('produce')
            ->will($this->throwException($error));

        $this
            ->eventDispatcher
            ->expects($this->at(2))
            ->method('dispatch')
            ->with(BernardSchedulerEvents::ERROR, new ErrorEvent($job, $error));
    }

    public function testItStopsOnMaxMessages()
    {
        $this
            ->schedule
            ->expects($this->any())
            ->method('dequeue')
            ->willReturn($this->createJob());

        $this->assertTrue($this->generator->tick($this->schedule, ['max-messages' => 2]));
        $this->assertFalse($this->generator->tick($this->schedule, []));
    }

    public function testItStopsWhenEmpty()
    {
        $this
            ->schedule
            ->expects($this->at(0))
            ->method('dequeue')
            ->willReturn($this->createJob());

        $this
            ->schedule
            ->expects($this->at(1))
            ->method('dequeue')
            ->willReturn(null);

        $this->assertTrue($this->generator->tick($this->schedule, ['stop-when-empty' => true]));
        $this->assertFalse($this->generator->tick($this->schedule, []));
    }

    public function testItStopsOnMaxRuntime()
    {
        $this->assertFalse($this->generator->tick($this->schedule, ['max-runtime' => -1]));
    }
}