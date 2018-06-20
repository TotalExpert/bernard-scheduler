<?php
namespace TotalExpert\BernardScheduler\Command;

use Bernard\Message\PlainMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TotalExpert\BernardScheduler\Scheduler;

class ScheduleCommand extends Command
{
    /**
     * @var Scheduler
     */
    protected $scheduler;

    /**
     * ScheduleCommand constructor.
     * @param Scheduler $scheduler
     */
    public function __construct(Scheduler $scheduler)
    {
        $this->scheduler = $scheduler;

        parent::__construct('bernard-scheduler:schedule');
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Name for the message eg. "ImportUsers".')
            ->addArgument('message', InputArgument::OPTIONAL, 'JSON encoded string that is used for message properties.')
            ->addOption('datetime', 'd', InputOption::VALUE_REQUIRED, 'The datetime string to schedule the job at.', 'now')
            ->addOption('queue', 'q', InputOption::VALUE_OPTIONAL, 'Name of a queue to add this job to. By default the queue is guessed from the message name.', null);
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $enqueueAt = new \DateTime($input->getOption('datetime'));
        $queue = $input->getOption('queue');

        $messageProps = [];

        if ($input->getArgument('message')) {
            $messageProps = json_decode($input->getArgument('message'), true);

            if (json_last_error()) {
                throw new \RuntimeException('Could not decode invalid JSON ['.json_last_error().']');
            }
        }

        $message = new PlainMessage($name, $messageProps);

        $this->scheduler->schedule($message, $enqueueAt, $queue);
    }
}