<?php
namespace TotalExpert\BernardScheduler\Tests;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Job;

class JobTest extends TestCase
{
    public function testItWrapsMessageWithEnvelope()
    {
        $job = new Job($this->getMessage(), new \DateTime());

        $this->assertInstanceOf(Envelope::class, $job->getEnvelope());
    }

    public function testItGetsProperEnqueueAt()
    {
        $enqueueAt = new \DateTime();

        $job = new Job($this->getMessage(), $enqueueAt);

        $this->assertInstanceOf(\DateTime::class, $job->getEnqueueAt());
        $this->assertEquals($enqueueAt, $job->getEnqueueAt());
    }

    public function testItGetsProperQueueName()
    {
        $queueName = 'email';

        $job = new Job($this->getMessage(), new \DateTime(), $queueName);

        $this->assertEquals($queueName, $job->getQueueName());
    }

    private function getMessage()
    {
        return new PlainMessage('DoJob', []);
    }
}
