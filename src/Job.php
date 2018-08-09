<?php
namespace TotalExpert\BernardScheduler;

use Bernard\Envelope;
use Bernard\Message;

final class Job
{
    /**
     * @var Envelope
     */
    private $envelope;

    /**
     * @var \DateTime
     */
    private $enqueueAt;

    /**
     * @var string|null
     */
    private $queueName;

    /**
     * Job constructor.
     * @param Message $message
     * @param \DateTime $enqueueAt
     * @param string $queueName
     */
    public function __construct(
        Message $message,
        \DateTime $enqueueAt,
        $queueName = null
    ) {
        $this->envelope = new Envelope($message);
        $this->enqueueAt = $enqueueAt;
        $this->queueName = $queueName;
    }

    /**
     * @return Envelope
     */
    public function getEnvelope()
    {
        return $this->envelope;
    }

    /**
     * @return \DateTime
     */
    public function getEnqueueAt()
    {
        return $this->enqueueAt;
    }

    /**
     * @return string|null
     */
    public function getQueueName()
    {
        return $this->queueName;
    }
}
