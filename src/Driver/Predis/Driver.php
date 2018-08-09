<?php
namespace TotalExpert\BernardScheduler\Driver\Predis;

use Predis\ClientInterface;
use TotalExpert\BernardScheduler\Driver\PhpRedis\Driver as PhpRedisDriver;

class Driver extends PhpRedisDriver
{
    /**
     * Driver constructor.
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }
}
