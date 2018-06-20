<?php
namespace TotalExpert\BernardScheduler;

use Bernard\Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TotalExpert\BernardScheduler\Event\BernardSchedulerEvents;
use TotalExpert\BernardScheduler\Event\JobEvent;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;

class Scheduler
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var ScheduleInterface
     */
    protected $schedule;

    /**
     * Scheduler constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScheduleInterface $schedule
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ScheduleInterface $schedule
    ){
        $this->eventDispatcher = $eventDispatcher;
        $this->schedule = $schedule;
    }

    /**
     * @param Message $message
     * @param \DateTime $dateTime
     * @param string|null $queueName
     */
    public function schedule(Message $message, \DateTime $dateTime, $queueName = null)
    {
        $job = new Job($message, $dateTime, $queueName);

        $this->eventDispatcher->dispatch(BernardSchedulerEvents::SCHEDULE, new JobEvent($job));

        $this->schedule->enqueue($job);
    }
}
