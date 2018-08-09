<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Driver\PhpRedis;

use TotalExpert\BernardScheduler\Driver\PhpRedis\Driver;
use TotalExpert\BernardScheduler\Tests\Base\AbstractRedisTestCase;

class DriverTest extends AbstractRedisTestCase
{
    protected function createRedis()
    {
        return $this
            ->getMockBuilder(\Redis::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function createDriver()
    {
        return new Driver($this->redis);
    }
}
