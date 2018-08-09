<?php
namespace TotalExpert\BernardScheduler;

use Bernard\Message;
use Bernard\Producer;
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
     * @var Producer
     */
    protected $producer;

    /**
     * Scheduler constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScheduleInterface $schedule
     * @param Producer $producer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ScheduleInterface $schedule,
        Producer $producer
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->schedule = $schedule;
        $this->producer = $producer;
    }

    /**
     * @param Message $message
     * @param \DateTime $dateTime
     * @param string|null $queueName
     */
    public function schedule(Message $message, \DateTime $dateTime, $queueName = null)
    {
        $dateTime > new \DateTime()
            ? $this->doSchedule($message, $dateTime, $queueName)
            : $this->producer->produce($message, $queueName);
    }

    /**
     * @param Message $message
     * @param \DateTime $dateTime
     * @param $queueName
     */
    private function doSchedule(Message $message, \DateTime $dateTime, $queueName)
    {
        $job = new Job($message, $dateTime, $queueName);

        $this->eventDispatcher->dispatch(BernardSchedulerEvents::SCHEDULE, new JobEvent($job));

        $this->schedule->enqueue($job);
    }
}
