<?php
namespace TotalExpert\BernardScheduler\Driver;

interface DriverInterface
{
    /**
     * @param int $timestamp
     * @param string $job
     */
    public function enqueueAt($timestamp, $job);

    /**
     * @param int $timestamp
     * @return array|null
     */
    public function popJob($timestamp);

    /**
     * @param mixed $receipt
     */
    public function cleanup($receipt);
}
