<?php
namespace TotalExpert\BernardScheduler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TotalExpert\BernardScheduler\Generator;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;

class GenerateCommand extends Command
{
    /**
     * @var ScheduleInterface
     */
    protected $schedule;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * GenerateCommand constructor.
     * @param ScheduleInterface $schedule
     * @param Generator $generator
     */
    public function __construct(ScheduleInterface $schedule, Generator $generator)
    {
        $this->schedule = $schedule;
        $this->generator = $generator;

        parent::__construct('bernard-scheduler:generate');
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        $this
            ->addOption('max-runtime', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the generator will run.', null)
            ->addOption('max-messages', null, InputOption::VALUE_OPTIONAL, 'Maximum number of jobs that should be generated.', null)
            ->addOption('stop-when-empty', null, InputOption::VALUE_NONE, 'Stop generator when schedule is empty.', null)
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop generator when an error occurs.', null);
    }

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generator->run($this->schedule, $input->getOptions());
    }
}