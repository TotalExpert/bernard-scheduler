<?php
namespace TotalExpertInc\BernardScheduler\Driver;

interface DriverInterface
{
    /**
     * @param int $timestamp
     * @param string $job
     */
    public function enqueueAt($timestamp, $job);

    /**
     * @param int $timestamp|null
     * @return int
     */
    public function popTimestamp($timestamp);

    /**
     * @param int $timestamp
     * @return string
     */
    public function popJob($timestamp);

    /**
     * @param int $timestamp
     */
    public function cleanup($timestamp);
}
