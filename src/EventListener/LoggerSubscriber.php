<?php
namespace TotalExpert\BernardScheduler\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use TotalExpert\BernardScheduler\Event\BernardSchedulerEvents;
use TotalExpert\BernardScheduler\Event\ErrorEvent;
use TotalExpert\BernardScheduler\Event\JobEvent;

class LoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * LoggerSubscriber constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param JobEvent $event
     */
    public function onGenerate(JobEvent $event)
    {
        $this->logger->info('[bernard-scheduler] Generating {message} to {queue}.', [
            'message' => $event->getJob()->getEnvelope()->getMessage(),
            'queue' => $event->getJob()->getQueueName()
        ]);
    }

    /**
     * @param JobEvent $event
     */
    public function onSchedule(JobEvent $event)
    {
        $this->logger->info('[bernard-scheduler] Scheduling {message} for {queue} at {enqueueAt}.', [
            'message' => $event->getJob()->getEnvelope()->getMessage(),
            'queue' => $event->getJob()->getQueueName(),
            'enqueueAt' => $event->getJob()->getEnqueueAt()->format(DATE_ATOM)
        ]);
    }

    /**
     * @param ErrorEvent $event
     */
    public function onError(ErrorEvent $event)
    {
        $this->logger->error('[bernard-scheduler] Encountered {exception} while generating {message} to {queue}.', [
            'exception' => $event->getError(),
            'message' => $event->getJob()->getEnvelope()->getMessage(),
            'queue' => $event->getJob()->getQueueName()
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            BernardSchedulerEvents::GENERATE => ['onGenerate'],
            BernardSchedulerEvents::SCHEDULE => ['onSchedule'],
            BernardSchedulerEvents::ERROR => ['onError']
        ];
    }
}
