<?php
namespace TotalExpert\BernardScheduler\Tests\Driver;

use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Driver\DriverInterface;

abstract class AbstractDriverTest extends TestCase
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function setUp()
    {
        $this->driver = $this->createDriver();
    }

    /**
     * @return DriverInterface
     */
    protected abstract function createDriver();


    public function testItPopsNullJobWithNoMessageInSchedule()
    {
        $this->assertNull($this->driver->popJob(time()));
    }

    public function testItPopsNullJobWithFutureJobInSchedule()
    {
        $this->enqueueJob('+1 minutes');
        $this->assertNull($this->driver->popJob(time()));
    }

    public function testItPopsJobArrayWithPastJobInSchedule()
    {
        $this->enqueueJob('-1 minutes');
        $job = $this->driver->popJob(time());
        $this->assertTrue(is_array($job));
        $this->assertEquals('a job', $job[0]);
        $this->assertNotNull($job[1]);
    }

    protected function enqueueJob($at)
    {
        $enqueueAt = new \DateTime($at);
        $this->driver->enqueueAt($enqueueAt->getTimestamp(), 'a job');
    }
}