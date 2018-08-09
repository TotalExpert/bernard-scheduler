<?php
namespace TotalExpert\BernardScheduler\Event;

use Symfony\Component\EventDispatcher\Event;
use TotalExpert\BernardScheduler\Job;

class JobEvent extends Event
{
    /**
     * @var Job
     */
    protected $job;

    /**
     * JobEvent constructor.
     * @param Job $job
     */
    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
