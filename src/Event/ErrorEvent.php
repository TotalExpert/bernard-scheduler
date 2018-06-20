<?php
namespace TotalExpert\BernardScheduler\Event;

use Symfony\Component\EventDispatcher\Event;
use TotalExpert\BernardScheduler\Job;

class ErrorEvent extends Event
{
    /**
     * @var Job
     */
    protected $job;

    /**
     * @var \Throwable|\Exception
     */
    protected $error;

    /**
     * ErrorEvent constructor.
     * @param Job $job
     * @param $error
     */
    public function __construct(Job $job, $error)
    {
        $this->job = $job;
        $this->error = $error;
    }

    /**
     * @return \Exception|\Throwable
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }
}
