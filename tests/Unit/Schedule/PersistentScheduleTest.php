<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Schedule;

use Bernard\Message\PlainMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Driver\DriverInterface;
use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Schedule\PersistentSchedule;
use TotalExpert\BernardScheduler\Serializer\SerializerInterface;

class PersistentScheduleTest extends TestCase
{
    /**
     * @var DriverInterface|MockObject
     */
    protected $driver;

    /**
     * @var SerializerInterface|MockObject
     */
    protected $serializer;

    /**
     * @var PersistentSchedule
     */
    protected $schedule;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->driver = $this->createMock(DriverInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->schedule = new PersistentSchedule($this->driver, $this->serializer);
    }

    public function testItEnqueuesJob()
    {
        $enqueueAt = new \DateTime();
        $queueName = 'emails';
        $message = new PlainMessage('SendEmail', []);
        $job = new Job($message, $enqueueAt, $queueName);

        $this
            ->driver
            ->expects($this->once())
            ->method('enqueueAt')
            ->with(
                $enqueueAt->getTimestamp(),
                $this->anything()
            );

        $this
            ->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($job))
            ->willReturn('a serialized job');

        $this->schedule->enqueue($job);
    }

    public function testItDeQueuesJobWhenDriverPopsJob()
    {
        $dequeueTime = new \DateTime();
        $serialized = 'a job';

        $this
            ->driver
            ->expects($this->once())
            ->method('popJob')
            ->with($dequeueTime->getTimestamp())
            ->willReturn([$serialized, '1']);

        $expectedJob = new Job(
            new PlainMessage('SendEmail', []),
            new \DateTime(),
            'email'
        );

        $this
            ->serializer
            ->expects($this->once())
            ->method('unserialize')
            ->with($serialized)
            ->willReturn($expectedJob);

        $job = $this->schedule->dequeue($dequeueTime);
        $this->assertEquals($expectedJob, $job);
    }

    public function testItDeQueuesNullJobWhenDriverReturnsNullSerialized()
    {
        $dequeueTime = new \DateTime();

        $this
            ->driver
            ->expects($this->once())
            ->method('popJob')
            ->with($dequeueTime->getTimestamp())
            ->willReturn([null, null]);

        $this
            ->serializer
            ->expects($this->never())
            ->method('unserialize');

        $job = $this->schedule->dequeue($dequeueTime);
        $this->assertNull($job);
    }

    public function testItCleansUpJobWhenItHasReceipt()
    {
        $dequeueTime = new \DateTime();
        $serialized = 'a job';
        $receipt = '1';

        $this
            ->driver
            ->expects($this->once())
            ->method('popJob')
            ->with($dequeueTime->getTimestamp())
            ->willReturn([$serialized, $receipt]);

        $expectedJob = new Job(
            new PlainMessage('SendEmail', []),
            new \DateTime(),
            'email'
        );

        $this
            ->serializer
            ->expects($this->once())
            ->method('unserialize')
            ->with($serialized)
            ->willReturn($expectedJob);

        $job = $this->schedule->dequeue($dequeueTime);
        $this->assertEquals($expectedJob, $job);

        $this
            ->driver
            ->expects($this->once())
            ->method('cleanup')
            ->with($receipt);

        $this->schedule->cleanup($job);
    }

    public function testItDoesntCleanUpJobWhenItDoesntHaveReceipt()
    {
        $this->driver->expects($this->never())->method('cleanup');

        $job = new Job(
            new PlainMessage('SendEmail', []),
            new \DateTime(),
            'email'
        );

        $this->schedule->cleanup($job);
    }
}