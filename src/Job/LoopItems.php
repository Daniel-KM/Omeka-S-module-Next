<?php
namespace Next\Job;

use Omeka\Stdlib\Message;

/**
 * Update all items, so all the modules that uses api events are triggered.
 *
 * This job can be use as a one-time task that help to process existing items
 * when a new feature is added in a module.
 */
class LoopItems extends AbstractTask
{
    /**
     * Limit for the loop to avoid heavy sql requests.
     *
     * @var int
     */
    const BULK_LIMIT = 25;

    public function perform()
    {
        /**
         * @var \Omeka\Mvc\Controller\Plugin\Logger $logger
         * @var \Omeka\Api\Manager $api
         * @var \Doctrine\ORM\EntityManager $entityManager
         */
        $services = $this->getServiceLocator();
        $logger = $services->get('Omeka\Logger');
        $api = $services->get('Omeka\ApiManager');
        $entityManager = $services->get('Omeka\EntityManager');

        $resourceType = 'items';

        $totalToProcess = $api->search($resourceType)->getTotalResults();

        if (empty($totalToProcess)) {
            $logger->info(new Message(
                'No resource to process.' // @translate
            ));
            return;
        }

        $logger->info(new Message(
            'Processing %d resources.', // @translate
            $totalToProcess
        ));

        $offset = 0;
        $totalProcessed = 0;
        while (true) {
            /** @var \Omeka\Api\Representation\AbstractRepresentation[] $resource */
            $resources = $api
                ->search($resourceType, [
                    'limit' => self::BULK_LIMIT,
                    'offset' => $offset,
                ])
                ->getContent();
            if (empty($resources)) {
                break;
            }

            foreach ($resources as $resource) {
                if ($this->shouldStop()) {
                    $logger->warn(new Message(
                        'The job "%s" was stopped.', // @translate
                        'Loop items'
                    ));
                    break 2;
                }

                // Update the resource without any change.
                $api->update($resourceType, $resource->id(), [], [], ['isPartial' => true]);

                ++$totalProcessed;

                // Avoid memory issue.
                unset($resource);
            }

            // Avoid memory issue.
            unset($resources);
            $entityManager->clear();

            $offset += self::BULK_LIMIT;
        }

        $logger->info(new Message(
            'End of the job: %d/%d processed.', // @translate
            $totalProcessed,
            $totalToProcess
        ));
    }
}
