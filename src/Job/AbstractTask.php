<?php
namespace Next\Job;

use Omeka\Job\AbstractJob;

abstract class AbstractTask extends AbstractJob
{
    public function shouldStop()
    {
        return $this->job->getId()
            ? parent::shouldStop()
            : false;
    }
}
