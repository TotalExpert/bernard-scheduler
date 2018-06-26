<?php
namespace TotalExpert\BernardScheduler\Driver\Doctrine;

use TotalExpert\BernardScheduler\Driver\DriverInterface;
use Doctrine\DBAL\Connection;

class Driver implements DriverInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Driver constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param int $timestamp
     * @param string $job
     * @throws \Doctrine\DBAL\DBALException
     */
    public function enqueueAt($timestamp, $job)
    {
        $types = ['integer', 'datetime', 'string'];

        $data = [
            'enqueue_at' => $timestamp,
            'created_at' => new \DateTime(),
            'job' => $job,
        ];

        $this->connection->insert('bernard_schedule', $data, $types);
    }

    /**
     * @param int $timestamp
     * @return array|null
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function popJob($timestamp)
    {
        $this->connection->beginTransaction();

        try {
            $message = $this->doPopJob($timestamp);

            $this->connection->commit();
        } catch (\Exception $e) {
            $message = null;

            $this->connection->rollback();
        }

        return $message;
    }

    /**
     * Run the query to pop the job.
     *
     * @param int $timestamp
     * @return array|null
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function doPopJob($timestamp)
    {
        $updateSql = $this->connection->getDatabasePlatform()->getForUpdateSql();

        $query = "SELECT
                    id, job
                  FROM bernard_schedule
                  WHERE enqueue_at <= :enqueue_at
                  AND visible = :visible
                  ORDER BY created_at
                  LIMIT 1 {$updateSql}";

        list($id, $job) = $this->connection->fetchArray($query, [
            'enqueue_at' => $timestamp,
            'visible' => true,
        ]);

        if ($id) {
            $this->connection->update('bernard_schedule', ['visible' => 0], compact('id'));

            return [$job, $id];
        }

        return null;
    }

    /**
     * @param mixed $receipt
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function cleanup($receipt)
    {
        $this->connection->delete('bernard_schedule', ['id' => $receipt]);
    }
}