<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Entity\User;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Renderer\RendererInterface;

/**
 * View helper for rendering the user bar.
 *
 * This is the copy of \Guest\View\Helper\UserBar, so improvements in core and
 * specific code of Guest can be used together with module Next too.
 */
class UserBar extends AbstractHelper
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/user-bar';

    /**
     * The default partial view script.
     */
    const PARTIAL_NAME_GUEST = 'common/user-bar-guest';

    /**
     * Render the user bar.
     *
     * @param string|null $partialName Name of view script, or a view model
     * @return string
     */
    public function __invoke($partialName = null)
    {
        $view = $this->getView();

        $site = $view->vars()->site;
        if (empty($site)) {
            return '';
        }

        $showUserBar = $view->siteSetting('show_user_bar', 0);
        if ($showUserBar == -1) {
            return '';
        }

        $user = $view->identity();
        if ($showUserBar != 1 && !$user) {
            return '';
        }

        if ($user) {
            $plugins = $view->getHelperPluginManager();
            $isGuest = $user->getRole() === 'guest'
                && ($plugins->has('guestWidget') || $plugins->has('guestUserWidget'));
            if ($isGuest) {
                $links = [];
                $partialName = $partialName ?: self::PARTIAL_NAME_GUEST;
            } else {
                $links = $this->links($view, $site, $user);
                $partialName = $partialName ?: self::PARTIAL_NAME;
            }
        } else {
            $links = [];
            $partialName = $partialName ?: self::PARTIAL_NAME;
        }

        return $view->partial(
            $partialName,
            [
                'site' => $site,
                'user' => $user,
                'links' => $links,
            ]
        );
    }

    /**
     * Prepare the list of links for the user bar.
     *
     * @param RendererInterface $view
     * @param SiteRepresentation $site
     * @param User $user
     * @return array
     */
    protected function links(RendererInterface $view, SiteRepresentation $site, User $user)
    {
        if (!$view->userIsAllowed('Omeka\Controller\Admin\Index', 'index')) {
            return [];
        }

        $links = [];
        $translate = $view->plugin('translate');
        $url = $view->plugin('url');

        $links[] = [
            'resource' => 'logo',
            'action' => 'show',
            'text' => $view->setting('installation_title', 'Omeka S'),
            'url' => $url('admin'),
        ];

        $links[] = [
            'resource' => 'site',
            'action' => 'show',
            'text' => $site->title(),
            'url' => $site->adminUrl('show'),
        ];

        // There is no default label for resources, so get it from the controller (sometime upper-cased).
        $params = $view->params();
        $controller = strtolower($params->fromRoute('__CONTROLLER__'));
        $mapPluralLabels = [
            'item' => 'Items', // @translate
            'item-set' => 'Item sets', // @translate
            'media' => 'Media', // @translate
            'page' => 'Pages', // @translate
        ];

        if (!isset($mapPluralLabels[$controller])) {
            return $links;
        }

        $routeParams = $params->fromRoute();
        if ($controller === 'page') {
            if ($routeParams['action'] === 'show') {
                $links[] = [
                    'resource' => $controller,
                    'action' => 'browse',
                    'text' => $translate($mapPluralLabels[$controller]),
                    'url' => $url('admin/site/slug/action', ['site-slug' => $site->slug(), 'action' => 'page']),
                ];
                $page = empty($routeParams['page-slug'])
                    ? $site->homepage()
                    : $view->api()->read('site_pages', ['site' => $site->id(), 'slug' => $routeParams['page-slug']])->getContent();
                if ($page && $page->userIsAllowed('edit')) {
                    $links[] = [
                        'resource' => $controller,
                        'action' => 'edit',
                        'text' => $translate('Edit'),
                        'url' => $page->adminUrl('edit'),
                    ];
                }
            }
        } else {
            $action = $params->fromRoute('action');
            $id = $params->fromRoute('id');

            // Manage the special case for item set / show, routed as item / browse.
            $itemSetId = ($controller === 'item' && $action === 'browse') ? $params->fromRoute('item-set-id') : null;
            if ($itemSetId) {
                $controller = 'item-set';
                $action = 'show';
                $id = $itemSetId;
            }

            $links[] = [
                'resource' => $controller,
                'action' => 'browse',
                'text' => $translate($mapPluralLabels[$controller]),
                'url' => $url('admin/default', ['controller' => $controller]),
            ];

            if ($id) {
                $mapResourceNames = ['item' => 'items', 'item-set' => 'item_sets', 'media' => 'media'];
                $resourceName = $mapResourceNames[$controller];
                $resource = $view->api()->read($resourceName, $id)->getContent();
                $links[] = [
                    'resource' => $controller,
                    'action' => 'show',
                    'text' => $translate('View'),
                    'url' => $resource->adminUrl(),
                ];
                if ($resource->userIsAllowed('edit')) {
                    $links[] = [
                        'resource' => $controller,
                        'action' => 'edit',
                        'text' => $translate('Edit'),
                        'url' => $resource->adminUrl('edit'),
                    ];
                }
            }
        }

        return $links;
    }
}
