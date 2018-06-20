<?php
namespace TotalExpert\BernardScheduler\Schedule;

use TotalExpert\BernardScheduler\Driver\DriverInterface;
use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Serializer\SerializerInterface;

class PersistentSchedule implements ScheduleInterface
{
    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var \SplObjectStorage
     */
    private $receipts;

    /**
     * PersistentSchedule constructor.
     * @param DriverInterface $driver
     * @param SerializerInterface $serializer
     */
    public function __construct(DriverInterface $driver, SerializerInterface $serializer)
    {
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->receipts = new \SplObjectStorage();
    }

    /**
     * @inheritdoc
     */
    public function enqueue(Job $job)
    {
        $this->driver->enqueueAt(
            $job->getEnqueueAt()->getTimestamp(),
            $this->serializer->serialize($job)
        );
    }

    /**
     * @inheritdoc
     */
    public function dequeue(\DateTime $dateTime)
    {
        list($serialized, $receipt) = $this->driver->popJob($dateTime->getTimestamp());

        if ($serialized) {
            $job = $this->serializer->unserialize($serialized);

            $this->receipts->attach($job, $receipt);

            return $job;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function cleanup(Job $job)
    {
        if ($this->receipts->contains($job)) {
            $this->driver->cleanup($this->receipts[$job]);

            $this->receipts->detach($job);
        }
    }
}