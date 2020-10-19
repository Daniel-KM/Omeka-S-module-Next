<?php declare(strict_types=1);
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
