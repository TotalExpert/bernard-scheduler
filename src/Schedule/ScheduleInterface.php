<?php
namespace TotalExpert\BernardScheduler\Schedule;

use TotalExpert\BernardScheduler\Job;

interface ScheduleInterface
{
    /**
     * @param Job $job
     * @return void
     */
    public function enqueue(Job $job);

    /**
     * @param \DateTime $dateTime
     * @return Job|null
     */
    public function dequeue(\DateTime $dateTime);

    /**
     * @param Job $job
     * @return void
     */
    public function cleanup(Job $job);
}