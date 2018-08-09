<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;

class SqlLiteDriverTest extends AbstractDoctrineDriverTestCase
{
    protected function createConnection()
    {
        return DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite'
        ]);
    }
}