<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Serializer;

use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Serializer\Serializer;
use TotalExpert\BernardScheduler\Tests\Base\AbstractSchedulerTestCase;

class SerializerTest extends AbstractSchedulerTestCase
{
    public function testSerializeAndDeserializeAreEqual()
    {
        $serializer = new Serializer();

        $message = $this->createMessage();
        $dateTime = new \DateTime();
        $dateTime->setTimestamp(time());
        $job = new Job($message, $dateTime, 'email');

        $serialized = $serializer->serialize($job);
        $this->assertTrue(is_string($serialized));
        $this->assertNotNull(json_decode($serialized, true));

        $unserialized = $serializer->unserialize($serialized);

        $this->assertEquals($job, $unserialized);
    }
}