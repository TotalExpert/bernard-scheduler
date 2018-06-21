<?php
namespace TotalExpert\BernardScheduler\Tests;

use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Job;
use Bernard\Message\PlainMessage;

abstract class AbstractSchedulerTestCase extends TestCase
{
    /**
     * @return Job
     */
    protected function createJob()
    {
        return new Job(
            $this->createMessage(),
            new \DateTime(),
            'email'
        );
    }

    /**
     * @return PlainMessage
     */
    protected function createMessage()
    {
        return new PlainMessage('SendEmail', []);
    }
}