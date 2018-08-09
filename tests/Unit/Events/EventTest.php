<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Events;

use TotalExpert\BernardScheduler\Event\ErrorEvent;
use TotalExpert\BernardScheduler\Event\JobEvent;
use TotalExpert\BernardScheduler\Event\PingEvent;
use TotalExpert\BernardScheduler\Tests\Base\AbstractSchedulerTestCase;

class EventTest extends AbstractSchedulerTestCase
{
    public function testErrorEvent()
    {
        $this->assertTrue(class_exists(ErrorEvent::class));

        $job = $this->createJob();
        $error = new \Exception();
        $event = new ErrorEvent($job, $error);

        $this->assertInstanceOf(ErrorEvent::class, $event);
        $this->assertEquals($job, $event->getJob());
        $this->assertEquals($error, $event->getError());
    }

    public function testJobEvent()
    {
        $this->assertTrue(class_exists(JobEvent::class));

        $job = $this->createJob();
        $event = new JobEvent($job);

        $this->assertInstanceOf(JobEvent::class, $event);
        $this->assertEquals($job, $event->getJob());
    }

    public function testPingEvent()
    {
        $this->assertTrue(class_exists(PingEvent::class));

        $pingEvent = new PingEvent();

        $this->assertInstanceOf(PingEvent::class, $pingEvent);
    }
}
