<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Driver\Predis;

use Predis\Client;
use TotalExpert\BernardScheduler\Driver\Predis\Driver;
use TotalExpert\BernardScheduler\Tests\Base\AbstractRedisTestCase;

class DriverTest extends AbstractRedisTestCase
{
    protected function createRedis()
    {
        return $this
            ->getMockBuilder(Client::class)
            ->setMethods([
                'del',
                'lLen',
                'lPop',
                'rPush',
                'zAdd',
                'zrangebyscore',
                'zRem'
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function createDriver()
    {
        return new Driver($this->redis);
    }
}
