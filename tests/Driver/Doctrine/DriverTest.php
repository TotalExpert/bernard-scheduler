<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use TotalExpert\BernardScheduler\Driver\Doctrine\Driver;
use TotalExpert\BernardScheduler\Driver\Doctrine\ScheduleSchema;
use TotalExpert\BernardScheduler\Tests\Driver\AbstractDriverTest;

class DriverTest extends AbstractDriverTest
{
    /**
     * @var Connection
     */
    protected $connection;

    public function setUp()
    {
        $this->connection = $this->createConnection();

        $schema = new Schema();

        ScheduleSchema::create($schema);

        array_map([$this->connection, 'executeQuery'], $schema->toSql($this->connection->getDatabasePlatform()));

        parent::setUp();
    }

    protected function createDriver()
    {
        return new Driver($this->connection);
    }

    protected function createConnection()
    {
        return DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite'
        ]);
    }

    protected function tearDown()
    {
        foreach ($this->connection->getSchemaManager()->listTables() as $table) {
            $sql = $this->connection->getDatabasePlatform()->getDropTableSQL($table->getName());
            $this->connection->exec($sql);
        }
    }
}