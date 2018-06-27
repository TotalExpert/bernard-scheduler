<?php
namespace TotalExpert\BernardScheduler\Tests\Driver\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\MockObject\MockObject;
use TotalExpert\BernardScheduler\Driver\Doctrine\Driver;
use TotalExpert\BernardScheduler\Driver\Doctrine\ScheduleSchema;
use TotalExpert\BernardScheduler\Tests\Driver\AbstractDriverTestCase;

class DriverTest extends AbstractDriverTestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @inheritdoc
     */
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

    public function testItRollsBackOnUpdateFailure()
    {
        /**
         * @var MockObject|Connection $connection
         */
        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getDatabasePlatform',
                'beginTransaction',
                'fetchArray',
                'update',
                'rollback',
                'commit'
            ])
            ->getMock();

        $platform = $this
            ->getMockBuilder(AbstractPlatform::class)
            ->setMethods(['getForUpdateSql'])
            ->getMockForAbstractClass();

        $platform->expects($this->once())->method('getForUpdateSql')->willReturn('FOR UPDATE');
        $connection->expects($this->once())->method('getDatabasePlatform')->willReturn($platform);
        $connection->expects($this->once())->method('fetchArray')->willReturn(['a job', '1']);
        $connection->expects($this->once())->method('update')->willThrowException(new \Exception());
        $connection->expects($this->once())->method('rollback');

        $driver = new Driver($connection);
        $driver->popJob(time());
    }
}