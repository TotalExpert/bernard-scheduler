<?php
namespace TotalExpertInc\BernardScheduler\Normalizer;

use Bernard\Envelope;
use Normalt\Normalizer\AggregateNormalizer;
use Normalt\Normalizer\AggregateNormalizerAware;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use TotalExpertInc\BernardScheduler\Job;

final class JobNormalizer implements NormalizerInterface, DenormalizerInterface, AggregateNormalizerAware
{
    /**
     * @var AggregateNormalizer
     */
    private $aggregate;

    /**
     * @param AggregateNormalizer $aggregate
     */
    public function setAggregateNormalizer(AggregateNormalizer $aggregate)
    {
        $this->aggregate = $aggregate;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Job;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'envelope' => $this->aggregate->normalize($object->getEnvelope()),
            'enqueueAt' => $object->getEnqueueAt()->getTimestamp(),
            'queueName' => $object->getQueueName()
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Job::class;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        foreach (['enqueueAt', 'envelope'] as $required) {
            if (empty($data[$required])) {
                throw new InvalidArgumentException("Required data {$required} is empty.");
            }
        }

        /** @var \Bernard\Envelope $envelope **/
        $envelope = $this->aggregate->denormalize($data['envelope'], Envelope::class);

        $job = new Job(
            $envelope->getMessage(),
            new \DateTime("@{$data['enqueueAt']}"),
            $data['queueName'] ?: null
        );

        $this->forceEnvelope($job, $envelope);

        return $job;
    }

    /**
     * @param Job $job
     * @param Envelope $envelope
     */
    private function forceEnvelope(Job $job, Envelope $envelope)
    {
        try {
            $reflection = new \ReflectionProperty($job, 'envelope');
        } catch (\ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $reflection->setAccessible(true);
        $reflection->setValue($job, $envelope);
    }
}
