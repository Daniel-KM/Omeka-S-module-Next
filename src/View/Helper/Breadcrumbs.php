<?php
namespace Next\View\Helper;

use Zend\Navigation\Navigation;
use Zend\Navigation\Page\AbstractPage;
use Zend\Router\RouteMatch;
use Zend\View\Helper\AbstractHelper;

class Breadcrumbs extends AbstractHelper
{
    protected $defaultTemplate = 'common/breadcrumbs';

    /**
     * Prepare the breadcrumb via a partial for resources and pages.
     *
     * For pages, the output is the same than the default Omeka breadcrumbs.
     *
     * @todo Manage the case where the home page is not a page and the editor doesn't want breadcrumb on it.
     *
     * @link https://docs.laminas.dev/laminas-navigation/helpers/breadcrumbs/
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
     * - template (string) The partial to use (default: "common/breadcrumbs")
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

        // In some case, there is no vars (see ItemController for search).
        $site = $this->currentSite();
        if (!$site) {
            return '';
        }

        // To set the site slug make creation of next urls quicker internally.
        $siteSlug = $site->slug();
        $vars = $view->vars();

        $plugins = $view->getHelperPluginManager();
        $translate = $plugins->get('translate');
        $url = $plugins->get('url');
        $siteSetting = $plugins->get('siteSetting');

        $crumbsSettings = $siteSetting('next_breadcrumbs_crumbs', false);
        // The multicheckbox skips keys of unset boxes, so they are added.
        if (is_array($crumbsSettings)) {
            $crumbsSettings = array_fill_keys($crumbsSettings, true) + [
                'home' => false,
                'homepage' => false,
                'current' => false,
                'itemset' => false,
                'collections' => false,
            ];
        } else {
            // This param has never been set in site settings, so use default
            // values.
            $crumbsSettings = [];
        }

        $defaults = $crumbsSettings + [
            'home' => true,
            'homepage' => false,
            'prepend' => [],
            'current' => true,
            'itemset' => true,
            'collections' => true,
            'property_itemset' => $siteSetting('next_breadcrumbs_property_itemset'),
            'separator' => $siteSetting('next_breadcrumbs_separator', '&gt;'),
            'template' => $this->defaultTemplate,
        ];
        $options += $defaults;

        /** @var \Zend\Router\RouteMatch $routeMatch */
        $routeMatch = $site->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        $matchedRouteName = $routeMatch->getMatchedRouteName();

        // Use a standard Zend/Laminas navigation breadcrumb.
        // The crumb is built flat and converted into a hierarchical one below.
        $crumbs = [];

        if ($options['home']) {
            $crumbs[] = [
                'label' => $translate('Home'), // @translate
                'uri' => $site->siteUrl($siteSlug),
                'resource' => $site,
            ];
        }

        $prepend = $siteSetting('next_breadcrumbs_prepend', []);
        if ($prepend) {
            $crumbs = array_merge($crumbs, $prepend);
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
                        'label' => $translate('Home'),
                        'uri' => $site->siteUrl($siteSlug),
                        'resource' => $site,
                    ];
                }
                break;

            case 'site/resource':
                // Only actions "browse" and "search" are available in public.
                $action = $routeMatch->getParam('action', 'browse');
                if ($action === 'search') {
                    if ($options['collections']) {
                        $crumbs[] = [
                            'label' => $translate('Collections'),
                            'uri' => $url(
                                'site/resource',
                                ['site-slug' => $siteSlug, 'controller' => 'item-set', 'action' => 'browse']
                            ),
                            'resource' => null,
                        ];
                    }

                    $controller = $this->extractController($routeMatch);
                    $label = $this->extractLabel($controller);
                    $crumbs[] = [
                        'label' => $translate($label),
                        'uri' => $url(
                            $matchedRouteName,
                            ['site-slug' => $siteSlug, 'controller' => $controller, 'action' => 'browse']
                        ),
                        'resource' => null,
                    ];
                    if ($options['current']) {
                        $label = $translate('Search'); // @translate
                    }
                } elseif ($action === 'browse') {
                    $controller = $this->extractController($routeMatch);
                    if ($options['collections'] && $controller !== 'item-set') {
                        $crumbs[] = [
                            'label' => $translate('Collections'),
                            'uri' => $url(
                                'site/resource',
                                ['site-slug' => $siteSlug, 'controller' => 'item-set', 'action' => 'browse']
                            ),
                            'resource' => null,
                        ];
                    }

                    if ($options['current']) {
                        $label = $this->extractLabel($controller);
                        $label = $translate($label);
                    }
                } else {
                    if ($options['current']) {
                        $label = $translate('Unknown'); // @translate
                    }
                }
                break;

            case 'site/resource-id':
                /** @var \Omeka\Api\Representation\AbstractResourceEntityRepresentation $resource */
                $resource = $vars->resource;
                // In case of an exception in a block, the resource may be null.
                if (!$resource) {
                    $crumbs[] = [
                        'label' => 'Error', // @translate
                        'uri' => $view->serverUrl(true),
                        'resource' => null,
                    ];
                    break;
                }
                $type = $resource->resourceName();

                switch ($type) {
                    case 'media':
                        $item = $resource->item();
                        if ($options['itemset']) {
                            if ($options['collections']) {
                                $crumbs[] = [
                                    'label' => $translate('Collections'),
                                    'uri' => $url(
                                        'site/resource',
                                        ['site-slug' => $siteSlug, 'controller' => 'item-set', 'action' => 'browse']
                                    ),
                                    'resource' => null,
                                ];
                            }

                            $itemSet = $view->primaryItemSet($item, $site);
                            if ($itemSet) {
                                $crumbs[] = [
                                    'label' => $itemSet->displayTitle(),
                                    'uri' => $itemSet->siteUrl($siteSlug),
                                    'resource' => $itemSet,
                                ];
                            }
                        }
                        $crumbs[] = [
                            'label' => $item->displayTitle(),
                            'uri' => $item->siteUrl($siteSlug),
                            'resource' => $item,
                        ];
                        break;

                    case 'items':
                        if ($options['collections']) {
                            $crumbs[] = [
                                'label' => $translate('Collections'),
                                'uri' => $url(
                                    'site/resource',
                                    ['site-slug' => $siteSlug, 'controller' => 'item-set', 'action' => 'browse']
                                ),
                                'resource' => null,
                            ];
                        }

                        if ($options['itemset']) {
                            $itemSet = $view->primaryItemSet($resource, $site);
                            if ($itemSet) {
                                $crumbs[] = [
                                    'label' => $itemSet->displayTitle(),
                                    'uri' => $itemSet->siteUrl($siteSlug),
                                    'resource' => $itemSet,
                                ];
                            }
                        }
                        break;

                    case 'item_sets':
                    default:
                        if ($options['collections']) {
                            $crumbs[] = [
                                'label' => $translate('Collections'),
                                'uri' => $url(
                                    'site/resource',
                                    ['site-slug' => $siteSlug, 'controller' => 'item-set', 'action' => 'browse']
                                ),
                                'resource' => null,
                            ];
                        }
                        break;
                }
                if ($options['current']) {
                    $label = $resource->displayTitle();
                }
                break;

            case 'site/item-set':
                if ($options['collections']) {
                    $crumbs[] = [
                        'label' => $translate('Collections'),
                        'uri' => $url(
                            'site/resource',
                            ['site-slug' => $siteSlug, 'controller' => 'item-set', 'action' => 'browse']
                        ),
                        'resource' => null,
                    ];
                }

                if ($options['current']) {
                    $action = $routeMatch->getParam('action', 'browse');
                    // In Omeka S, item set show is a redirect to item browse
                    // with a special partial, so normally, there is no "show",
                    // except with specific redirection.
                    /** @var \Omeka\Api\Representation\ItemSetRepresentation $resource */
                    $resource = $vars->itemSet;
                    if ($resource) {
                        $label = $resource->displayTitle();
                    }
                }
                break;

            case 'site/page':
                /** @var \Omeka\Api\Representation\SitePageRepresentation $page */
                $page = $vars->page;
                // In case of an exception in a block, the page may be null.
                if (!$page) {
                    $crumbs[] = [
                        'label' => 'Error', // @translate
                        'uri' => $view->serverUrl(true),
                        'resource' => null,
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
                 * @todo Use the container directly, prepending root pages.
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
                            'label' => $active->getLabel(),
                            'uri' => $active->getHref(),
                            'resource' => $page,
                        ];
                    }

                    while ($parent = $active->getParent()) {
                        if (!$parent instanceof AbstractPage) {
                            break;
                        }

                        $parents[] = [
                            'label' => $parent->getLabel(),
                            'uri' => $parent->getHref(),
                            'resource' => null,
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
                    $label = $siteSetting('guest_dashboard_label') ?: $setting('guest_dashboard_label');
                    $crumbs[] = [
                        'label' => $label ?: $translate('Dashboard'), // @translate
                        'uri' => $url('site/guest', ['site-slug' => $siteSlug, 'action' => 'me']),
                        'resource' => null,
                    ];
                }
                // For compatibility with old module GuestUser.
                elseif ($plugins->has('guestUserWidget')) {
                    $setting = $plugins->get('setting');
                    $label = $siteSetting('guest_dashboard_label') ?: $setting('guest_dashboard_label');
                    $crumbs[] = [
                        'label' => $label ?: $translate('Dashboard'), // @translate
                        'uri' => $url('site/guest-user', ['site-slug' => $siteSlug, 'action' => 'me']),
                        'resource' => null,
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

            case 'site/guest':
            case 'site/guest/anonymous':
            // Routes "guest-user" are kept for the old module GuestUser.
            case 'site/guest-user':
            case 'site/guest-user/anonymous':
                if ($options['current']) {
                    $action = $routeMatch->getParam('action', 'me');
                    switch ($action) {
                        case 'me':
                            $setting = $plugins->get('setting');
                            $label = $translate($setting('guestuser_dashboard_label') ?: 'Dashboard'); // @translate
                            break;
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
                        default:
                            $label = $translate('User'); // @translate
                            break;
                    }
                }
                break;

            case 'site/guest/guest':
            case 'site/guest/basket':
            case 'site/guest-user/guest':
                $setting = $plugins->get('setting');
                $label = $siteSetting('guest_dashboard_label') ?: $setting('guest_dashboard_label');
                if ($matchedRouteName === 'site/guest-user/guest') {
                    $crumbs[] = [
                        'label' => $label ?: $translate('Dashboard'), // @translate
                        'uri' => $url('site/guest-user', ['site-slug' => $siteSlug]),
                        'resource' => null,
                    ];
                } else {
                    $crumbs[] = [
                        'label' => $label ?: $translate('Dashboard'), // @translate
                        'uri' => $url('site/guest', ['site-slug' => $siteSlug]),
                        'resource' => null,
                    ];
                }
                if ($options['current']) {
                    $action = $routeMatch->getParam('action', 'me');
                    switch ($action) {
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
                'label' => $label,
                'uri' => $view->serverUrl(true),
                'resource' => null,
            ];
        }

        $template = $options['template'];
        unset($options['template']);

        /** @see \Omeka\Api\Representation\SiteRepresentation::publicNav() */

        $nested = $this->nestedPages($crumbs);

        return $view->partial(
            $template,
            [
                'site' => $site,
                'breadcrumbs' => new Navigation($nested),
                'options' => $options,
                // Keep the crumbs for compatibility with old themes.
                'crumbs' => $crumbs,
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

    protected function nestedPages($flat)
    {
        $nested = [];
        $last = count($flat) - 1;
        foreach (array_values($flat) as $level => $sub) {
            if ($level === 0) {
                $nested[] = $sub;
                $current = &$nested[0];
            } else {
                $current = $sub;
            }
            $current['pages'] = [];
            // Resource should be an instance of \Zend\Permissions\Acl\Resource\ResourceInterface.
            unset($current['resource']);
            if ($level !== $last) {
                $current['pages'][] = null;
                $current = &$current['pages'][0];
            } else {
                // Active is required at least for the last page, else the
                // container won't render anything.
                $current['active'] = true;
            }
        }
        return $nested;
    }

    /**
     * Get the current site from the view.
     *
     * @return \Omeka\Api\Representation\SiteRepresentation|null
     */
    protected function currentSite()
    {
        $view = $this->getView();
        return isset($view->site)
            ? $view->site
            : $view->getHelperPluginManager()->get('Zend\View\Helper\ViewModel')->getRoot()->getVariable('site');
    }
}
