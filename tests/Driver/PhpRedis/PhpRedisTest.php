<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\PhpRedis;

use TotalExpert\BernardScheduler\Driver\PhpRedis\Driver;
use TotalExpert\BernardScheduler\Tests\Driver\AbstractDriverTestCase;

/**
 * @group integration
 */
class PhpRedisTest extends AbstractDriverTestCase
{
    protected function createDriver()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);

        return new Driver($redis);
    }
}