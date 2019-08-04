<?php
namespace Next\View\Helper;

use Zend\View\Helper\AbstractHelper;

class Breadcrumb extends AbstractHelper
{
    protected $partial = 'common/breadcrumb';

    /**
     * Prepare the breadcrumb via a partial for resources and pages.
     *
     * For pages, the output is the same than the default Omeka breadcrumbs.
     *
     * @todo Manage the case when there is no default site.
     * @todo Manage the option current for the pages and convert nav into crumbs.
     *
     * @params array $options Managed options:
     * - home (bool) Prepend home (true by default)
     * - prepend (array) A list of crumbs to insert after home
     * - current (bool) Append current resource if any (true by default; always
     *   true for pages currently)
     * - partial (string) The partial to use (default: "common/breadcrumb")
     * Options are passed to the partial too.
     * @return string The html breadcrumb.
     */
    public function __invoke(array $options = [])
    {
        /** @var \Zend\View\Renderer\PhpRenderer $view */
        $view = $this->getView();

        $vars = $view->vars();
        if (!isset($vars->site)) {
            return;
        }

        $defaults = [
            'home' => true,
            'prepend' => [],
            'current' => true,
            'nav' => null,
            'partial' => $this->partial,
        ];
        $options += $defaults;

        /** @var \Omeka\Api\Representation\SiteRepresentation $site */
        $site = $vars->site;

        // To set the site slug make creation of next urls quicker internally.
        $siteSlug = $site->slug();

        $plugins = $view->getHelperPluginManager();
        $translate = $plugins->get('translate');
        $url = $plugins->get('url');

        /** @var \Zend\Router\RouteMatch $routeMatch */
        $routeMatch = $site->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        $crumbs = [];

        if ($options['home']) {
            $crumbs[] = [
                'resource' => $site,
                'url' => $site->siteUrl($siteSlug),
                'title' => $translate('Home'),
            ];
        }

        if ($options['prepend']) {
            $crumbs = array_merge($crumbs, $options['prepend']);
        }

        switch ($matchedRouteName) {
            // Home page.
            case 'top':
            case 'site':
                if (!$options['home'] != $options['current']) {
                    $crumbs[] = [
                        'resource' => $site,
                        'url' => $site->siteUrl($siteSlug),
                        'title' => $translate('Home'),
                    ];
                }
                break;

            case 'site/resource':
                // Only actions "browse" and "search" are available in public.
                $action = $routeMatch->getParam('action', 'browse');
                if ($action === 'search') {
                    $controller = $this->extractController($routeMatch);
                    $label = $this->extractLabel($controller);
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $url(
                            $matchedRouteName,
                            ['site-slug' => $siteSlug, 'controller' => $controller, 'action' => 'browse']
                        ),
                        'title' => $translate($label),
                    ];
                    if ($options['current']) {
                        $crumbs[] = [
                            'resource' => null,
                            'url' => $url(
                                $matchedRouteName,
                                ['site-slug' => $siteSlug, 'controller' => $controller, 'action' => $action]
                            ),
                            'title' => $translate('Search'),
                        ];
                    }
                }
                // In other cases, action is browse.
                elseif ($options['current']) {
                    $controller = $this->extractController($routeMatch);
                    $label = $this->extractLabel($controller);
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $url(
                            $matchedRouteName,
                            ['site-slug' => $siteSlug, 'controller' => $controller, 'action' => 'browse']
                        ),
                        'title' => $translate($label),
                    ];
                }
                break;

            case 'site/resource-id':
                /** @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource */
                $resource = $vars->resource;
                $type = $resource->resourceName();

                switch ($type) {
                    case 'media':
                        $item = $resource->item();
                        $itemSet = array_slice($item->itemSets(), 0, 1);
                        if ($itemSet) {
                            $itemSet = reset($itemSet);
                            $crumbs[] = [
                                'resource' => $itemSet,
                                'url' => $itemSet->siteUrl($siteSlug),
                                'title' => $itemSet->displayTitle(),
                            ];
                        }
                        $crumbs[] = [
                            'resource' => $item,
                            'url' => $item->siteUrl($siteSlug),
                            'title' => $item->displayTitle(),
                        ];
                        break;

                    case 'items':
                        $itemSet = array_slice($resource->itemSets(), 0, 1);
                        if ($itemSet) {
                            $itemSet = reset($itemSet);
                            $crumbs[] = [
                                'resource' => $itemSet,
                                'url' => $itemSet->siteUrl($siteSlug),
                                'title' => $itemSet->displayTitle(),
                            ];
                        }
                        break;
                    case 'itemsets':
                        // Nothing to do.
                        break;
                }
                if ($options['current']) {
                    $crumbs[] = [
                        'resource' => $resource,
                        'url' => $resource->siteUrl($siteSlug),
                        'title' => $resource->displayTitle(),
                    ];
                }
                break;

            case 'site/item-set':
                // In Omeka S, item set show is a redirect to item browse with a
                // special partial.
                if ($options['current']) {
                    /** @var \Omeka\Api\Representation\ItemSetRepresentation $resource */
                    $resource = $vars->itemSet;
                    $crumbs[] = [
                        'resource' => $resource,
                        'url' => $resource->siteUrl($siteSlug),
                        'title' => $resource->displayTitle(),
                    ];
                }
                break;
            case 'site/page':
                $options['nav'] = $site->publicNav();
                break;
        }

        $partialName = $options['partial'];
        unset($options['partial']);
        return $view->partial(
            $partialName,
            [
                'crumbs' => $crumbs,
                'options' => $options,
            ]
        );
    }

    protected function extractController($routeMatch)
    {
        $controllers = [
            'Omeka\Controller\Site\ItemSet' => 'item-set',
            'Omeka\Controller\Site\Item' => 'item',
            'Omeka\Controller\Site\Media' => 'media',
            'item-set' => 'item-set',
            'item' => 'item',
            'media' => 'media',
        ];
        $controller = $routeMatch->getParam('controller') ?: $routeMatch->getParam('__CONTROLLER__');
        return $controllers[$controller];
    }

    protected function extractLabel($controller)
    {
        $labels = [
            'item-set' => 'Item sets', // @translate
            'item' => 'Items', // @translate
            'media' => 'Media', // @translate
        ];
        return $labels[$controller];
    }
}
