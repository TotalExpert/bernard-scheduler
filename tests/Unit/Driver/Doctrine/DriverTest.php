<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;
use TotalExpert\BernardScheduler\Tests\Base\AbstractDoctrineDriverTestCase;

class DriverTest extends AbstractDoctrineDriverTestCase
{
    protected function createConnection()
    {
        return DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite'
        ]);
    }
}
