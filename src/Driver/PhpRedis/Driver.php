<?php
namespace TotalExpert\BernardScheduler\Driver\PhpRedis;

use TotalExpert\BernardScheduler\Driver\DriverInterface;
use \Redis;

class Driver implements DriverInterface
{
    /**
     * @var string
     */
    protected $queueKey = 'bernard-scheduler:queue';

    /**
     * @var string
     */
    protected $jobKeyPrefix = 'bernard-scheduler:job';

    /**
     * @var Redis
     */
    protected $redis;

    /**
     * Driver constructor.
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @inheritdoc
     */
    public function enqueueAt($timestamp, $job)
    {
        $this->redis->rPush($this->getJobKey($timestamp), $job);
        $this->redis->zAdd($this->queueKey, $timestamp, $timestamp);
    }

    /**
     * @inheritdoc
     */
    public function popJob($timestamp)
    {
        $pastTimestamps = $this->redis->zrangebyscore($this->queueKey, '-inf', $timestamp, [
            'limit' => [0, 1]
        ]);

        foreach ($pastTimestamps as $pastTimestamp) {
            $job = $this->redis->lPop($this->getJobKey($pastTimestamp));

            if ($job) {
                return [$job, $pastTimestamp];
            }
        }

        return [null, null];
    }

    /**
     * @inheritdoc
     */
    public function cleanup($receipt)
    {
        $key = $this->getJobKey($receipt);

        if ($this->redis->lLen($key) === 0) {
            $this->redis->del($key);
            $this->redis->zRem($this->queueKey, $receipt);
        }
    }

    /**
     * @param int $timestamp
     * @return string
     */
    protected function getJobKey($timestamp)
    {
        return "{$this->jobKeyPrefix}-{$timestamp}";
    }
}
