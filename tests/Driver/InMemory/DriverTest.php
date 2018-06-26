<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\InMemory;

use TotalExpert\BernardScheduler\Driver\InMemory\Driver;
use TotalExpert\BernardScheduler\Tests\Driver\AbstractDriverTest;

class DriverTest extends AbstractDriverTest
{
    protected function createDriver()
    {
        return new Driver();
    }
}