<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Driver\InMemory;

use TotalExpert\BernardScheduler\Driver\InMemory\Driver;
use TotalExpert\BernardScheduler\Tests\Base\AbstractDriverTestCase;

class DriverTest extends AbstractDriverTestCase
{
    protected function createDriver()
    {
        return new Driver();
    }
}
