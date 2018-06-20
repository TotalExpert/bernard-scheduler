<?php
namespace TotalExpert\BernardScheduler\Driver\InMemory;

use TotalExpert\BernardScheduler\Driver\DriverInterface;

class Driver implements DriverInterface
{
    /**
     * @var array
     */
    protected $scheduledJobs = [];

    /**
     * @inheritdoc
     */
    public function enqueueAt($timestamp, $job)
    {
        $this->scheduledJobs[] = [
            'timestamp' => $timestamp,
            'job' => $job
        ];
    }

    /**
     * @inheritdoc
     */
    public function popJob($timestamp)
    {
        foreach ($this->scheduledJobs as $key => $scheduledJob) {
            if ($scheduledJob['timestamp'] === $timestamp) {
                return [$scheduledJob['job'], $key];
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function cleanup($receipt)
    {
        unset($this->scheduledJobs[$receipt]);
    }
}
