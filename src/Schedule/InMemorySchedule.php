<?php
namespace TotalExpert\BernardScheduler\Schedule;

use TotalExpert\BernardScheduler\Job;

class InMemorySchedule implements ScheduleInterface
{
    /**
     * @var Job[]
     */
    protected $jobs = [];

    /**
     * @param Job $job
     */
    public function enqueue(Job $job)
    {
        $this->jobs[] = $job;
    }

    /**
     * @inheritdoc
     */
    public function dequeue(\DateTime $dateTime)
    {
        foreach ($this->jobs as $key => $job) {
            if ($job->getEnqueueAt() <= $dateTime) {
                unset($this->jobs[$key]);
                return $job;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function cleanup(Job $job)
    {
        // No op.
    }
}
