<?php
namespace TotalExpert\BernardScheduler\Tests\Integration\Driver\Predis;

use Predis\Client;
use TotalExpert\BernardScheduler\Driver\Predis\Driver;
use TotalExpert\BernardScheduler\Tests\Base\AbstractDriverTestCase;

class PredisTest extends AbstractDriverTestCase
{
    /**
     * @var Client
     */
    protected $client;

    protected function tearDown()
    {
        parent::tearDown();

        $this->client->flushall();
    }

    protected function createDriver()
    {
        $this->client = new Client('tcp://localhost');

        return new Driver($this->client);
    }
}