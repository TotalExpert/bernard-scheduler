<?php
namespace TotalExpertInc\BernardScheduler\Serializer;

use TotalExpertInc\BernardScheduler\Job;

interface SerializerInterface
{
    /**
     * @param Job $job
     * @return string
     */
    public function serialize(Job $job);

    /**
     * @param $contents
     * @return Job
     */
    public function unserialize($contents);
}