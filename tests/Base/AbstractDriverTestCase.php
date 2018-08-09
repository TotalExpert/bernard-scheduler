<?php
namespace TotalExpert\BernardScheduler\Tests\Base;

use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Driver\DriverInterface;

abstract class AbstractDriverTestCase extends TestCase
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $driverClass;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->driver = $this->createDriver();
        $this->driverClass = get_class($this->driver);
    }

    /**
     * @return DriverInterface
     */
    protected abstract function createDriver();

    public function testDriverClassExists()
    {
        $this->assertTrue(
            class_exists($this->driverClass),
            "{$this->driverClass} does not exist."
        );
    }


    public function testItPopsNullJobWithNoMessageInSchedule()
    {
        $job = $this->popNow();

        $this->assertJobIsExpectedArray($job);

        $this->assertNull(
            $job[0],
            "{$this->driverClass} did not pop null job with no messages scheduled."
        );
    }

    public function testItPopsNullJobWithFutureJobInSchedule()
    {
        $this->enqueueJob('+1 minutes');

        $job = $this->popNow();

        $this->assertJobIsExpectedArray($job);

        $this->assertNull(
            $job[0],
            "{$this->driverClass} did not pop null job with only a future job scheduled."
        );
    }

    public function testItPopsJobArrayWithPastJobInSchedule()
    {
        $this->enqueueJob('-1 minutes');

        $job = $this->popNow();

        $this->assertJobIsExpectedArray($job);

        $this->assertEquals(
            'a job',
            $job[0],
            "{$this->driverClass} did not return expected job string."
        );

        $this->assertNotNull(
            $job[1],
            "{$this->driverClass} did not return a non-null receipt."
        );
    }

    public function testItDoesntPopSameMessageTwice()
    {
        $this->enqueueJob('-1 seconds');

        $job = $this->popNow();
        $this->assertJobIsExpectedArray($job);

        $this->assertNotNull(
            $job[0],
            "{$this->driverClass} did not pop an expected job."

        );

        $job = $this->popNow();
        $this->assertJobIsExpectedArray($job);

        $this->assertNull(
            $job[0],
            "{$this->driverClass} popped a job that should not have popped."
        );
    }

    public function testItCleansUp()
    {
        $this->enqueueJob('-1 seconds');

        $job = $this->popNow();
        $this->assertJobIsExpectedArray($job);

        $this->driver->cleanup($job[1]);

        $job = $this->popNow();
        $this->assertJobIsExpectedArray($job);

        $this->assertNull(
            $job[0],
            "{$this->driverClass} popped a job that should not have popped."
        );
    }

    /**
     * @param string $at
     */
    protected function enqueueJob($at)
    {
        $enqueueAt = new \DateTime($at);
        $this->driver->enqueueAt($enqueueAt->getTimestamp(), 'a job');
    }

    /**
     * @return array|null
     */
    protected function popNow()
    {
        return $this->driver->popJob(time());
    }

    protected function assertJobIsExpectedArray($job)
    {
        $this->assertTrue(
            is_array($job),
            "{$this->driverClass} did not pop job as an array."
        );

        $this->assertEquals(
            2,
            count($job),
            "{$this->driverClass} did not pop job as an array with two entries."
        );
    }
}