<?php declare(strict_types=1);
namespace Next\Mvc;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;

class MvcListeners extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        $this->listeners[] = $events->attach(
            MvcEvent::EVENT_ROUTE,
            [$this, 'handleItemSetShow'],
            -20
        );
    }

    /**
     * Set default order of items in public item set show.
     *
     * @param MvcEvent $event
     */
    public function handleItemSetShow(MvcEvent $event): void
    {
        $routeMatch = $event->getRouteMatch();
        $matchedRouteName = $routeMatch->getMatchedRouteName();
        if ('site/item-set' !== $matchedRouteName) {
            return;
        }

        $services = $event->getApplication()->getServiceManager();
        if (!$services->get('Omeka\Status')->isSiteRequest()) {
            return;
        }

        // Don't process if an order is set.
        $request = $event->getRequest();
        /** @var \Laminas\Stdlib\Parameters $query */
        $query = $request->getQuery();
        if (!empty($query['sort_by'])) {
            return;
        }

        $siteSettings = $services->get('Omeka\Settings\Site');
        $orders = $siteSettings->get('next_items_order_for_itemsets');
        if (empty($orders)) {
            return;
        }

        $itemSetId = $routeMatch->getParam('item-set-id');

        // For performance, the check uses a single strpos.
        $specificOrder = null;
        $idString = ',' . $itemSetId . ',';
        foreach ($orders as $ids => $order) {
            if (strpos(',' . $ids . ',', $idString) !== false) {
                $specificOrder = $order;
                break;
            }
        }

        // Check the default order, if any.
        if (is_null($specificOrder)) {
            if (!isset($orders[0])) {
                return;
            }
            $specificOrder = $orders[0];
        }

        // Set the specific order.
        $query['sort_by'] = $specificOrder['sort_by'];
        $query['sort_order'] = $specificOrder['sort_order'];
    }
}
