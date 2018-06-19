<?php
namespace TotalExpertInc\BernardScheduler;

use Bernard\Producer;
use TotalExpertInc\BernardScheduler\Driver\DriverInterface;
use TotalExpertInc\BernardScheduler\Serializer\SerializerInterface;

class Generator
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
     * @var Producer
     */
    private $producer;

    /**
     * @var bool
     */
    private $shutdown = false;

    /**
     * Generator constructor.
     * @param DriverInterface $driver
     * @param SerializerInterface $serializer
     * @param Producer $producer
     */
    public function __construct(
        DriverInterface $driver,
        SerializerInterface $serializer,
        Producer $producer
    ){
        $this->driver = $driver;
        $this->serializer = $serializer;
        $this->producer = $producer;
    }

    /**
     * Starts an infinite loop calling tick();
     *
     * @param int $interval
     */
    public function run($interval)
    {
        $this->bind();

        while ($this->tick()) {
            sleep($interval);
        }
    }

    /**
     * Setup signal handlers for unix signals.
     */
    private function bind()
    {
        pcntl_signal(SIGTERM, array($this, 'shutdown'));
        pcntl_signal(SIGQUIT, array($this, 'shutdown'));
        pcntl_signal(SIGINT, array($this, 'shutdown'));
    }

    /**
     * Mark for shutdown
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * @return bool
     */
    private function tick()
    {
        if ($this->shutdown) {
            return false;
        }

        $timestamp = $this->driver->popTimestamp(time());

        if ($timestamp === null) {
            return true;
        }

        while ($serializedJob = $this->driver->popJob($timestamp)) {
            $job = $this->serializer->unserialize($serializedJob);

            $this->producer->produce(
                $job->getEnvelope()->getMessage(),
                $job->getQueueName()
            );
        }

        $this->driver->cleanup($timestamp);

        return true;
    }
}
