<?php declare(strict_types=1);

namespace Next;

if (!class_exists(\Generic\AbstractModule::class)) {
    require file_exists(dirname(__DIR__) . '/Generic/AbstractModule.php')
        ? dirname(__DIR__) . '/Generic/AbstractModule.php'
        : __DIR__ . '/src/Generic/AbstractModule.php';
}

use Generic\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Session\Container;
use Omeka\Module\Exception\ModuleCannotInstallException;

/**
 * Next
 *
 * Bring together various features too small to be a full module; may be
 * integrated in the next release of Omeka S, or not.
 *
 * @copyright Daniel Berthereau, 2018-2023
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2.1-en.txt
 */
class Module extends AbstractModule
{
    const NAMESPACE = __NAMESPACE__;

    protected function preInstall(): void
    {
        $services = $this->getServiceLocator();
        $translator = $services->get('MvcTranslator');

        /** @var \Omeka\Module\Manager $moduleManager */
        $moduleManager = $services->get('Omeka\ModuleManager');
        $advancedSearch = $moduleManager->getModule('AdvancedSearch');
        if ($advancedSearch) {
            $advancedSearchVersion = $advancedSearch->getIni('version');
            if (version_compare($advancedSearchVersion, '3.3.6.16', '<')) {
                $message = new \Omeka\Stdlib\Message(
                    $translator->translate('This module requires module "%s" version "%s" or greater.'), // @translate
                    'Advanced Search', '3.3.6.16'
                );
                throw new ModuleCannotInstallException((string) $message);
            }
        }
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager): void
    {
        // Manage buttons in admin resources.
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Item',
            'view.layout',
            [$this, 'handleViewLayoutResource']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\ItemSet',
            'view.layout',
            [$this, 'handleViewLayoutResource']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.layout',
            [$this, 'handleViewLayoutResource']
        );

        // Manage prev/next.
        $controllers = [
            'Omeka\Controller\Admin\Item',
            // 'Omeka\Controller\Admin\ItemSet',
            // 'Omeka\Controller\Admin\Media',
            'Omeka\Controller\Site\Item',
            // 'Omeka\Controller\Site\ItemSet',
            // 'Omeka\Controller\Site\Media',
        ];
        foreach ($controllers as $controller) {
            $sharedEventManager->attach(
                $controller,
                'view.browse.before',
                [$this, 'handleViewBrowse']
            );
        }
        $sharedEventManager->attach(
            \AdvancedSearch\Controller\IndexController::class,
            'view.layout',
            [$this, 'handleViewBrowse']
        );
        $sharedEventManager->attach(
            \Search\Controller\IndexController::class,
            'view.layout',
            [$this, 'handleViewBrowse']
        );

        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.show.sidebar',
            [$this, 'handleViewShowSidebarMedia']
        );
        $sharedEventManager->attach(
            'Omeka\Controller\Admin\Media',
            'view.details',
            [$this, 'handleViewShowSidebarMedia']
        );

        // Main settings.
        $sharedEventManager->attach(
            \Omeka\Form\SettingForm::class,
            'form.add_elements',
            [$this, 'handleMainSettings']
        );
        // Site settings.
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_elements',
            [$this, 'handleSiteSettings']
        );
        $sharedEventManager->attach(
            \Omeka\Form\SiteSettingsForm::class,
            'form.add_input_filters',
            [$this, 'handleSiteSettingsFilters']
        );
    }

    public function handleViewLayoutResource(Event $event): void
    {
        $view = $event->getTarget();
        $params = $view->params()->fromRoute();
        $action = $params['action'] ?? 'browse';
        if (!in_array($action, ['show'])) {
            return;
        }

        $controller = $params['__CONTROLLER__'] ?? $params['controller'] ?? '';
        $controllers = [
            'item' => 'items',
            'item-set' => 'item_sets',
            'media' => 'media',
            'Omeka\Controller\Admin\Item' => 'items',
            'Omeka\Controller\Admin\ItemSet' => 'item_sets',
            'Omeka\Controller\Admin\Media' => 'media',
        ];
        if (!isset($controllers[$controller])) {
            return;
        }

        if ($action === 'show') {
            // The resource is not available in the main view.
            $id = isset($params['id']) ? (int) $params['id'] : 0;
            if (!$id) {
                return;
            }
            $resource = $view->api()->read($controllers[$controller], ['id' => $id], ['initialize' => false])->getContent();
            $url = $view->publicResourceUrl($resource);
            if (!$url) {
                return;
            }
            $linkPublicView = $view->hyperlink(
                $view->translate('Public view'), // @translate
                $url,
                ['class' => 'button', 'target' => '_blank']
            );
            $linkBrowseView = $view->browsePreviousNext($resource);
            $html = preg_replace(
                '~<div id="page-actions">(.*?)</div>~s',
                '<div id="page-actions">' . $linkPublicView . ' $1 ' . $linkBrowseView . '</div>',
                $view->content,
                1
            );
            $view->vars()->offsetSet('content', $html);
        }
    }

    public function handleViewShowSidebarMedia(Event $event): void
    {
        $view = $event->getTarget();
        $resource = $view->resource;
        echo $view->partial('admin/media/show-details-renderer', ['media' => $resource]);
    }

    public function handleViewBrowse(Event $event): void
    {
        $session = new Container('Next');
        if (!isset($session->lastBrowsePage)) {
            $session->lastBrowsePage = [];
            $session->lastQuery = [];
        }
        $params = $event->getTarget()->params();
        $ui = $params->fromRoute('__ADMIN__') ? 'admin' : 'public';
        // Why not use $this->getServiceLocator()->get('Request')->getServer()->get('REQUEST_URI')?
        $session->lastBrowsePage[$ui] = $_SERVER['REQUEST_URI'];
        // Remove any csrf key.
        $query = $params->fromQuery();
        foreach (array_keys($query) as $key) {
            if (substr($key, -4) === 'csrf') {
                unset($query[$key]);
            }
        }
        $session->lastQuery[$ui] = $query;
    }

    public function handleSiteSettings(Event $event): void
    {
        parent::handleSiteSettings($event);

        $services = $this->getServiceLocator();

        $settings = $services->get('Omeka\Settings\Site');
        $orders = $settings->get('next_items_order_for_itemsets') ?: [];
        $ordersString = '';
        foreach ($orders as $ids => $order) {
            $ordersString .= $ids . ' ' . $order['sort_by'];
            if (isset($order['sort_order'])) {
                $ordersString .= ' ' . $order['sort_order'];
            }
            $ordersString .= "\n";
        }

        /**
         * @see \Omeka\Form\Element\RestoreTextarea $siteGroupsElement
         * @see \Internationalisation\Form\SettingsFieldset $fieldset
         */
        $fieldset = $event->getTarget()
            ->get('next');
        $fieldset
            ->get('next_items_order_for_itemsets')
            ->setValue($ordersString);
    }

    public function handleSiteSettingsFilters(Event $event): void
    {
        $inputFilter = version_compare(\Omeka\Module::VERSION, '4', '<')
            ? $event->getParam('inputFilter')->get('next')
            : $event->getParam('inputFilter');
        $inputFilter
            // TODO Use DataTextarea.
            ->add([
                'name' => 'next_items_order_for_itemsets',
                'required' => false,
                'filters' => [
                    [
                        'name' => \Laminas\Filter\Callback::class,
                        'options' => [
                            'callback' => [$this, 'filterResourceOrder'],
                        ],
                    ],
                ],
            ])
        ;
    }

    public function filterResourceOrder($string)
    {
        $list = $this->stringToList($string);

        // The setting is ordered by item set id for quicker check.
        // "0" is the default order, so it is always single.
        $result = [];
        foreach ($list as $row) {
            [$ids, $sortBy, $sortOrder] = array_map('trim', explode(' ', str_replace("\t", ' ', $row) . '  ', 3));
            $ids = trim((string) $ids, ', ');
            if (!strlen($ids) || empty($sortBy)) {
                continue;
            }
            $ids = explode(',', $ids);
            sort($ids);
            $ids = in_array('0', $ids)
                ? 0
                : implode(',', $ids);
            $result[$ids] = [
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder && strtolower($sortOrder) === 'desc' ? 'desc' : 'asc',
            ];
        }
        ksort($result);

        return $result;
    }

    /**
     * Get each line of a string separately.
     */
    public function stringToList($string): array
    {
        return array_filter(array_map('trim', explode("\n", $this->fixEndOfLine($string))), 'strlen');
    }

    /**
     * Clean the text area from end of lines.
     *
     * This method fixes Windows and Apple copy/paste from a textarea input.
     */
    public function fixEndOfLine($string): string
    {
        return str_replace(["\r\n", "\n\r", "\r"], ["\n", "\n", "\n"], (string) $string);
    }
}
