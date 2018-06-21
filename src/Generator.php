<?php
namespace TotalExpert\BernardScheduler;

use Bernard\Producer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TotalExpert\BernardScheduler\Event\BernardSchedulerEvents;
use TotalExpert\BernardScheduler\Event\ErrorEvent;
use TotalExpert\BernardScheduler\Event\JobEvent;
use TotalExpert\BernardScheduler\Event\PingEvent;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;

class Generator
{
    /**
     * @var Producer
     */
    protected $producer;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var bool
     */
    protected $shutdown = false;

    /**
     * @var bool
     */
    protected $configured = false;

    /**
     * @var array
     */
    protected $options = [
        'max-runtime' => PHP_INT_MAX,
        'max-messages' => null,
        'stop-when-empty' => false,
        'stop-on-error' => false,
    ];

    /**
     * Generator constructor.
     * @param Producer $producer
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        Producer $producer,
        EventDispatcherInterface $eventDispatcher
    ){
        $this->producer = $producer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Starts an infinite loop calling tick();
     * @param ScheduleInterface $schedule
     * @param $options
     */
    public function run(ScheduleInterface $schedule, array $options = [])
    {
        declare(ticks=1);

        $this->bind();

        while ($this->tick($schedule, $options)) {
            //No OP
        }
    }

    /**
     * Setup signal handlers for unix signals.
     */
    private function bind()
    {
        pcntl_signal(SIGTERM, [$this, 'shutdown']);
        pcntl_signal(SIGQUIT, [$this, 'shutdown']);
        pcntl_signal(SIGINT, [$this, 'shutdown']);
    }

    /**
     * Mark for shutdown
     */
    public function shutdown()
    {
        $this->shutdown = true;
    }

    /**
     * @param ScheduleInterface $schedule
     * @param array $options
     * @return bool
     */
    public function tick(ScheduleInterface $schedule, array $options)
    {
        $this->configure($options);

        if ($this->shutdown) {
            return false;
        }

        if (microtime(true) > $this->options['max-runtime']) {
            return false;
        }

        $this->eventDispatcher->dispatch(BernardSchedulerEvents::PING, new PingEvent());

        $job = $schedule->dequeue(new \DateTime());

        if (!$job) {
            return !$this->options['stop-when-empty'];
        }

        $this->generate($job, $schedule);

        if (null === $this->options['max-messages']) {
            return true;
        }

        return (bool) --$this->options['max-messages'];
    }

    /**
     * @param Job $job
     * @param ScheduleInterface $schedule
     */
    protected function generate(Job $job, ScheduleInterface $schedule)
    {
        try {
            $this->eventDispatcher->dispatch(BernardSchedulerEvents::GENERATE, new JobEvent($job));

            $this->producer->produce(
                $job->getEnvelope()->getMessage(),
                $job->getQueueName()
            );

            $schedule->cleanup($job);

            $this->eventDispatcher->dispatch(BernardSchedulerEvents::CLEANUP, new JobEvent($job));
        } catch (\Exception $exception) {
            $this->handleError($job, $exception);
        } catch (\Throwable $error) {
            $this->handleError($job, $error);
        }
    }

    /**
     * @param array $options
     * @return void
     */
    protected function configure(array $options)
    {
        if ($this->configured) {
            return;
        }

        $this->options = array_filter($options) + $this->options;
        $this->options['max-runtime'] += microtime(true);
        $this->configured = true;
    }

    /**
     * @param Job $job
     * @param $error
     */
    protected function handleError(Job $job, $error)
    {
        $this->eventDispatcher->dispatch(BernardSchedulerEvents::ERROR, new ErrorEvent($job, $error));

        if ($this->options['stop-on-error']) {
            throw $error;
        }
    }
}
