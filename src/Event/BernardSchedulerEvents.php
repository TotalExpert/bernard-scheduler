<?php
namespace TotalExpert\BernardScheduler\Event;

class BernardSchedulerEvents
{
    /**
     * Occurs when the generator polls for jobs to generate.
     */
    const PING = 'bernard-scheduler.ping';

    /**
     * Occurs when a job is generated from the schedule.
     */
    const GENERATE = 'bernard-scheduler.generate';

    /**
     * Occurs when a scheduled job is cleaned up.
     */
    const CLEANUP = 'bernard-scheduler.cleanup';

    /**
     * Occurs when a new job is scheduled.
     */
    const SCHEDULE = 'bernard-scheduler.schedule';

    /**
     * Occurs when an error is thrown while generating a job.
     */
    const ERROR = 'bernard-scheduler.error';
}
