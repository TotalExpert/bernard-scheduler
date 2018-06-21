<?php
namespace TotalExpert\BernardScheduler\Tests;

use Bernard\Envelope;
use TotalExpert\BernardScheduler\Job;

class JobTest extends AbstractSchedulerTestCase
{
    public function testItWrapsMessageWithEnvelope()
    {
        $this->assertInstanceOf(
            Envelope::class,
            $this->createJob()->getEnvelope()
        );
    }

    public function testItGetsProperEnqueueAt()
    {
        $enqueueAt = new \DateTime();
        $job = new Job($this->createMessage(), $enqueueAt);

        $this->assertInstanceOf(\DateTime::class, $job->getEnqueueAt());
        $this->assertEquals($enqueueAt, $job->getEnqueueAt());
    }

    public function testItGetsProperQueueName()
    {
        $queueName = 'email';

        $job = new Job($this->createMessage(), new \DateTime(), $queueName);

        $this->assertEquals($queueName, $job->getQueueName());
    }
}
