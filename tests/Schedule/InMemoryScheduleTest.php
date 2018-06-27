<?php
namespace TotalExpert\BernardScheduler\Tests\Schedule;

use Bernard\Message\PlainMessage;
use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Schedule\InMemorySchedule;

class InMemoryScheduleTest extends TestCase
{
    /**
     * @var InMemorySchedule
     */
    protected $schedule;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->schedule = new InMemorySchedule();
    }

    public function testItDeQueuesNullJobWhenNoJobsAvailable()
    {
        $this->assertNull($this->schedule->dequeue(new \DateTime()));
    }

    public function testItDeQueuesJobWhenJobAvailable()
    {
        $job = new Job(
            new PlainMessage('SendEmail', []),
            new \DateTime('-1 seconds'),
            'email'
        );

        $this->schedule->enqueue($job);
        $deQueuedJob = $this->schedule->dequeue(new \DateTime());
        $this->assertNotNull($deQueuedJob);
        $this->assertEquals($job, $deQueuedJob);
    }

    public function testItDoesntDeQueueSameJob()
    {
        $job = new Job(
            new PlainMessage('SendEmail', []),
            new \DateTime('-1 seconds'),
            'email'
        );

        $this->schedule->enqueue($job);

        $this->assertNotNull($this->schedule->dequeue(new \DateTime()));
        $this->assertNull($this->schedule->dequeue(new \DateTime()));
    }
}