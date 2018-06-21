<?php
namespace TotalExpert\BernardScheduler\Tests\EventListener;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use TotalExpert\BernardScheduler\Event\BernardSchedulerEvents;
use TotalExpert\BernardScheduler\Event\ErrorEvent;
use TotalExpert\BernardScheduler\Event\JobEvent;
use TotalExpert\BernardScheduler\EventListener\LoggerSubscriber;
use TotalExpert\BernardScheduler\Tests\AbstractSchedulerTestCase;

class LoggerSubscriberTest extends AbstractSchedulerTestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->logger = $this
            ->getMockBuilder(LoggerInterface::class)
            ->setMethods(['info', 'error'])
            ->getMockForAbstractClass();

        $this->eventDispatcher = new EventDispatcher();
        $this->eventDispatcher->addSubscriber(new LoggerSubscriber($this->logger));
    }

    public function testItLogsInfoOnGenerate()
    {
        $this->logger->expects($this->once())->method('info');
        $event = new JobEvent($this->createJob());
        $this->eventDispatcher->dispatch(BernardSchedulerEvents::GENERATE, $event);
    }

    public function testItLogsInfoOnSchedule()
    {
        $this->logger->expects($this->once())->method('info');
        $event = new JobEvent($this->createJob());
        $this->eventDispatcher->dispatch(BernardSchedulerEvents::SCHEDULE, $event);
    }

    public function testItLogsErrorOnError()
    {
        $this->logger->expects($this->once())->method('error');
        $event = new ErrorEvent($this->createJob(), new \Exception());
        $this->eventDispatcher->dispatch(BernardSchedulerEvents::ERROR, $event);
    }
}
