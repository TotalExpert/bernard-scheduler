<?php
namespace TotalExpert\BernardScheduler\Tests\Unit\Normalizer;

use Bernard\Message\PlainMessage;
use Bernard\Normalizer\EnvelopeNormalizer;
use Bernard\Normalizer\PlainMessageNormalizer;
use Normalt\Normalizer\AggregateNormalizer;
use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Normalizer\JobNormalizer;
use TotalExpert\BernardScheduler\Tests\Base\AbstractSchedulerTestCase;

class NormalizerTest extends AbstractSchedulerTestCase
{
    /**
     * @var JobNormalizer
     */
    protected $normalizer;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->normalizer = new AggregateNormalizer([
            new JobNormalizer(),
            new EnvelopeNormalizer(),
            new PlainMessageNormalizer()
        ]);
    }

    public function testItNormalizesJob()
    {
        $queue = 'email';
        $dateTime = new \DateTime();
        $message = new PlainMessage('SendEmail', []);
        $job = new Job($message, $dateTime, $queue);

        $expected = [
            'queueName' => $queue,
            'enqueueAt' => $dateTime->getTimestamp(),
            'envelope' => [
                'class' => PlainMessage::class,
                'timestamp' => time(),
                'message' => [
                    'name' => 'SendEmail',
                    'arguments' => []
                ]
            ]
        ];

        $this->assertEquals($expected, $this->normalizer->normalize($job));
    }

    public function testItDenormalizesJob()
    {
        $queue = 'email';
        $dateTime = new \DateTime();
        $message = new PlainMessage('SendEmail', []);

        $normalized = [
            'queueName' => $queue,
            'enqueueAt' => $dateTime->getTimestamp(),
            'envelope' => [
                'class' => PlainMessage::class,
                'timestamp' => time(),
                'message' => [
                    'name' => 'SendEmail',
                    'arguments' => []
                ]
            ]
        ];

        $denormalized = $this->normalizer->denormalize($normalized, Job::class);

        $this->assertEquals($queue, $denormalized->getQueueName());
        $this->assertEquals($dateTime->getTimestamp(), $denormalized->getEnqueueAt()->getTimestamp());
        $this->assertEquals($message, $denormalized->getEnvelope()->getMessage());
    }
}
