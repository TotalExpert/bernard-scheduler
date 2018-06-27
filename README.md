# Bernard Scheduler
[![Build Status](https://scrutinizer-ci.com/g/TotalExpert/bernard-scheduler/badges/build.png?b=master)](https://scrutinizer-ci.com/g/TotalExpert/bernard-scheduler/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/TotalExpert/bernard-scheduler/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/TotalExpert/bernard-scheduler/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/TotalExpert/bernard-scheduler/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/TotalExpert/bernard-scheduler/?branch=master)

This library adds a job scheduler layer on top of [bernard/bernard](https://github.com/bernardphp/bernard).  It follows
similar conventions to bernard, and can be used with bernard components, such as `Bernard\Message` and `Bernard\Producer`.
## Getting Started
### Driver
Bernard Scheduler currently supports the following drivers
* [Redis Extension](#redis-extension)
* [Predis](#predis)
* [Doctrine DBAL](#doctrine-dbal)
* [In Memory](#in-memory)

#### Redis Extension
Requires the installation of the pecl extension.
```
<?php

use TotalExpert\BernardScheduler\Driver\PhpRedis\Driver;

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);

$driver = new Driver($redis);
```

#### Predis
Requires the installation of [predis/predis](https://packagist.org/packages/predis/predis).
```
<?php

use Predis\Client;
use TotalExpert\BernardScheduler\Driver\Predis\Driver;

$predis = new Client('tcp://localhost');
$driver = new Driver($predis);
```

#### Doctrine DBAL
Requires the installation of [doctrine/dbal](https://packagist.org/packages/doctrine/dbal).
This driver requires that the correct schema is available on the connection.  The schema can be created using the
`ScheduleSchema`.
```
<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use TotalExpert\BernardScheduler\Driver\Doctrine\ScheduleSchema;

$schema = new Schema;
ScheduleSchema::create($schema);

/**
 * @var Connection $connection
 */
$sql = $schema->toSql($connection->getDatabasePlatform());

foreach ($sql as $query) {
    $connection->exec($query);
}
```
Then, the driver can be setup by assigning a connection.
```
<?php

use Doctrine\DBAL\DriverManager;
use TotalExpert\BernardScheduler\Driver\Doctrine\Driver;

$connection = DriverManager::getConnection([
    'dbname'   => 'dev',
    'user'     => 'root',
    'password' => null,
    'driver'   => 'pdo_mysql',
]);

$driver = new Driver($connection);
```

#### In Memory
An in memory driver is provided for testing purposes.
```
<?php

use TotalExpert\BernardScheduler\Driver\InMemory\Driver;

$driver = new Driver();
```

### Normalizer
The scheduler uses the same `Normalt\Normalizer\AggregateNormalizer` as bernard.  You will just need to add the
`JobNormalizer`, and it can be used with both the `Bernard\Serializer` and the `TotalExpert\BernardScheduler\Serializer\Serializer`
```
<?php

use Bernard\Normalizer\EnvelopeNormalizer;
use Bernard\Normalizer\PlainMessageNormalizer;
use Bernard\Serializer as BernardSerializer;
use Normalt\Normalizer\AggregateNormalizer;
use TotalExpert\BernardScheduler\Normalizer\JobNormalizer;
use TotalExpert\BernardScheduler\Serializer\Serializer as BernardSchedulerSerializer;

$aggregateNormalizer = new AggregateNormalizer([
    new JobNormalizer(),
    new EnvelopeNormalizer(),
    new PlainMessageNormalizer()
]);

$bernardSerializer = new BernardSerializer($aggregateNormalizer);
$bernardSchedulerSerializer = new BernardSchedulerSerializer($aggregateNormalizer);
```

## Usage
### Scheduling Messages
The `Scheduler` class can be used to schedule messages for the `Bernard\Producer` to produce at a later date.  The 
schedule method accepts a `Bernard\Message`, a `\DateTime` to queue the message at, and a queue name.  If the message 
is enqueued for the current time or in the past, the message will be sent directly to the producer, and skip 
scheduling. If the queue name parameter is null, the queue will be guessed by the producer upon producing the 
message.
````
<?php

use Bernard\Producer;
use Bernard\Message\PlainMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;
use TotalExpert\BernardScheduler\Scheduler;

/**
 * @var EventDispatcherInterface $dispatcher
 * @var Producer $producer
 * @var ScheduleInterface $schedule
 */
$scheduler = new Scheduler($dispatcher, $schedule, $producer);

$message = new PlainMessage('SendEmail', ['id' => 100]);
$enqueueAt = new \DateTime('+1 days');
$queueName = 'email';

$scheduler->schedule($message, $enqueueAt, $queueName);
````

### Generating Scheduled Messages
The `Generator` class can be used to generate messages to the producer when their scheduled 
time is no longer in the past.  The generator runs in a loop, which can be configured using the available options.
* `max-runtime` Specify the max runtime for the loop (in seconds)
* `max-messages` Specify the max number of messages to generate in a loop.
* `stop-when-empty` Stops the loop when all available messages have been generated.
* `stop-on-error` Allows exceptions to be thrown during the loop, stopping generation of further messages.
```
<?php

use Bernard\Producer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use TotalExpert\BernardScheduler\Generator;
use TotalExpert\BernardScheduler\Schedule\ScheduleInterface;

/**
 * @var EventDispatcherInterface $dispatcher
 * @var Producer $producer
 */
$generator = new Generator($producer, $dispatcher);

/**
 * @var ScheduleInterface $schedule
 */
$generator->run($schedule, ['max-messages' => 5]);
```

### Command-Line Interface
The scheduler comes with command wrappers can be used with the Symfony Console component for the `Generator` and the 
`Scheduler`.
```
<?php

use Symfony\Component\Console\Application;
use TotalExpert\BernardScheduler\Command\GenerateCommand;
use TotalExpert\BernardScheduler\Command\ScheduleCommand;
use TotalExpert\BernardScheduler\Generator;
use TotalExpert\BernardScheduler\Scheduler;

$application = new Application();

/**
 * @var Generator $generator
 */
$application->add(new GenerateCommand($generator));

/**
 * @var Scheduler $scheduler
 */
$application->add(new ScheduleCommand($scheduler));

$application->run();
```
##### Generate Command
```
/path/to/console bernard-scheduler:generate
```
##### Schedule Command
```
/path/to/console bernard-scheduler:schedule SendEmail
```