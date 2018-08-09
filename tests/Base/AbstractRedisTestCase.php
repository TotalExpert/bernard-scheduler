<?php
namespace TotalExpert\BernardScheduler\Tests\Base;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Predis\Client;
use TotalExpert\BernardScheduler\Driver\DriverInterface;

abstract class AbstractRedisTestCase extends TestCase
{
    /**
     * @var \Redis|Client|MockObject
     */
    protected $redis;

    /**
     * @var DriverInterface
     */
    protected $driver;

    protected function setUp()
    {
        $this->redis = $this->createRedis();
        $this->driver = $this->createDriver();
    }

    /**
     * @return \Redis|Client|MockObject
     */
    abstract protected function createRedis();

    /**
     * @return DriverInterface
     */
    abstract protected function createDriver();

    public function testItEnqueuesJob()
    {
        $timestamp = time();
        $job = 'this is a job';

        $this
            ->redis
            ->expects($this->once())
            ->method('rPush')
            ->with(
                "bernard-scheduler:job-{$timestamp}",
                $job
            );

        $this
            ->redis
            ->expects($this->once())
            ->method('zAdd')
            ->with(
                'bernard-scheduler:queue',
                $timestamp,
                $timestamp
            );

        $this->driver->enqueueAt($timestamp, $job);
    }

    public function testItPopsJobWhenItHasOne()
    {
        $timestamp = time();
        $pastTimestamp = $timestamp - 1;
        $job = 'this is a job';

        $this
            ->redis
            ->expects($this->once())
            ->method('zrangebyscore')
            ->with(
                'bernard-scheduler:queue',
                '-inf',
                $timestamp,
                ['limit' => [0, 1]]
            )
            ->willReturn([$pastTimestamp]);

        $this
            ->redis
            ->expects($this->once())
            ->method('lPop')
            ->with("bernard-scheduler:job-{$pastTimestamp}")
            ->willReturn($job);

        $poppedJob = $this->driver->popJob($timestamp);

        $this->assertEquals([$job, $pastTimestamp], $poppedJob);
    }

    public function testItPopsNullArrayWhenItDoesntHaveJob()
    {
        $timestamp = time();

        $this
            ->redis
            ->expects($this->once())
            ->method('zrangebyscore')
            ->with(
                'bernard-scheduler:queue',
                '-inf',
                $timestamp,
                ['limit' => [0, 1]]
            )
            ->willReturn([]);

        $poppedJob = $this->driver->popJob($timestamp);

        $this->assertEquals([null, null], $poppedJob);
    }

    public function testItCleansUpWhenNoResultsLeftInKey()
    {
        $timestamp = time();

        $this
            ->redis
            ->expects($this->once())
            ->method('lLen')
            ->with("bernard-scheduler:job-{$timestamp}")
            ->willReturn(0);

        $this
            ->redis
            ->expects($this->once())
            ->method('del')
            ->with("bernard-scheduler:job-{$timestamp}");

        $this
            ->redis
            ->expects($this->once())
            ->method('zRem')
            ->with('bernard-scheduler:queue', $timestamp);

        $this->driver->cleanup($timestamp);
    }

    public function testItDoesntCleanupWhenResultsLeftInKey()
    {
        $timestamp = time();

        $this
            ->redis
            ->expects($this->once())
            ->method('lLen')
            ->with("bernard-scheduler:job-{$timestamp}")
            ->willReturn(1);

        $this->redis->expects($this->never())->method('del');
        $this->redis->expects($this->never())->method('zRem');

        $this->driver->cleanup($timestamp);
    }
}
