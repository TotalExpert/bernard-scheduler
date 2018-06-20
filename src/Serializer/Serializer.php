<?php
namespace TotalExpert\BernardScheduler\Serializer;

use Bernard\Normalizer\EnvelopeNormalizer;
use Bernard\Normalizer\PlainMessageNormalizer;
use Normalt\Normalizer\AggregateNormalizer;
use TotalExpert\BernardScheduler\Job;
use TotalExpert\BernardScheduler\Normalizer\JobNormalizer;

class Serializer implements SerializerInterface
{
    /**
     * @var AggregateNormalizer
     */
    private $normalizer;

    /**
     * Serializer constructor.
     * @param AggregateNormalizer|null $normalizer
     */
    public function __construct(AggregateNormalizer $normalizer = null)
    {
        $this->normalizer = $normalizer ?: $this->createAggregateNormalizer();
    }

    /**
     * @inheritdoc
     */
    public function serialize(Job $job)
    {
        return json_encode($this->normalizer->normalize($job));
    }

    /**
     * @inheritdoc
     */
    public function unserialize($contents)
    {
        $data = json_decode($contents, true);

        return $this->normalizer->denormalize($data, Job::class);
    }

    /**
     * @return AggregateNormalizer
     */
    private function createAggregateNormalizer()
    {
        return new AggregateNormalizer([
            new JobNormalizer(),
            new EnvelopeNormalizer(),
            new PlainMessageNormalizer()
        ]);
    }
}
