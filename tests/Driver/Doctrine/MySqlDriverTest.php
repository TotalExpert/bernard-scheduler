<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;

/**
 * @group integration
 */
class MySqlDriverTest extends AbstractDoctrineDriverTestCase
{
    protected function createConnection()
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_mysql',
            'host' => '127.0.0.1',
            'user' => 'root',
            'dbname' => 'bernard_scheduler_test',
            'password' => ''
        ]);
    }
}
