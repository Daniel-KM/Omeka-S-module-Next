<?php
namespace Next;

use Omeka\Module\AbstractModule;
use Zend\EventManager\Event;
use Zend\EventManager\SharedEventManagerInterface;

/**
 * Next
 *
 * Allows to use some new features of the next release of Omeka S in the stable
 * release.
 *
 * @copyright Daniel Berthereau, 2018
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $adapters = [
            \Omeka\Api\Adapter\ItemAdapter::class,
            \Omeka\Api\Adapter\ItemSetAdapter::class,
            \Omeka\Api\Adapter\MediaAdapter::class,
        ];
        foreach ($adapters as $adapter) {
            // Add the tagging and tag filters to resource search.
            $sharedEventManager->attach(
                $adapter,
                'api.search.query',
                [$this, 'apiSearchQuery']
            );
        }
    }

    public function apiSearchQuery(Event $event)
    {
        $query = $event->getParam('request')->getContent();
        if (isset($query['sort_by']) && $query['sort_by'] === 'random') {
            $qb = $event->getParam('queryBuilder');
            $qb->orderBy('RAND()');
        }
    }
}
