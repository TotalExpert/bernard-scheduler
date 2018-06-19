<?php
namespace TotalExpertInc\BernardScheduler;

use Bernard\Message;
use TotalExpertInc\BernardScheduler\Driver\DriverInterface;
use TotalExpertInc\BernardScheduler\Serializer\SerializerInterface;

class Scheduler
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
     * Scheduler constructor.
     * @param DriverInterface $driver
     * @param SerializerInterface $serializer
     */
    public function __construct(
        DriverInterface $driver,
        SerializerInterface $serializer
    ){
        $this->driver = $driver;
        $this->serializer = $serializer;
    }

    /**
     * @param Message $message
     * @param \DateTime $dateTime
     * @param string|null $queueName
     */
    public function schedule(Message $message, \DateTime $dateTime, $queueName = null)
    {
        $job = new Job($message, $dateTime, $queueName);

        $this->driver->enqueueAt(
            $dateTime->getTimestamp(),
            $this->serializer->serialize($job)
        );
    }
}