<?php
namespace TotalExpert\BernardScheduler\Tests\Integration\Driver\PhpRedis;

use TotalExpert\BernardScheduler\Driver\PhpRedis\Driver;
use TotalExpert\BernardScheduler\Tests\Base\AbstractDriverTestCase;

class PhpRedisTest extends AbstractDriverTestCase
{
    /**
     * @var \Redis
     */
    protected $redis;

    protected function tearDown()
    {
        parent::tearDown();

        $this->redis->flushAll();
    }

    protected function createDriver()
    {
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6379);

        return new Driver($this->redis);
    }
}
