<?php
namespace Next\View\Helper;

use Zend\Navigation\Page\AbstractPage;
use Zend\Router\RouteMatch;
use Zend\View\Helper\AbstractHelper;

class Breadcrumbs extends AbstractHelper
{
    protected $partial = 'common/breadcrumbs';

    /**
     * Prepare the breadcrumb via a partial for resources and pages.
     *
     * For pages, the output is the same than the default Omeka breadcrumbs.
     *
     * @todo Manage the case when there is no default site.
     * @todo Manage the case where the home page is not a page and the editor doesn't want breadcrumb on it.
     * @todo Use the standard Breadcrumbs helper instead of conversion into a partial? It will rights and translator. See \Zend\View\Helper\Navigation\Breadcrumbs
     *
     * @params array $options Managed options:
     * - home (bool) Prepend home (true by default)
     * - homepage (bool) Display the breadcrumbs on the home page (false by
     *   default)
     * - prepend (array) A list of crumbs to insert after home
     * - current (bool) Append current resource if any (true by default; always
     *   true for pages currently)
     * - itemset (bool) Insert the first item set as crumb for an item (true by
     *   default)
     * - property_itemset (string) Property where is set the first parent item
     *   set of an item when they are multiple.
     * - separator (string) Separator, escaped for html (default is "&gt;")
     * - partial (string) The partial to use (default: "common/breadcrumbs")
     * Options are passed to the partial too.
     * @return string The html breadcrumb.
     */
    public function __invoke(array $options = [])
    {
        /**
         * @var \Zend\View\Renderer\PhpRenderer $view
         * @var \Omeka\Api\Representation\SiteRepresentation $site
         */
        $view = $this->getView();

        $vars = $view->vars();
        if (isset($vars->site)) {
            $site = $vars->site;
        } else {
            // In some case, there is no vars (see ItemController for search).
            $site = $view->currentSite();
            if (!$site) {
                return '';
            }
        }

        // To set the site slug make creation of next urls quicker internally.
        $siteSlug = $site->slug();

        $plugins = $view->getHelperPluginManager();
        $translate = $plugins->get('translate');
        $url = $plugins->get('url');
        $siteSetting = $plugins->get('siteSetting');

        $crumbsSettings = $siteSetting('next_breadcrumbs_crumbs');
        // The multicheckbox skips keys of unset boxes, so they are added.
        if (is_array($crumbsSettings)) {
            $crumbsSettings = array_fill_keys($crumbsSettings, true) + [
                'home' => false,
                'homepage' => false,
                'current' => false,
                'itemset' => false,
            ];
        }

        $defaults = $crumbsSettings + [
            'home' => true,
            'homepage' => false,
            'prepend' => [],
            'current' => true,
            'itemset' => true,
            'property_itemset' => $siteSetting('next_breadcrumbs_property_itemset'),
            'separator' => $siteSetting('next_breadcrumbs_separator', '&gt;'),
            'partial' => $this->partial,
        ];
        $options += $defaults;

        /** @var \Zend\Router\RouteMatch $routeMatch */
        $routeMatch = $site->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        $crumbs = [];

        if ($options['home']) {
            $crumbs[] = [
                'resource' => $site,
                'url' => $site->siteUrl($siteSlug),
                'label' => $translate('Home'),
            ];
        }

        if ($options['prepend']) {
            $crumbs = array_merge($crumbs, $options['prepend']);
        }

        $label = null;

        switch ($matchedRouteName) {
            // Home page, without default site or defined home page.
            case 'top':
            case 'site':
                if (!$options['homepage']) {
                    return '';
                }

                if (!$options['home'] != $options['current']) {
                    $crumbs[] = [
                        'resource' => $site,
                        'url' => $site->siteUrl($siteSlug),
                        'label' => $translate('Home'),
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
                        'label' => $translate($label),
                    ];
                    if ($options['current']) {
                        $label = $translate('Search');
                    }
                }
                // In other cases, action is browse or unknown.
                else {
                    $controller = $this->extractController($routeMatch);
                    if ($controller === 'browse') {
                        if ($options['current']) {
                            $label = $this->extractLabel($controller);
                            $label = $translate($label);
                        }
                    } else {
                        if ($options['current']) {
                            $label = $translate('Unknown'); // @translate
                        }
                    }
                }
                break;

            case 'site/resource-id':
                /** @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource */
                $resource = $vars->resource;
                // In case of an exception in a block, the resource may be null.
                if (!$resource) {
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $view->serverUrl(true),
                        'label' => 'Error', // @translate
                    ];
                    break;
                }
                $type = $resource->resourceName();

                switch ($type) {
                    case 'media':
                        $item = $resource->item();
                        if ($options['itemset']) {
                            $itemSet = $view->primaryItemSet($item, $site);
                            if ($itemSet) {
                                $crumbs[] = [
                                    'resource' => $itemSet,
                                    'url' => $itemSet->siteUrl($siteSlug),
                                    'label' => $itemSet->displayTitle(),
                                ];
                            }
                        }
                        $crumbs[] = [
                            'resource' => $item,
                            'url' => $item->siteUrl($siteSlug),
                            'label' => $item->displayTitle(),
                        ];
                        break;

                    case 'items':
                        if ($options['itemset']) {
                            $itemSet = $view->primaryItemSet($resource, $site);
                            if ($itemSet) {
                                $crumbs[] = [
                                    'resource' => $itemSet,
                                    'url' => $itemSet->siteUrl($siteSlug),
                                    'label' => $itemSet->displayTitle(),
                                ];
                            }
                        }
                        break;

                    case 'itemsets':
                        // Nothing to do.
                        break;
                }
                if ($options['current']) {
                    $label = $resource->displayTitle();
                }
                break;

            case 'site/item-set':
                // In Omeka S, item set show is a redirect to item browse with a
                // special partial.
                if ($options['current']) {
                    /** @var \Omeka\Api\Representation\ItemSetRepresentation $resource */
                    $resource = $vars->itemSet;
                    $label = $resource->displayTitle();
                }
                break;

            case 'site/page':
                /** @var \Omeka\Api\Representation\SitePageRepresentation $page */
                $page = $vars->page;
                // In case of an exception in a block, the page may be null.
                if (!$page) {
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $view->serverUrl(true),
                        'label' => 'Error', // @translate
                    ];
                    break;
                }
                if (!$options['homepage']) {
                    $homepage = version_compare(\Omeka\Module::VERSION, '1.4', '>=')
                        ? $site->homepage()
                        : null;
                    if (!$homepage) {
                        $linkedPages = $site->linkedPages();
                        $homepage = $linkedPages ? current($linkedPages) : null;
                    }
                    // This is the home page and home page is not wanted.
                    if ($homepage && $homepage->id() === $page->id()) {
                        return '';
                    }
                }

                // Find the page inside navigation. By construction, this is the
                // active page of the navigation. If not in navigation, it's a
                // root page.

                /**
                 * @var \Zend\View\Helper\Navigation $nav
                 * @var \Zend\Navigation\Navigation $container
                 * @see \Zend\View\Helper\Navigation\Breadcrumbs::renderPartialModel()
                 */
                $nav = $site->publicNav();
                $container = $nav->getContainer();
                $active = $nav->findActive($container);
                if ($active) {
                    // This process uses the short title in the navigation (label).
                    $active = $active['page'];
                    $parents = [];
                    if ($options['current']) {
                        $parents[] = [
                            'resource' => $page,
                            'url' => $active->getHref(),
                            'label' => $active->getLabel(),
                        ];
                    }

                    while ($parent = $active->getParent()) {
                        if (!$parent instanceof AbstractPage) {
                            break;
                        }

                        $parents[] = [
                            'resource' => null,
                            'url' => $parent->getHref(),
                            'label' => $parent->getLabel(),
                        ];

                        // Break if at the root of the given container.
                        if ($parent === $container) {
                            break;
                        }

                        $active = $parent;
                    }
                    $parents = array_reverse($parents);
                    $crumbs = array_merge($crumbs, $parents);
                }
                // The page is not in the navigation menu, so it's a root page.
                elseif ($options['current']) {
                    $label = $page->title();
                }
                break;


            // For compatibility with old version of module Basket.
            case 'site/basket':
                if ($plugins->has('guestWidget')) {
                    $setting = $plugins->get('setting');
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $url('site/guest', ['site-slug' => $siteSlug, 'action' => 'me']),
                        'label' => $translate($setting('guest_dashboard_label') ?: 'Dashboard'), // @translate
                    ];
                }
                // For compatibility with old module GuestUser.
                elseif ($plugins->has('guestUserWidget')) {
                    $setting = $plugins->get('setting');
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $url('site/guest-user', ['site-slug' => $siteSlug, 'action' => 'me']),
                        'label' => $translate($setting('guestuser_dashboard_label') ?: 'Dashboard'), // @translate
                    ];
                }
                if ($options['current']) {
                    $label = $translate('Basket'); // @translate
                }
                break;

            case 'site/collecting':
                // TODO Add the page where the collecting form is.
                // Action can be "submit", "success" or "item-show".
                if ($options['current']) {
                    $label = $translate('Collecting'); // @translate
                }
                break;

            // For compatibility with old module GuestUser.
            case 'site/guest-user':
            case 'site/guest-user/anonymous':
            case 'site/guest-user/guest':
                $isOldGuestuser = true;
                $setting = $plugins->get('setting');
                $crumbs[] = [
                    'resource' => null,
                    'url' => $url('site/guest-user', ['site-slug' => $siteSlug]),
                    'label' => $translate($setting('guestuser_dashboard_label') ?: 'Dashboard'), // @translate
                ];
                // No break.
            case 'site/guest':
            case 'site/guest/anonymous':
            case 'site/guest/guest':
            case 'site/guest/basket':
                if (empty($isOldGuestuser)) {
                    $setting = $plugins->get('setting');
                    $crumbs[] = [
                        'resource' => null,
                        'url' => $url('site/guest', ['site-slug' => $siteSlug]),
                        'label' => $translate($setting('guest_dashboard_label') ?: 'Dashboard'), // @translate
                    ];
                }

                if ($options['current']) {
                    $action = $routeMatch->getParam('action', 'me');
                    switch ($action) {
                        case 'login':
                            $label = $translate('Login'); // @translate
                            break;
                        case 'register':
                            $label = $translate('Register'); // @translate
                            break;
                        case 'auth-error':
                            $label = $translate('Authentication error'); // @translate
                            break;
                        case 'forgot-password':
                            $label = $translate('Forgot password'); // @translate
                            break;
                        case 'confirm':
                            $label = $translate('Confirm'); // @translate
                            break;
                        case 'confirm-email':
                            $label = $translate('Confirm email'); // @translate
                            break;
                        case 'me':
                            $label = $translate('Me'); // @translate
                            break;
                        case 'logout':
                            $label = $translate('Logout'); // @translate
                            break;
                        case 'update-account':
                            $label = $translate('Update account'); // @translate
                            break;
                        case 'update-email':
                            $label = $translate('Update email'); // @translate
                            break;
                        case 'accept-terms':
                            $label = $translate('Accept terms'); // @translate
                            break;
                        case 'basket':
                            $label = $translate('Basket'); // @translate
                            break;
                        default:
                            $label = $translate('User'); // @translate
                            break;
                    }
                }
                break;

            case strpos($matchedRouteName, 'search-page-') === 0:
                if ($options['current']) {
                    $label = $translate('Search'); // @translate
                }
                break;

            default:
                if ($options['current']) {
                    $label = $translate('Current page'); // @translate
                }
                break;
        }

        if ($options['current'] && isset($label)) {
            $crumbs[] = [
                'resource' => null,
                'url' => $view->serverUrl(true),
                'label' => $label,
            ];
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

    protected function extractController(RouteMatch $routeMatch)
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
        return isset($controllers[$controller])
            ? $controllers[$controller]
            : $controller;
    }

    protected function extractLabel($controller)
    {
        $labels = [
            'item-set' => 'Item sets', // @translate
            'item' => 'Items', // @translate
            'media' => 'Media', // @translate
        ];
        return isset($labels[$controller])
            ? $labels[$controller]
            : $controller;
    }
}
