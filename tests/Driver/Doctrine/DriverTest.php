<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use TotalExpert\BernardScheduler\Driver\Doctrine\Driver;
use TotalExpert\BernardScheduler\Driver\Doctrine\ScheduleSchema;

class DriverTest extends TestCase
{

    /**
     * @var Driver
     */
    protected $driver;

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

        $this->driver = new Driver($this->connection);
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

    public function testItPopsNullJobWithNoMessageInSchedule()
    {
        $this->assertNull($this->driver->popJob(time()));
    }

    public function testItPopsNullJobWithFutureJobInSchedule()
    {
        $this->enqueueJob('+1 minutes');
        $this->assertNull($this->driver->popJob(time()));
    }

    public function testItPopsJobArrayWithPastJobInSchedule()
    {
        $this->enqueueJob('-1 minutes');
        $job = $this->driver->popJob(time());
        $this->assertTrue(is_array($job));
        $this->assertEquals('a job', $job[0]);
        $this->assertEquals(1, $job[1]);
    }

    protected function enqueueJob($at)
    {
        $enqueueAt = new \DateTime($at);
        $this->driver->enqueueAt($enqueueAt->getTimestamp(), 'a job');
    }
}