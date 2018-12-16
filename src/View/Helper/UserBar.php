<?php
namespace Next\View\Helper;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Entity\User;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Renderer\RendererInterface;

/**
 * View helper for rendering the user bar.
 */
class UserBar extends AbstractHelper
{
    /**
     * The default partial view script.
     */
    const PARTIAL_NAME = 'common/user-bar';

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

        $links = $user ? $this->links($view, $site, $user) : [];

        $partialName = $partialName ?: self::PARTIAL_NAME;

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
        $mapLabels = [
            'item' => 'Item', // @translate
            'item-set' => 'Item set', // @translate
            'media' => 'Media', // @translate
            'page' => 'Page', // @translate
        ];
        $mapPluralLabels = [
            'item' => 'Items', // @translate
            'item-set' => 'Item sets', // @translate
            'media' => 'Media', // @translate
            'page' => 'Pages', // @translate
        ];

        if (!isset($mapLabels[$controller])) {
            return [];
        }

        $routeParams = $params->fromRoute();
        if ($controller === 'page') {
            $links[] = [
                'resource' => $controller,
                'action' => 'browse',
                'text' => $translate($mapPluralLabels[$controller]),
                'url' => $url('admin/site/slug/action', ['site-slug' => $site->slug(), 'action' => 'page']),
            ];
            $page = $view->api()->read('site_pages', ['site' => $site->id(), 'slug' => $routeParams['page-slug']])->getContent();
            if ($page->userIsAllowed('edit')) {
                $links[] = [
                    'resource' => $controller,
                    'action' => 'edit',
                    'text' => sprintf($translate('Edit %s'), $translate($mapLabels[$controller])), // @translate
                    'url' => $page->adminUrl('edit'),
                ];
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
                if ($resource->userIsAllowed('edit')) {
                    $links[] = [
                        'resource' => $controller,
                        'action' => 'edit',
                        'text' => sprintf($translate('Edit %s'), $translate($mapLabels[$controller])), // @translate
                        'url' => $resource->adminUrl('edit'),
                    ];
                } else {
                    $links[] = [
                        'resource' => $controller,
                        'action' => 'show',
                        'text' => sprintf($translate('Show %s'), $translate($mapLabels[$controller])), // @translate
                        'url' => $resource->adminUrl(),
                    ];
                }
            }
        }

        return $links;
    }
}
