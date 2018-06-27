<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\InMemory;

use TotalExpert\BernardScheduler\Driver\InMemory\Driver;
use TotalExpert\BernardScheduler\Tests\Driver\AbstractDriverTestCase;

class DriverTest extends AbstractDriverTestCase
{
    protected function createDriver()
    {
        return new Driver();
    }
}